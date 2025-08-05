<?php
    require_once '../includes/db.php';
    require_once '../includes/auth.php';
    require_login();

    $stmt = $pdo->prepare("SELECT q.id, v.name, v.company, q.submitted_at, r.user_id AS request_creator_id FROM quotations q JOIN vendors v ON q.vendor_id = v.id JOIN requests r ON q.request_id = r.id WHERE q.is_read = 0 ORDER BY q.submitted_at DESC");
    $stmt->execute();
    $notifications = $stmt->fetchAll();

    header('Content-Type: application/json');
    $notifications_list = [];
    foreach ($notifications as $notification){
        if($_SESSION['user_id'] == $notification['request_creator_id']){
            $notifications_list[] = $notification;
        }
    }

    echo json_encode($notifications_list);