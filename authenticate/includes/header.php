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
  <title><?= ($_SESSION['user_role'] === 'osas') ? strtoupper($_SESSION['user_role']) : ucfirst($_SESSION['user_role']) ?> Portal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <!-- air datepicker CSS -->
  <link rel="shortcut icon" href="../assets/images/favicon.png" type="image/x-icon">
  <link href="../assets/css/air-datepicker.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
  <!-- Sidebar Backdrop for Mobile -->
  <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <button class="sidebar-toggle desktop-toggle" id="sidebarToggle">
        <i class="fas fa-chevron-left"></i>
      </button>
      <a href="dashboard.php" class="sidebar-brand" id="sidebar-brand-logo">
        <img id="themeLogo" src="../assets/images/theme-logo-dark.png" alt="Logo">
        <!-- <span class="sidebar-brand-text">
          <?php //echo ($_SESSION['user_role'] === 'osas') ? strtoupper($_SESSION['user_role']) : ucfirst($_SESSION['user_role']) ?> Portal
        </span> -->
      </a>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section">
        <div class="nav-section-title">Main</div>
        
        <div class="nav-item">
          <a class="nav-link <?= is_active('dashboard.php') ?>" href="dashboard.php">
            <i class="nav-icon fas fa-home"></i>
            <span class="nav-text">Dashboard</span>
          </a>
        </div>

        <div class="nav-item">
          <a class="nav-link <?= is_active('requests.php') ?>" href="requests.php">
            <i class="nav-icon fas fa-clipboard-list"></i>
            <span class="nav-text">Requests</span>
          </a>
        </div>

        <div class="nav-item">
          <a class="nav-link <?= is_active('quotations.php') ?>" href="quotations.php">
            <i class="nav-icon fas fa-file-invoice-dollar"></i>
            <span class="nav-text">Quotations</span>
            <?php if ($_SESSION['user_role'] === 'student' && $unread_count > 0): ?>
              <span class="notification-badge" id="sidebarNotiBadge"><?= $unread_count ?></span>
            <?php endif; ?>
          </a>
        </div>
      </div>

      <?php if ($_SESSION['user_role'] === 'admin'): ?>
      <div class="nav-section">
        <div class="nav-section-title">Management</div>
        
        <div class="nav-item">
          <a class="nav-link <?= is_active('user_management.php') ?>" href="user_management.php">
            <i class="nav-icon fas fa-users"></i>
            <span class="nav-text">Users</span>
          </a>
        </div>

        <div class="nav-item">
          <a class="nav-link <?= is_active('societies.php') ?>" href="societies.php">
            <i class="nav-icon fas fa-university"></i>
            <span class="nav-text">Societies</span>
          </a>
        </div>
      </div>
      <?php endif; ?>

      <div class="nav-section">
        <div class="nav-section-title">Account</div>
        
        <div class="nav-item">
          <a class="nav-link <?= is_active('profile.php') ?>" href="profile.php">
            <i class="nav-icon fas fa-user-cog"></i>
            <span class="nav-text">Profile</span>
          </a>
        </div>

        <div class="nav-item">
          <a class="nav-link text-danger" href="logout.php">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <span class="nav-text">Logout</span>
          </a>
        </div>
      </div>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="main-content" id="mainContent">
    <!-- Header -->
    <header class="main-header">
      <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle mobile-toggle btn btn-outline-secondary" id="mobileToggle">
          <i class="fas fa-bars"></i>
        </button>
        <h1 class="header-title">
          <?php
          $page_titles = [
            'dashboard.php' => 'Dashboard',
            'requests.php' => 'Requests',
            'quotations.php' => 'Quotations',
            'user_management.php' => 'User Management',
            'societies.php' => 'Societies',
            'profile.php' => 'Profile'
          ];
          $current_page = basename($_SERVER['PHP_SELF']);
          echo isset($page_titles[$current_page]) ? $page_titles[$current_page] : 'Portal';
          ?>
        </h1>
      </div>

      <div class="header-actions">
        <?php if ($_SESSION['user_role'] === 'student'): ?>
        <!-- Notifications -->
        <div class="dropdown">
          <button class="btn btn-outline-secondary position-relative" type="button" data-bs-toggle="dropdown" style="border: none;">
            <i class="fas fa-bell"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill theme_bg_color" id="headerNotiBadge" style="font-size: 0.6rem;top: 9px !important;left: 27px !important;opacity: 0.8;"></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end notifications-dropdown" id="notifications-list" style="left: -260px">
            <li class="dropdown-item text-muted small">Loading...</li>
          </ul>
        </div>
        <?php endif; ?>

        <!-- Theme Toggle -->
        <div class="theme-toggle">
          <input type="checkbox" class="modeSwitch" id="modeSwitch">
          <label for="modeSwitch" class="modeSwitch-label">
            <i class="fas fa-moon"></i>
            <i class="fas fa-sun"></i>
            <span class="ball"></span>
          </label>
        </div>
      </div>
    </header>

    <!-- Content Area -->
    <div class="content-area">
      <!-- Your page content will go here -->