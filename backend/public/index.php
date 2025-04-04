<?php
session_start();
require_once '../config/config.php';
require_once '../classes/Dashboard.php';

// Create Dashboard object which handles authentication and database connection
$dashboard = new Dashboard();

$userCount  = $dashboard->getUserCount();
$adminCount = $dashboard->getAdminCount();
$savedCount = $dashboard->getSavedItemsCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - Media Search</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .sidebar {
      min-width: 250px;
      background-color: #fff;
      border-right: 1px solid #ddd;
    }
    .dashboard-card {
      min-height: 300px;
    }
  </style>
</head>
<body>
<div class="d-flex">
  <!-- Include Sidebar -->
  <?php include '../models/sidebar.php'; ?>
  
  <!-- Main Dashboard Content -->
  <div class="flex-grow-1 p-4">
    <h1>Dashboard</h1>
    <div class="row mt-4">
      <!-- Users Count -->
      <div class="col-md-4">
        <div class="card text-white bg-primary dashboard-card mb-3">
          <div class="card-header">Users</div>
          <div class="card-body">
            <h2 class="card-title"><?= htmlspecialchars($userCount) ?></h2>
            <p class="card-text">Total registered users</p>
          </div>
        </div>
      </div>
      <!-- Admins Count -->
      <div class="col-md-4">
        <div class="card text-white bg-success dashboard-card mb-3">
          <div class="card-header">Admins</div>
          <div class="card-body">
            <h2 class="card-title"><?= htmlspecialchars($adminCount) ?></h2>
            <p class="card-text">Total admin accounts</p>
          </div>
        </div>
      </div>
      <!-- Saved Items Count -->
      <div class="col-md-4">
        <div class="card text-white bg-info dashboard-card mb-3">
          <div class="card-header">Saved Items</div>
          <div class="card-body">
            <h2 class="card-title"><?= htmlspecialchars($savedCount) ?></h2>
            <p class="card-text">Total saved media items</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
