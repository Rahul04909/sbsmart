<?php
// users.php - Admin User Management
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';

$stmt = $pdo->query("SELECT id, name, email, phone, is_active, created_at FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Users | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<?php include 'partials/topnav.php'; ?>
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 text-gray-800">Users</h2>
    <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-download"></i> Export List</button>
  </div>

  <div class="card shadow border-0">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 fw-bold text-primary">Registered Users</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-3">ID</th>
                  <th>Name</th>
                  <th>Email / Phone</th>
                  <th>Status</th>
                  <th>Registered</th>
                  <th class="text-end pe-3">Action</th>
                </tr>
              </thead>
              <tbody>
              <?php if (empty($users)): ?>
                <tr><td colspan="6" class="text-center py-4 text-muted">No users found.</td></tr>
              <?php else: ?>
              <?php foreach($users as $u): ?>
                <tr>
                  <td class="ps-3">#<?= $u['id']; ?></td>
                  <td>
                      <div class="fw-bold text-dark"><?= htmlspecialchars($u['name']); ?></div>
                  </td>
                  <td>
                      <div class="text-dark"><?= htmlspecialchars($u['email']); ?></div>
                      <div class="small text-muted"><?= htmlspecialchars($u['phone']); ?></div>
                  </td>
                  <td>
                      <?php if (!empty($u['is_active'])): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Active</span>
                      <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Inactive</span>
                      <?php endif; ?>
                  </td>
                  <td class="text-muted small"><?= date('d M Y', strtotime($u['created_at'])); ?></td>
                  <td class="text-end pe-3">
                    <button class="btn btn-sm btn-outline-primary" title="View Details"><i class="bi bi-search"></i></button>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php endif; ?>
              </tbody>
            </table>
        </div>
    </div>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
