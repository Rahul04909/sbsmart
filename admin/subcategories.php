<?php
// subcategories.php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';

$q = trim($_GET['q'] ?? '');
$filterSql = '';
$params = [];
if ($q !== '') {
    $filterSql = "WHERE s.name LIKE :q OR s.slug LIKE :q OR s.description LIKE :q OR c.name LIKE :q";
    $params['q'] = '%' . $q . '%';
}

$stmt = $pdo->prepare("SELECT s.id, s.name, s.slug, s.description, s.category_id, c.name AS category_name, s.created_at
    FROM subcategories s
    LEFT JOIN categories c ON c.id = s.category_id
    $filterSql
    ORDER BY s.id DESC");
$stmt->execute($params);
$subs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Subcategories</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'partials/topnav.php'; ?>
<div class="container mt-4">
  <div class="d-flex justify-content-between">
    <h3>Subcategories</h3>
    <div>
      <a href="subcategory_form.php" class="btn btn-success">Add Subcategory</a>
      <a href="categories.php" class="btn btn-secondary">Manage Categories</a>
    </div>
  </div>

  <form method="get" class="row g-2 my-3">
    <div class="col-auto">
      <input class="form-control" name="q" placeholder="Search subcategories..." value="<?php echo htmlspecialchars($q); ?>">
    </div>
    <div class="col-auto">
      <button class="btn btn-outline-primary">Search</button>
      <a href="subcategories.php" class="btn btn-outline-secondary">Clear</a>
    </div>
  </form>

  <form method="post" action="subcategory_delete.php" onsubmit="return confirm('Delete selected subcategories?');">
    <div class="mb-2">
      <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm">Delete Selected</button>
      <button type="submit" name="action" value="delete_all" class="btn btn-warning btn-sm" onclick="return confirm('Delete ALL subcategories?');">Delete All</button>
    </div>

    <table class="table table-striped">
      <thead>
        <tr>
          <th style="width:40px"><input type="checkbox" id="checkAllSubs"></th>
          <th>#</th>
          <th>Name</th>
          <th>Category</th>
          <th>Slug</th>
          <th>Description</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($subs as $s): ?>
        <tr>
          <td><input type="checkbox" name="ids[]" value="<?php echo $s['id']; ?>"></td>
          <td><?php echo $s['id']; ?></td>
          <td><?php echo htmlspecialchars($s['name']); ?></td>
          <td><?php echo htmlspecialchars($s['category_name'] ?? '-'); ?></td>
          <td><?php echo htmlspecialchars($s['slug']); ?></td>
          <td><?php echo htmlspecialchars($s['description']); ?></td>
          <td>
            <a href="subcategory_form.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
            <a href="subcategory_delete.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this subcategory?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </form>
</div>

<script>
document.getElementById('checkAllSubs').addEventListener('change', function(){
  document.querySelectorAll('input[name="ids[]"]').forEach(cb => cb.checked = this.checked);
});
</script>
</body>
</html>
