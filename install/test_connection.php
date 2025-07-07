<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['host'];
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    if(isset($_POST['name'])){
        $name = $_POST['name'];
    }

    try {
        if(isset($_POST['name'])){
            $pdo = new PDO("mysql:host=$host", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("USE `$name`");

            echo "<div class='alert alert-success'>Connected to database <strong>" . htmlspecialchars($name) . "</strong></div>";
        } else {
            $pdo = new PDO("mysql:host=$host", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<div class='alert alert-success'>Host connection successful <strong></strong></div>";
        }
    } catch (PDOException $e) {
        if(isset($_POST['name'])){
            error_log("DB Error: " . $e->getMessage()); // Log it
            echo "<div class='alert alert-danger'>" . htmlspecialchars("Database connection failed") . "</div>";
        } else {
            error_log("Host Error: " . $e->getMessage()); // Log it
            echo "<div class='alert alert-danger'>" . htmlspecialchars("Host connection failed") . "</div>";
        }
    }
}
