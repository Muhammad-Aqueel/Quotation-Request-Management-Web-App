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
      background-color: white;
    }
    .theme_outline_btn_color:hover{
      background-color : #903035 !important;
      border: 1px solid #903035 !important;
      color: #fff !important;
      box-shadow: 0px 0px 10px #6c0006cc;
    }
    body {
      font-family: 'Inter', sans-serif;
    }
    .nav-link .fas{
      font-size: 24px;
      margin-bottom: 0.5rem!important;
    }
    .navbar .nav-link, .nav-link #quoteBell{
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .bg-img{
      background-image: url('../assets/images/favicon.png');
      background-position: center;
      background-repeat: no-repeat;
      background-attachment: fixed;
      min-width: 100%;
      position: fixed;
      min-height: 100%;
      z-index: -1;
      opacity: .3;
    }
    @media (max-width: 767px) {
      picture img {
        width: 50px !important;
      }
    }
    /* Sidebar widget styling */
    .filter-sidebar {
      position: fixed;
      top: 150px;
      right: -336px;
      width: 320px;
      /* height: calc(100vh - 100px); */
      /* background: #fff; */
      transition: right 0.3s ease-in-out;
      z-index: 1050;
      /* border-left: 1px solid #ddd; */
      border-radius: 6px 0px 0px 6px;
      /* box-shadow: -2px 0 5px rgb(0 0 0 / 10%); */
    }

    /* When active (slide in) */
    .filter-sidebar.active {
      right: -1px;
    }

    /* Toggle Button styling */
    .toggle-filter-btn {
      position: fixed;
      top: 100px;
      right: -42px;
      z-index: 1060;
      padding: 8px 12px;
      border-radius: 4px 0 0 4px;
      transition: right 0.3s ease-in-out;
    }

    /* When active (slide in) */
    .toggle-filter-btn.active {
      right: 0px;
    }
</style>
</head>
<body>
<nav class="navbar navbar-light bg-light shadow-sm sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">
      <!-- <img src="../assets/images/theme-logo.png" alt="theme logo" width="150"> -->
      <picture>
        <source media="(max-width: 767px)" srcset="../assets/images/favicon.png">
        <img src="../assets/images/theme-logo.png" alt="theme logo" width="150" style="max-width: 100%;">
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

<div class="container mt-4 mb-5 pb-3">

