<?php
// subcategory_form.php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM subcategories WHERE id = :id");
    $stmt->execute(['id'=>$id]);
    $sub = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$sub) die('Not found');
} else {
    $sub = ['category_id'=>'','name'=>'','slug'=>'','description'=>''];
}

// load categories for dropdown
$cats = $pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int)($_POST['category_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($category_id <= 0) $errors[] = 'Select category';
    if ($name === '') $errors[] = 'Name required';
    if ($slug === '') $slug = strtolower(preg_replace('/[^a-zA-Z0-9\-]+/','-',$name));

    if (empty($errors)) {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE subcategories SET category_id=:category_id, name=:name, slug=:slug, description=:description WHERE id=:id");
            $stmt->execute(['category_id'=>$category_id,'name'=>$name,'slug'=>$slug,'description'=>$description,'id'=>$id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO subcategories (category_id,name,slug,description,created_at) VALUES (:category_id,:name,:slug,:description,NOW())");
            $stmt->execute(['category_id'=>$category_id,'name'=>$name,'slug'=>$slug,'description'=>$description]);
        }
        header('Location: subcategories.php');
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $id ? 'Edit' : 'Add'; ?> Subcategory</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'partials/topnav.php'; ?>
<div class="container mt-4">
  <h3><?php echo $id ? 'Edit' : 'Add'; ?> Subcategory</h3>
  <?php if($errors): foreach($errors as $e): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
  <?php endforeach; endif; ?>

  <form method="post">
    <div class="mb-2">
      <label>Category</label>
      <select class="form-control" name="category_id" required>
        <option value="">-- Select category --</option>
        <?php foreach($cats as $c): ?>
          <option value="<?php echo $c['id']; ?>" <?php echo ($c['id']==($sub['category_id'] ?? '')) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-2">
      <label>Name</label>
      <input class="form-control" name="name" value="<?php echo htmlspecialchars($sub['name']); ?>" required>
    </div>
    <div class="mb-2">
      <label>Slug</label>
      <input class="form-control" name="slug" value="<?php echo htmlspecialchars($sub['slug']); ?>">
    </div>
    <div class="mb-2">
      <label>Description</label>
      <textarea class="form-control" name="description"><?php echo htmlspecialchars($sub['description']); ?></textarea>
    </div>

    <button class="btn btn-primary"><?php echo $id ? 'Update' : 'Add'; ?></button>
    <a href="subcategories.php" class="btn btn-secondary">Back</a>
  </form>
</div>
</body>
</html>
