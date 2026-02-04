<?php
// product_form.php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/../includes/helpers.php'; // For resolve_image

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(['id'=>$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) die("Product not found");
    $product['images_arr'] = !empty($product['images']) ? json_decode($product['images'], true) : [];
} else {
    $product = [
      'sku'=>'','hsn_code'=>'','slug'=>'','title'=>'','short_desc'=>'','short_description'=>'','description'=>'',
      'price'=>0,'mrp'=>0,'discount_percentage'=>0,'stock'=>0,'image'=>null,'images'=>'[]','images_arr'=>[],'brand_id'=>null,'subcategory_id'=>null,
      'tags'=>'','status'=>1
    ];
}

// brands
$brands = $pdo->query("SELECT id,name FROM brands ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sku = trim($_POST['sku'] ?? '');
    $hsn_code = trim($_POST['hsn_code'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $short_desc = trim($_POST['short_desc'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $mrp = (float)($_POST['mrp'] ?? 0);
    $discount_percentage = (float)($_POST['discount_percentage'] ?? 0); 
    $stock = (int)($_POST['stock'] ?? 0);
    $brand_id = (int)($_POST['brand_id'] ?? 0);
    $subcategory_id = (int)($_POST['subcategory_id'] ?? 0);
    $tags = trim($_POST['tags'] ?? '');
    
    // Fix: db uses tinyint(1), so use 1/0. Form uses 'active'/'inactive'.
    $statusInput = $_POST['status'] ?? 'active';
    $status = ($statusInput === 'active') ? 1 : 0;
    
    // Best Seller check
    $is_bestseller = !empty($_POST['is_bestseller']) ? 1 : 0;

    if ($title === '') $errors[] = 'Title required';
    if ($sku === '') $sku = 'SKU'.time();

    // Fix: Upload path should be relative to project root, not inside admin
    // Current dir is admin/
    // Uploads should go to ../uploads/products
    $uploadDir = __DIR__ . '/../uploads/products';

    // main image
    $imageName = $product['image'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newFilename = uniqid('p_') . '.' . $ext;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . '/' . $newFilename)) {
            $imageName = $newFilename;
        } else {
            $errors[] = "Failed to upload main image";
        }
    }

    // multi images
    $imagesArr = $product['images_arr'] ?? [];
    if (!empty($_FILES['images']['name'][0])) {
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        foreach ($_FILES['images']['tmp_name'] as $k => $tmpname) {
            if (!$tmpname) continue;
            $orig = $_FILES['images']['name'][$k];
            $ext = pathinfo($orig, PATHINFO_EXTENSION) ?: 'jpg';
            $fnew = uniqid('pimg_') . '.' . $ext;
            if (move_uploaded_file($tmpname, $uploadDir . '/' . $fnew)) {
                $imagesArr[] = $fnew;
            }
        }
    }

    // remove selected existing images
    if (!empty($_POST['remove_images']) && is_array($_POST['remove_images'])) {
        foreach ($_POST['remove_images'] as $rem) {
            $rem = basename($rem);
            if (($i = array_search($rem, $imagesArr)) !== false) {
                if (file_exists($uploadDir . '/' . $rem)) @unlink($uploadDir . '/' . $rem);
                array_splice($imagesArr, $i, 1);
            }
        }
    }

    $imagesJson = json_encode(array_values($imagesArr));

    if (empty($errors)) {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE products SET sku=:sku,hsn_code=:hsn_code,slug=:slug,title=:title,short_desc=:short_desc,short_description=:short_description,description=:description,price=:price,mrp=:mrp,discount_percentage=:discount_percentage,stock=:stock,brand_id=:brand_id,subcategory_id=:subcategory_id,tags=:tags,image=:image,images=:images,status=:status,is_bestseller=:is_bestseller,updated_at=NOW() WHERE id=:id");
            $stmt->execute([
                'sku'=>$sku,'hsn_code'=>$hsn_code,'slug'=>$slug,'title'=>$title,'short_desc'=>$short_desc,'short_description'=>$short_description,
                'description'=>$description,'price'=>$price,'mrp'=>$mrp,'discount_percentage'=>$discount_percentage,'stock'=>$stock,'brand_id'=>$brand_id,'subcategory_id'=>$subcategory_id,
                'tags'=>$tags,'image'=>$imageName,'images'=>$imagesJson,'status'=>$status,'is_bestseller'=>$is_bestseller,'id'=>$id
            ]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (sku,hsn_code,slug,title,short_desc,short_description,description,price,mrp,discount_percentage,stock,brand_id,subcategory_id,tags,image,images,status,is_bestseller,created_at) VALUES (:sku,:hsn_code,:slug,:title,:short_desc,:short_description,:description,:price,:mrp,:discount_percentage,:stock,:brand_id,:subcategory_id,:tags,:image,:images,:status,:is_bestseller,NOW())");
            $stmt->execute([
                'sku'=>$sku,'hsn_code'=>$hsn_code,'slug'=>$slug,'title'=>$title,'short_desc'=>$short_desc,'short_description'=>$short_description,
                'description'=>$description,'price'=>$price,'mrp'=>$mrp,'discount_percentage'=>$discount_percentage,'stock'=>$stock,'brand_id'=>$brand_id,'subcategory_id'=>$subcategory_id,
                'tags'=>$tags,'image'=>$imageName,'images'=>$imagesJson,'status'=>$status,'is_bestseller'=>$is_bestseller
            ]);
            $id = $pdo->lastInsertId();
        }
        header('Location: products.php?msg=Saved');
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $id ? 'Edit' : 'Add'; ?> Product</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'partials/topnav.php'; ?>
<div class="container mt-4">
  <h3><?php echo $id ? 'Edit' : 'Add'; ?> Product</h3>

  <?php if($errors): foreach($errors as $e): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
  <?php endforeach; endif; ?>

  <form method="post" enctype="multipart/form-data">
    <div class="row">
      <div class="col-md-4 mb-2"><label>SKU</label><input class="form-control" name="sku" value="<?php echo htmlspecialchars($product['sku']); ?>"></div>
      <div class="col-md-4 mb-2"><label>HSN Code</label><input class="form-control" name="hsn_code" value="<?php echo htmlspecialchars($product['hsn_code']); ?>"></div>
      <div class="col-md-4 mb-2"><label>Slug</label><input class="form-control" name="slug" value="<?php echo htmlspecialchars($product['slug']); ?>"></div>
    </div>

    <div class="mb-2"><label>Title</label><input class="form-control" name="title" value="<?php echo htmlspecialchars($product['title']); ?>" required></div>

    <div class="mb-2"><label>Short Desc (brief)</label><input class="form-control" name="short_desc" value="<?php echo htmlspecialchars($product['short_desc']); ?>"></div>

    <div class="mb-2"><label>Short Description (longer)</label><textarea class="form-control" name="short_description"><?php echo htmlspecialchars($product['short_description']); ?></textarea></div>

    <div class="mb-2"><label>Full Description</label><textarea class="form-control" name="description"><?php echo htmlspecialchars($product['description']); ?></textarea></div>

    <div class="row">
      <div class="col"><label>Price</label><input class="form-control" type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>"></div>
      <div class="col"><label>MRP</label><input class="form-control" type="number" step="0.01" id="mrp" name="mrp" value="<?php echo htmlspecialchars($product['mrp']); ?>"></div>
      <div class="col"><label>Discount %</label><input class="form-control" type="number" min="0" max="100" id="discount_percentage" name="discount_percentage" value="<?php echo htmlspecialchars($product['discount_percentage'] ?? 0); ?>" placeholder="0"></div>
      <div class="col"><label>Stock</label><input class="form-control" type="number" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>"></div>
    </div>

    <div class="row mt-3">
      <div class="col-md-6">
        <label>Brand</label>
        <select name="brand_id" id="brand_id" class="form-control">
          <option value="">-- Select brand --</option>
          <?php foreach($brands as $b): ?>
            <option value="<?php echo $b['id']; ?>" <?php echo ($b['id']==($product['brand_id'] ?? '')) ? 'selected':''; ?>><?php echo htmlspecialchars($b['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label>Subcategory</label>
        <select name="subcategory_id" id="subcategory_id" class="form-control">
          <option value="">-- Select subcategory --</option>
        </select>
      </div>
    </div>

    <div class="mb-2 mt-3">
      <label>Tags (comma separated)</label>
      <input class="form-control" name="tags" value="<?php echo htmlspecialchars($product['tags']); ?>">
    </div>

    <div class="mb-2 mt-3">
        <label class="form-check-label fw-bold">
            <input type="checkbox" name="is_bestseller" value="1" <?php echo !empty($product['is_bestseller']) ? 'checked' : ''; ?>>
            Mark as Best Seller
        </label>
    </div>

    <div class="mb-2">
      <label>Main image</label>
      <?php if(!empty($product['image'])): ?>
        <div class="mb-2"><img src="<?php echo resolve_image($product['image']); ?>" style="height:80px"></div>
      <?php endif; ?>
      <input type="file" name="image" class="form-control">
    </div>

    <div class="mb-2">
      <label>Additional images (multiple)</label>
      <?php if (!empty($product['images_arr'])): ?>
        <div class="mb-2">
          <?php foreach($product['images_arr'] as $img): ?>
            <label style="display:inline-block;margin-right:10px">
              <img src="<?php echo resolve_image($img); ?>" style="height:60px"><br>
              <input type="checkbox" name="remove_images[]" value="<?php echo htmlspecialchars($img); ?>"> remove
            </label>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <input type="file" name="images[]" multiple class="form-control">
    </div>

    <div class="mb-2">
      <label>Status</label>
      <select name="status" class="form-control">
        <?php 
          // Current status map check
          $curr = $product['status'] ?? 1; // Default 1 (active)
          // Handle 'active'/'inactive' strings if legacy data exists
          if ($curr === 'active') $curr = 1;
          if ($curr === 'inactive') $curr = 0;
          $curr = (int)$curr;
        ?>
        <option value="active" <?php echo ($curr === 1) ? 'selected' : ''; ?>>Active</option>
        <option value="inactive" <?php echo ($curr === 0) ? 'selected' : ''; ?>>Inactive</option>
      </select>
    </div>

    <div class="mt-3">
      <button class="btn btn-primary"><?php echo $id ? 'Update' : 'Add'; ?></button>
      <a class="btn btn-secondary" href="products.php">Back</a>
    </div>
  </form>
</div>

<script>
document.getElementById('brand_id').addEventListener('change', function(){
  var bid = this.value;
  var sel = document.getElementById('subcategory_id');
  sel.innerHTML = '<option>Loading...</option>';
  fetch('ajax_subcategories.php?brand_id=' + encodeURIComponent(bid))
    .then(r=>r.json())
    .then(data=>{
      var html = '<option value="">-- Select subcategory --</option>';
      data.forEach(function(s){
        var selAttr = (s.id == '<?php echo ($product['subcategory_id'] ?? '')?>') ? ' selected' : '';
        html += '<option value="'+s.id+'"'+selAttr+'>'+s.name+'</option>';
      });
      sel.innerHTML = html;
    }).catch(e=>{ sel.innerHTML = '<option value="">-- Select subcategory --</option>'; });
});
<?php if(!empty($product['brand_id'])): ?>
document.getElementById('brand_id').dispatchEvent(new Event('change'));
<?php endif; ?>

</script>
</body>
</html>
