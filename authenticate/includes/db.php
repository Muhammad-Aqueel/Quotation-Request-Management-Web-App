<?php
$config_path = __DIR__ . '/../../config.php';

if (!file_exists($config_path)) {
    header("Location: ../install/index.php");
    exit;
}

require_once $config_path;

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
