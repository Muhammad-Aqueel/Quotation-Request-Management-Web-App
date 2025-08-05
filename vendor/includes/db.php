<?php
$config_path = __DIR__ . '/../../config.php';

if (!file_exists($config_path)) {
    echo '<!DOCTYPE html>
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
    <div class="container mt-4 mb-5 pb-3">
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Unknown Error.</div><button onclick="location.reload()" class="btn btn-secondary theme_bg_color theme_border_color"><i class="fas fa-rotate-right"></i> Reload Page</button>';
    include 'includes/footer.php';
    exit;
}

require_once $config_path;

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo '<!DOCTYPE html>
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
    <div class="container mt-4 mb-5 pb-3">
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> "Database connection failed: "' . $e->getMessage() . '</div><button onclick="location.reload()" class="btn btn-secondary theme_bg_color theme_border_color"><i class="fas fa-rotate-right"></i> Reload Page</button>';
    include 'includes/footer.php';
    exit;
}
