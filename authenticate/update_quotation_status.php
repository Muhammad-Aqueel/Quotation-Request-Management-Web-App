<?php
    require_once 'includes/db.php';
    require_once 'includes/auth.php';
    require_login();

    $id = intval($_GET['id']);
    $request_id = intval($_GET['request_id']);
    $status = $_GET['status'];

    $stmt = $pdo->prepare("SELECT * FROM requests r WHERE r.id = ? AND (r.user_id = ? OR EXISTS ( SELECT 1 FROM users u WHERE u.id = ? AND u.role IN ('admin', 'osas')))");
    $stmt->execute([$request_id, $_SESSION['user_id'], $_SESSION['user_id']]);
    $request = $stmt->fetch();

    if (!$request) {
        // Determine where to redirect
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (strpos($referer, 'view_quotation.php') !== false) {
            header("Location: view_quotation.php?id=$id");
        } else {
            header("Location: quotations.php?request_id=$request_id");
        }
        exit;
    }

    $allowed = ['Pending', 'Approved', 'Rejected', 'Deleted'];
    if (!in_array($status, $allowed)){
        header("Location: quotations.php?request_id=$request_id");
    }

    $stmt = $pdo->prepare("UPDATE quotations SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    // Determine where to redirect
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if (strpos($referer, 'view_quotation.php') !== false) {
        header("Location: view_quotation.php?id=$id");
    } else {
        header("Location: quotations.php?request_id=$request_id");
    }
