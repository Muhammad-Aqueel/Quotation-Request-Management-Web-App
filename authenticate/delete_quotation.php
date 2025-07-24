<?php
    require_once 'includes/db.php';
    require_once 'includes/auth.php';
    require_login();

    $id = intval($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("UPDATE quotations SET status = 'Deleted' WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: quotations.php");
