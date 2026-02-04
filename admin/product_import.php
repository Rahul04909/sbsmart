<?php
// product_import.php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';

$errors = [];
$success = '';

/**
 * Download a remote image and save under uploads/products/.
 * Returns saved filename or null on failure.
 */
function download_image($url) {
    $url = trim($url);
    if ($url === '') return null;
    $ctx = stream_context_create([
        'http' => ['timeout' => 12, 'user_agent' => 'Mozilla/5.0'],
        'https'=> ['timeout' => 12, 'user_agent' => 'Mozilla/5.0']
    ]);
    $data = @file_get_contents($url, false, $ctx);
    if ($data === false) return null;

    // derive extension
    $path = parse_url($url, PHP_URL_PATH);
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    $ext = $ext ? strtolower(preg_replace('/[^a-z0-9]/','',$ext)) : 'jpg';
    if (strlen($ext) > 5) $ext = 'jpg';
    $fname = uniqid('imp_') . '.' . $ext;

    $dir = __DIR__ . '/uploads/products';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $fp = $dir . '/' . $fname;
    if (@file_put_contents($fp, $data) === false) return null;
    return $fname;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_FILES['csv']['tmp_name'])) {
        $errors[] = 'Please upload a CSV file.';
    } else {
        $tmp = $_FILES['csv']['tmp_name'];
        if (($handle = fopen($tmp, 'r')) === false) {
            $errors[] = 'Unable to open uploaded CSV.';
        } else {
            // read header row
            $header = fgetcsv($handle);
            if (!$header || !is_array($header)) {
                $errors[] = 'CSV header row not found or invalid.';
            } else {
                // map header -> lowercase keys
                $map = array_map(function($h){ return strtolower(trim($h)); }, $header);

                $rowNum = 1;
                $inserted = 0;
                // Begin DB transaction for speed & atomicity
                $pdo->beginTransaction();
                try {
                    while (($row = fgetcsv($handle)) !== false) {
                        $rowNum++;
                        // skip empty rows
                        $allEmpty = true;
                        foreach ($row as $cell) { if (trim($cell) !== '') { $allEmpty = false; break; } }
                        if ($allEmpty) continue;

                        // combine map -> values (if row shorter than header, fill with '')
                        $row = array_pad($row, count($map), '');
                        $data = array_combine($map, $row);

                        // basic fields
                        $title = trim($data['title'] ?? '');
                        if ($title === '') continue; // skip rows without title

                        $sku = trim($data['sku'] ?? '') ?: 'SKU' . time() . rand(100,999);
                        $hsn_code = trim($data['hsn_code'] ?? '');
                        $slug = trim($data['slug'] ?? '') ?: strtolower(preg_replace('/[^a-z0-9]+/','-', $title));
                        $short_desc = trim($data['short_desc'] ?? '');
                        $short_description = trim($data['short_description'] ?? '');
                        $description = trim($data['description'] ?? '');
                        $mrp = (float)($data['mrp'] ?? 0);
                        $discount_percentage = (float)($data['discount_percentage'] ?? 0);
                        $price = (float)($data['price'] ?? 0);
                        
                        $stock = (int)($data['stock'] ?? 0);
                        $is_bestseller = !empty($data['is_bestseller']) && strtolower(trim($data['is_bestseller'])) !== 'no' ? 1 : 0;
                        $categoryName = trim($data['category'] ?? '');
                        $subcategoryName = trim($data['subcategory'] ?? '');
                        $tags = trim($data['tags'] ?? '');
                        $imagesField = trim($data['images'] ?? ''); // comma separated urls or filenames
                        $singleImage = trim($data['image'] ?? ''); // optional single image
                        $status = in_array(trim(strtolower($data['status'] ?? 'active')), ['active','inactive']) ? trim(strtolower($data['status'] ?? 'active')) : 'active';

                        // find or create category
                        $category_id = null;
                        if ($categoryName !== '') {
                            $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = :name LIMIT 1");
                            $stmt->execute([':name' => $categoryName]);
                            $cid = $stmt->fetchColumn();
                            if ($cid) $category_id = (int)$cid;
                            else {
                                $cslug = strtolower(preg_replace('/[^a-z0-9]+/','-',$categoryName));
                                $stmt2 = $pdo->prepare("INSERT INTO categories (name,slug,created_at) VALUES (:name,:slug,NOW())");
                                $stmt2->execute([':name'=>$categoryName, ':slug'=>$cslug]);
                                $category_id = (int)$pdo->lastInsertId();
                            }
                        }

                        // find or create subcategory
                        $subcategory_id = null;
                        if ($subcategoryName !== '') {
                            $sql = "SELECT id FROM subcategories WHERE name = :name";
                            $params = [':name' => $subcategoryName];
                            if ($category_id) { $sql .= " AND category_id = :cid"; $params[':cid'] = $category_id; }
                            $sql .= " LIMIT 1";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute($params);
                            $sid = $stmt->fetchColumn();
                            if ($sid) $subcategory_id = (int)$sid;
                            else {
                                $sslug = strtolower(preg_replace('/[^a-z0-9]+/','-',$subcategoryName));
                                $stmt2 = $pdo->prepare("INSERT INTO subcategories (category_id,name,slug,created_at) VALUES (:category_id,:name,:slug,NOW())");
                                $stmt2->execute([':category_id'=>$category_id, ':name'=>$subcategoryName, ':slug'=>$sslug]);
                                $subcategory_id = (int)$pdo->lastInsertId();
                            }
                        }

                        // process images field (comma-separated)
                        $imagesArr = [];
                        if ($imagesField !== '') {
                            // split by comma but account for possible commas in filenames? We assume simple comma-separated.
                            $parts = array_map('trim', explode(',', $imagesField));
                            foreach ($parts as $p) {
                                if ($p === '') continue;
                                if (filter_var($p, FILTER_VALIDATE_URL)) {
                                    $saved = download_image($p);
                                    if ($saved) $imagesArr[] = $saved;
                                } else {
                                    // treat as filename (may already be uploaded earlier)
                                    $imagesArr[] = basename($p);
                                }
                            }
                        }

                        // if single image provided and imagesArr empty, try to use it
                        $imageName = null;
                        if ($singleImage !== '') {
                            if (filter_var($singleImage, FILTER_VALIDATE_URL)) {
                                $d = download_image($singleImage);
                                if ($d) $imageName = $d;
                            } else {
                                $imageName = basename($singleImage);
                            }
                        }

                        // if imagesArr not empty and main image not set, set main as first imagesArr
                        if (empty($imageName) && !empty($imagesArr)) {
                            $imageName = $imagesArr[0];
                        }

                        // insert into products
                        $stmtIns = $pdo->prepare("
                            INSERT INTO products
                            (sku,hsn_code,slug,title,short_desc,short_description,description,price,mrp,discount_percentage,stock,category_id,subcategory_id,tags,image,images,status,is_bestseller,created_at)
                            VALUES
                            (:sku,:hsn_code,:slug,:title,:short_desc,:short_description,:description,:price,:mrp,:discount_percentage,:stock,:category_id,:subcategory_id,:tags,:image,:images,:status,:is_bestseller,NOW())
                        ");

                        $stmtIns->execute([
                            ':sku'=>$sku,
                            ':hsn_code'=>$hsn_code,
                            ':slug'=>$slug,
                            ':title'=>$title,
                            ':short_desc'=>$short_desc,
                            ':short_description'=>$short_description,
                            ':description'=>$description,
                            ':price'=>$price,
                            ':mrp'=>$mrp,
                            ':discount_percentage'=>$discount_percentage,
                            ':stock'=>$stock,
                            ':category_id'=>$category_id,
                            ':subcategory_id'=>$subcategory_id,
                            ':tags'=>$tags,
                            ':image'=>$imageName,
                            ':images'=> json_encode(array_values($imagesArr)),
                            ':status'=>$status,
                            ':is_bestseller'=>$is_bestseller
                        ]);

                        $inserted++;
                    } // end while
                    $pdo->commit();
                    $success = "Imported $inserted products.";
                } catch (Throwable $e) {
                    $pdo->rollBack();
                    $errors[] = "Database error on import: " . $e->getMessage();
                }
            } // end header else
            fclose($handle);
        } // end handle open
    } // end file uploaded
} // end POST

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Import Products</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'partials/topnav.php'; ?>
<div class="container mt-4">
  <h3>Import products (CSV)</h3>
  <p>Supported CSV header (case-insensitive): <code>sku,title,hsn_code,slug,short_desc,short_description,description,price,mrp,discount_percentage,stock,category,subcategory,tags,images,image,status,is_bestseller</code></p>

  <?php if ($errors): foreach($errors as $e): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
  <?php endforeach; endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <div class="mb-2">
      <label>CSV file</label>
      <input type="file" name="csv" accept=".csv" class="form-control" required>
    </div>

    <div class="mb-2">
      <button class="btn btn-primary">Import</button>
      <a class="btn btn-secondary" href="products.php">Back</a>
    </div>
  </form>
</div>
</body>
</html>
