<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<style>
    body {
        display: flex;
        min-height: 100vh;
        margin: 0;
        background-color: #f8f9fa;
    }
    .sidebar {
        min-width: 250px;
        background-color: #fff;
        border-right: 1px solid #ddd;
        height: 100vh; /* Full viewport height */
        position: sticky;
        top: 0;
    }.sidebar .nav-link i {
        margin-right: 0.75rem;  /* 12px spacing between icons and text */
        width: 1.25rem;         /* Fixed width for consistent alignment */
        color: #495057;         /* Icon color matching text */
    }

    .sidebar .nav-link:hover i {
        color: #0d6efd;         /* Icon color on hover */
    }

    /* Specific style for logout icon */
    .sidebar .nav-link.logout-link i {
        color: #dc3545;         /* Red color for logout icon */
    }

    .sidebar .nav-link.logout-link:hover i {
        color: #bb2d3b;         /* Darker red on hover */
    }
    .container {
        flex: 1;
        padding: 20px;
        max-width: calc(100% - 250px); /* Adjust based on sidebar width */
        margin-top: 0; /* Remove top margin */
    }
    /* Keep existing dashboard card styles */
    .dashboard-card {
        min-height: 150px;
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
 
<!-- Sidebar Container -->
<div class="sidebar p-3 bg-light">
  <a href="../public/index.php" class="d-flex align-items-center mb-3 text-dark text-decoration-none">
    <span class="fs-4">Media Search</span>
  </a>
  <hr>
  <ul class="nav nav-pills flex-column mb-auto">
    <li class="nav-item">
      <a href="../public/index.php" class="nav-link text-dark">
        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
      </a>
    </li>
    <li class="nav-item">
      <a href="../public/admins.php" class="nav-link text-dark">
        <i class="fas fa-user-shield me-2"></i>Admins

      </a>
    </li>
    <li class="nav-item">
      <a href="../public/users.php" class="nav-link text-dark">
        <i class="fas fa-users me-2"></i>Users
      </a>
    </li>
    <li class="nav-item">
      <a href="../public/search_history.php" class="nav-link text-dark">
        <i class="fas fa-search me-2"></i>Search History
      </a>
    </li>
    <li class="nav-item">
      <a href="../public/saved.php" class="nav-link text-dark">
        <i class="fas fa-bookmark me-2"></i>Saved Items
      </a>
    </li>
    <li class="nav-item">
      <a href="../auth/logout.php" class="nav-link text-dark">
      <i class="fas  fa-sign-out-alt me-2"></i>Log out
      </a>
    </li>
  </ul>
  <hr>
  <div>
    <?php if (isset($_SESSION['admin_username'])): ?>
      <p class="text-muted">Logged in as: <?= htmlspecialchars($_SESSION['admin_username']) ?></p>
    <?php endif; ?>
  </div>
</div>
