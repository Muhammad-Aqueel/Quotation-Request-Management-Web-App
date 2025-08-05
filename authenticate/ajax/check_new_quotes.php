<?php
    require_once '../includes/db.php';
    require_once '../includes/auth.php';
    require_login();

    $last = intval($_GET['last_id'] ?? 0);

    $stmt = $pdo->prepare("SELECT q.id FROM quotations q JOIN requests r ON q.request_id = r.id WHERE r.user_id = ? AND q.id > ? ORDER BY q.id DESC LIMIT 1");
    $stmt->execute([$_SESSION['user_id'],$last]);
    $new = $stmt->fetch();

    if ($new) {
        echo json_encode(['new_id' => $new['id']]);
    } else {
        echo json_encode(['new_id' => 0]);
    }