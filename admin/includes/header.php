<?php
  require_once 'db.php';
  require_once 'auth.php';
  require_login();
  // Get unread count (you can also move this to AJAX if you prefer)
  $unread_count = $pdo->query("SELECT COUNT(*) FROM quotations WHERE is_read = 0")->fetchColumn();
  function is_active($filename) {
    $current = basename($_SERVER['PHP_SELF']);
    return $current === $filename ? 'nav_active_link active' : '';
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="../assets/images/favicon.png" type="image/x-icon">
  <style>
    .nav_active_link{
      color: #903035 !important;
    }
    .theme_bg_color{
      background-color : #903035 !important;
    }
    .theme_bg_color:hover{
      background-color : #975155 !important;
      border: 1px solid #975155 !important;
    }
    .theme_border_color{
      border: 1px solid #903035 !important;
    }
    .theme_outline_btn_color{
      border: 1px solid #903035 !important;
      color: #903035 !important;
    }
    .theme_outline_btn_color:hover{
      background-color : #903035 !important;
      border: 1px solid #903035 !important;
      color: #fff !important;
    }
    a{
      text-decoration: none;
    }
    body {
      font-family: 'Inter', sans-serif;
    }
    @media screen and (max-width:991px){
      .nav-link .fas{
        width: 30px;
      }
      #noti-count{
        transform: translate(-492%,-50%)!important;
      }
    }
    @media screen and (min-width:992px){
      .nav-link .n-icon{
        font-size: 24px;
        margin-bottom: 0.5rem!important;
      }
      .navbar .nav-link, .nav-link #quoteBell{
        display: flex;
        flex-direction: column;
        align-items: center;
      }
      #noti-count{
        transform: translate(50%,-50%)!important;
      }
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php"><img src="../assets/images/theme-logo.png" alt="theme logo" width="150"></a>
    <!-- <a class="navbar-brand" href="dashboard.php"><i class="fas fa-tools me-2"></i> Admin Panel</a> -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNavbar">
    <ul class="navbar-nav ms-auto">
      <li class="nav-item">
        <a class="nav-link <?= is_active('dashboard.php') ?>" href="dashboard.php">
          <i class="fas fa-home text-center n-icon"></i> Dashboard
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= is_active('requests.php') ?>" href="requests.php">
          <i class="fas fa-clipboard-list text-center n-icon"></i> Requests
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= is_active('quotations.php') ?>" href="quotations.php">
          <i class="fas fa-file-invoice-dollar text-center n-icon"></i> Quotations
        </a>
      </li>
      <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <li class="nav-item">
          <a class="nav-link <?= is_active('user_management.php') ?>" href="user_management.php">
            <i class="fas fa-users text-center n-icon"></i> Users
          </a>
        </li>
      <?php endif; ?>
      <li class="nav-item">
        <a class="nav-link me-4 <?= is_active('profile.php') ?>" href="profile.php">
          <i class="fas fa-user-cog text-center n-icon"></i> Profile
        </a>
      </li>
      <!-- Notification Bell -->
      <li class="nav-item dropdown">
        <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <div class="text-dark position-relative me-3" id="quoteBell" style="cursor: pointer;">
            <i class="fas fa-bell text-center n-icon"></i> Notifications
            <span id="noti-count" class="position-absolute opacity-75 badge rounded-pill theme_bg_color"></span>
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" style="width: 300px; max-height: 400px; overflow-y: auto;" id="notifications-list">
          <li class="dropdown-item text-muted small">Loading...</li>
        </ul>
      </li>
      <li class="nav-item">
        <a class="nav-link me-3 text-danger" href="logout.php">
          <i class="fas fa-sign-out-alt text-center n-icon"></i> Logout
        </a>
      </li>
    </ul>
    </div>
  </div>
</nav>

<div class="container mt-4 mb-5 pb-3">
