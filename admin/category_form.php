<?php
// category_form.php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
    $stmt->execute(['id'=>$id]);
    $cat = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$cat) { die('Category not found'); }
} else {
    $cat = ['name'=>'','slug'=>'','description'=>''];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '') $errors[] = 'Name is required';
    if ($slug === '') {
        // auto-generate slug
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9\-]+/','-', $name));
    }

    if (empty($errors)) {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE categories SET name=:name, slug=:slug, description=:description WHERE id=:id");
            $stmt->execute(['name'=>$name,'slug'=>$slug,'description'=>$description,'id'=>$id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name,slug,description,created_at) VALUES (:name,:slug,:description,NOW())");
            $stmt->execute(['name'=>$name,'slug'=>$slug,'description'=>$description]);
        }
        header('Location: categories.php');
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $id ? 'Edit' : 'Add'; ?> Category</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'partials/topnav.php'; ?>
<div class="container mt-4">
  <h3><?php echo $id ? 'Edit' : 'Add'; ?> Category</h3>
  <?php if ($errors): foreach($errors as $e): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
  <?php endforeach; endif; ?>

  <form method="post">
    <div class="mb-2">
      <label>Name</label>
      <input class="form-control" name="name" value="<?php echo htmlspecialchars($cat['name']); ?>" required>
    </div>
    <div class="mb-2">
      <label>Slug</label>
      <input class="form-control" name="slug" value="<?php echo htmlspecialchars($cat['slug']); ?>">
    </div>
    <div class="mb-2">
      <label>Description</label>
      <textarea class="form-control" name="description"><?php echo htmlspecialchars($cat['description']); ?></textarea>
    </div>
    <button class="btn btn-primary"><?php echo $id ? 'Update' : 'Add'; ?></button>
    <a href="categories.php" class="btn btn-secondary">Back</a>
  </form>
</div>
</body>
</html>
