<?php
// products.php - Admin Product Management
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/../includes/helpers.php'; // For resolve_image

$q = trim($_GET['q'] ?? '');
$cat_filter = (int)($_GET['category_id'] ?? 0);
$subcat_filter = (int)($_GET['subcategory_id'] ?? 0);
$status_filter = trim($_GET['status'] ?? ''); // '' | active | inactive

// fetch categories
$categories = $pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// build WHERE
$where = [];
$params = [];

if ($q !== '') {
    $where[] = "(p.title LIKE :q OR p.sku LIKE :q OR p.short_desc LIKE :q OR p.short_description LIKE :q OR p.description LIKE :q)";
    $params[':q'] = "%$q%";
}
if ($cat_filter) {
    $where[] = "p.category_id = :cat";
    $params[':cat'] = $cat_filter;
}
if ($subcat_filter) {
    $where[] = "p.subcategory_id = :subcat";
    $params[':subcat'] = $subcat_filter;
}
if ($status_filter && in_array($status_filter, ['active', 'inactive'])) {
    $where[] = "p.status = :status";
    $params[':status'] = $status_filter;
}
$whereSql = $where ? "WHERE " . implode(' AND ', $where) : "";

// pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

// total count
$stmtc = $pdo->prepare("SELECT COUNT(*) FROM products p $whereSql");
$stmtc->execute($params);
$total = (int)$stmtc->fetchColumn();

// fetch products
$sql = "SELECT p.*, c.name AS category_name, s.name AS subcategory_name
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        LEFT JOIN subcategories s ON s.id = p.subcategory_id
        $whereSql
        ORDER BY p.id DESC
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

