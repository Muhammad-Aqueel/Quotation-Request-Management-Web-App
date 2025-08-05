<?php
  function is_active($filename) {
    $current = basename($_SERVER['PHP_SELF']);
    return $current === $filename ? 'nav_active_link active' : '';
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Vendor Portal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="../assets/images/favicon.png" type="image/x-icon">
  <link rel="stylesheet" href="./assets/css/main.css">
</head>
<body class="bg-body-tertiary">
<nav class="navbar navbar-light bg-light shadow-sm sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">
      <!-- <img src="../assets/images/theme-logo.png" alt="theme logo" width="150"> -->
      <picture>
        <source media="(max-width: 767px)" srcset="../assets/images/favicon.png">
        <img id="themeLogo" src="../assets/images/theme-logo-dark.png" alt="theme logo" width="150" style="max-width: 100%;">
      </picture>
    </a>
    <a class="navbar-brand" href="index.php"><i class="fas fa-file-invoice"></i> Vendor Portal</a>
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link me-3 <?= is_active('index.php') ?>" href="index.php">
          <i class="fas fa-home"></i> Home
        </a>
      </li>
    </ul>
  </div>
</nav>

<div class="container-fluid mt-4 mb-5 pb-3">