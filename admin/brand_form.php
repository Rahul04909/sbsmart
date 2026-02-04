<?php
// brand_form.php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$brand = null;
$error = '';

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ?");
    $stmt->execute([$id]);
    $brand = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$brand) die('Brand not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '') {
        $error = 'Name is required.';
    } else {
        if ($slug === '') {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        }

        if ($id) {
            $stmt = $pdo->prepare("UPDATE brands SET name=?, slug=?, description=? WHERE id=?");
            $stmt->execute([$name, $slug, $description, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO brands (name, slug, description, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$name, $slug, $description]);
        }
        header('Location: brands.php');
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $id ? 'Edit' : 'Add'; ?> Brand</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'partials/topnav.php'; ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><?php echo $id ? 'Edit' : 'Add'; ?> Brand</h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Brand Name</label>
                            <input class="form-control" name="name" value="<?php echo htmlspecialchars($brand['name'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Slug (optional)</label>
                            <input class="form-control" name="slug" value="<?php echo htmlspecialchars($brand['slug'] ?? ''); ?>" placeholder="Auto-generated if empty">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($brand['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="brands.php" class="btn btn-outline-secondary">Cancel</a>
                            <button class="btn btn-primary">Save Brand</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