foreach ($params as $k => $v) {
    if (in_array($k, [':cat', ':subcat'])) $stmt->bindValue($k, (int)$v, PDO::PARAM_INT);
    else $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// fetch subcategories for filter
$subcategories = [];
if ($cat_filter) {
    $stmt = $pdo->prepare("SELECT id,name FROM subcategories WHERE category_id = :cid ORDER BY name");
    $stmt->execute([':cid' => $cat_filter]);
    $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// message
$msg = $_GET['msg'] ?? '';

// Helper to resolve first image properly
function get_thumb($row) {
    $img = $row['image'] ?? null;
    if (empty($img) && !empty($row['images'])) {
        $arr = json_decode($row['images'], true);
        if (is_array($arr) && !empty($arr)) $img = $arr[0];
    }
    return resolve_image($img);
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Products | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
.table-img { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #eee; }
.truncate-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-size: 0.85rem; }
.badge { font-weight: 500; }
</style>
</head>
<body class="bg-light">
<?php include 'partials/topnav.php'; ?>
<div class="container py-4">

  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 text-gray-800">Products</h2>
    <div class="d-flex gap-2">
      <a class="btn btn-primary" href="product_form.php"><i class="bi bi-plus-lg me-1"></i> Add Product</a>
      <div class="dropdown">
          <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="product_import.php"><i class="bi bi-file-earmark-arrow-up me-2"></i>Import CSV</a></li>
            <li><a class="dropdown-item" href="product_export.php"><i class="bi bi-file-earmark-arrow-down me-2"></i>Export CSV</a></li>
          </ul>
      </div>
    </div>
  </div>

  <?php if($msg): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i> <?php echo htmlspecialchars($msg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm mb-4 border-0">
    <div class="card-body p-4 bg-white rounded">
      <form method="get" class="row g-3">
        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                <input class="form-control border-start-0" name="q" placeholder="Search products..." value="<?php echo htmlspecialchars($q); ?>">
            </div>
        </div>

        <div class="col-md-2">
          <select name="category_id" class="form-select" onchange="this.form.submit()">
            <option value="0">Category: All</option>
            <?php foreach($categories as $c): ?>
              <option value="<?php echo (int)$c['id']; ?>" <?php echo $cat_filter== $c['id'] ? 'selected':''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-2">
          <select name="subcategory_id" class="form-select" onchange="this.form.submit()">
            <option value="0">Subcat: All</option>
            <?php foreach($subcategories as $s): ?>
              <option value="<?php echo (int)$s['id']; ?>" <?php echo $subcat_filter== $s['id'] ? 'selected':''; ?>><?php echo htmlspecialchars($s['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-2">
          <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">Status: All</option>
            <option value="active" <?php echo $status_filter==='active'?'selected':''; ?>>Active</option>
            <option value="inactive" <?php echo $status_filter==='inactive'?'selected':''; ?>>Inactive</option>
          </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
          <button class="btn btn-primary flex-grow-1">Filter</button>
          <a href="products.php" class="btn btn-outline-secondary">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <form method="post" action="product_delete.php" id="bulkForm">
    
    <div class="card shadow border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">All Products (<?= $total ?>)</h6>
            <div>
                <button type="submit" name="action" value="delete" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete selected rows?');"><i class="bi bi-trash"></i> Delete Selected</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th style="width:40px" class="ps-3"><input class="form-check-input" type="checkbox" id="checkAll"></th>
                  <th style="width:60px">Image</th>
                  <th>Product</th>
                  <th>Category</th>
                  <th>Price</th>
                  <th>Stock</th>
                  <th>Status</th>
                  <th>Created</th>
                  <th class="text-end pe-3">Actions</th>
                </tr>
              </thead>
              <tbody>
              <?php if (empty($products)): ?>
                <tr><td colspan="9" class="text-center py-4 text-muted">No products found matching your criteria.</td></tr>
              <?php else: ?>
              <?php foreach($products as $p): ?>
                <tr>
                  <td class="ps-3"><input class="form-check-input" type="checkbox" name="ids[]" value="<?php echo (int)$p['id']; ?>"></td>
                  <td>
                    <img src="<?php echo htmlspecialchars(get_thumb($p)); ?>" class="table-img" onerror="this.onerror=null;this.src='<?php echo resolve_image(''); ?>'">
                  </td>
                  <td style="min-width:240px;">
                      <div class="fw-bold text-dark">
                          <?= htmlspecialchars($p['title']) ?>
                          <?php if(!empty($p['sku'])): ?>
                             <span class="text-muted fw-normal small">(<?= htmlspecialchars($p['sku']) ?>)</span>
                          <?php endif; ?>
                      </div>
                      <!-- <div class="small text-muted mb-1">SKU: < ?= htmlspecialchars($p['sku'] ?? 'N/A') ? ></div> -->
                      <div class="truncate-2 text-secondary"><?= htmlspecialchars($p['short_description'] ?? '') ?></div>
                  </td>
                  <td>
                      <div class="small"><?= htmlspecialchars($p['category_name'] ?? '-') ?></div>
                      <?php if(!empty($p['subcategory_name'])): ?>
                          <div class="small text-muted"><i class="bi bi-arrow-return-right"></i> <?= htmlspecialchars($p['subcategory_name']) ?></div>
                      <?php endif; ?>
                  </td>
                  <td class="fw-semibold">₹<?= number_format((float)($p['price'] ?? 0), 2) ?></td>
                  <td>
                      <?php if((int)$p['stock'] > 10): ?>
                          <span class="text-success"><i class="bi bi-check-circle-fill small"></i> <?= $p['stock'] ?></span>
                      <?php elseif((int)$p['stock'] > 0): ?>
                          <span class="text-warning fw-bold"><?= $p['stock'] ?></span>
                      <?php else: ?>
                          <span class="text-danger fw-bold">Out of Stock</span>
                      <?php endif; ?>
                  </td>
                  <td>
                      <?php if($p['status'] === 'active' || (int)$p['status'] === 1): ?>
                          <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                      <?php else: ?>
                          <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactive</span>
                      <?php endif; ?>
                  </td>
                  <td class="small text-muted"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                  <td class="text-end pe-3">
                    <div class="btn-group">
                        <a href="product_form.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil-square"></i></a>
                        <a href="product_delete.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this product?');" title="Delete"><i class="bi bi-trash"></i></a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php endif; ?>
              </tbody>
            </table>
            </div>
        </div>
    </div>
  </form>

  <!-- pagination -->
  <?php
  $last = (int)ceil($total / $perPage);
  if ($last > 1):
  ?>
  <nav class="mt-4">
    <ul class="pagination justify-content-center">
      <?php for($i=1;$i<=$last;$i++): ?>
        <li class="page-item <?php echo $i==$page ? 'active' : ''; ?>">
          <?php
            $qs = $_GET;
            $qs['page'] = $i;
            $url = '?' . http_build_query($qs);
          ?>
          <a class="page-link" href="<?php echo $url; ?>"><?php echo $i; ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
  <?php endif; ?>

</div>

<script>
document.getElementById('checkAll').addEventListener('change', function(){
  document.querySelectorAll('input[name="ids[]"]').forEach(cb => cb.checked = this.checked);
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
