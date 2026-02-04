<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">

    <a class="navbar-brand fw-bold" href="index.php">SBSmart Admin</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">

      <ul class="navbar-nav me-auto mb-2 mb-lg-0">


        <li class="nav-item">
          <a class="nav-link" href="products.php">Products</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="categories.php">Categories</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="subcategories.php">Subcategories</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="orders.php">Orders</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="users.php">Users</a>
        </li>

      </ul>

      <span class="navbar-text text-white me-3">
        Welcome, <?php echo htmlspecialchars($admin_name); ?>
      </span>

      <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>

  </div>
</nav>
