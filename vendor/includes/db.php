<?php
$config_path = __DIR__ . '/../../config.php';

if (!file_exists($config_path)) {
    echo "<div class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> Unkown Error.</div>";
    exit;
}

require_once $config_path;

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
