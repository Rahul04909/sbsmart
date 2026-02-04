<?php
// brands.php - Brands Management
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';

$q = trim($_GET['q'] ?? '');
$filterSql = '';
$params = [];
if ($q !== '') {
    $filterSql = "WHERE name LIKE :q OR slug LIKE :q OR description LIKE :q";
    $params['q'] = '%' . $q . '%';
}

// fetch brands
$stmt = $pdo->prepare("SELECT id, name, slug, description, created_at FROM brands $filterSql ORDER BY id DESC");
$stmt->execute($params);
$brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

// counts helper
function count_products_in_brand($pdo, $bid) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE brand_id = ?");
        $stmt->execute([$bid]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0; // Fallback if column rename hasn't happened yet
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Brands | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
.badge { font-weight: 500; }
</style>
</head>
<body class="bg-light">
<?php include 'partials/topnav.php'; ?>
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 text-gray-800">Brands</h2>
    <div class="d-flex gap-2">
      <a href="brand_form.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Brand</a>
      <a href="subcategories.php" class="btn btn-outline-secondary"><i class="bi bi-list-nested me-1"></i> Manage Subcategories</a>
    </div>
  </div>

  <div class="card shadow-sm mb-4 border-0">
    <div class="card-body bg-white rounded">
      <form method="get" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                <input class="form-control border-start-0" name="q" placeholder="Search brands..." value="<?php echo htmlspecialchars($q); ?>">
            </div>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">Search</button>
            <a href="brands.php" class="btn btn-outline-secondary">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <form method="post" action="brand_delete.php" id="bulkForm">
    <div class="card shadow border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">All Brands</h6>
            <button type="submit" name="action" value="delete" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete selected brands? This will NOT delete products.');"><i class="bi bi-trash"></i> Delete Selected</button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th style="width:40px" class="ps-3"><input class="form-check-input" type="checkbox" id="checkAll"></th>
                      <th>Name</th>
                      <th>Description</th>
                      <th>Products</th>
                      <th>Created</th>
                      <th class="text-end pe-3">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php if (empty($brands)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No brands found.</td></tr>
                  <?php else: ?>
                  <?php foreach($brands as $b): 
                      $pCount = count_products_in_brand($pdo, $b['id']);
                  ?>
                    <tr>
                      <td class="ps-3"><input class="form-check-input" type="checkbox" name="ids[]" value="<?php echo $b['id']; ?>"></td>
                      <td>
                          <div class="fw-bold text-dark"><?= htmlspecialchars($b['name']); ?></div>
                          <div class="small text-muted">/<?= htmlspecialchars($b['slug']); ?></div>
                      </td>
                      <td class="text-secondary"><?= htmlspecialchars($b['description'] ?: '-'); ?></td>
                      <td><span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill"><?= $pCount ?> Items</span></td>
                      <td class="text-muted small"><?= date('d M Y', strtotime($b['created_at'])); ?></td>
                      <td class="text-end pe-3">
                        <div class="btn-group">
                            <a href="brand_form.php?id=<?php echo $b['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil-square"></i></a>
                            <a href="brand_delete.php?id=<?php echo $b['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this brand?');" title="Delete"><i class="bi bi-trash"></i></a>
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

</div>

<script>
document.getElementById('checkAll').addEventListener('change', function(){
  document.querySelectorAll('input[name="ids[]"]').forEach(cb => cb.checked = this.checked);
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
