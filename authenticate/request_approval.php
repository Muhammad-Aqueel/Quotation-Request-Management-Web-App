<?php
    require_once 'includes/db.php';
    require_once 'includes/auth.php';
    require_login();

    $id = intval($_GET['id']);
    $approval_status = $_GET['set'];

    $allowed = ['Pending', 'Approved', 'Rejected'];
    if (!in_array($approval_status, $allowed)){
        // Determine where to redirect
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (strpos($referer, 'view_request.php') !== false) {
            header("Location: view_request.php?id=$id");
        } else {
            header("Location: requests.php");
        }
    }

    if($approval_status == 'Pending' || $approval_status == 'Rejected'){
        // Mark this request as Purchase Order generated
        $updateStmt = $pdo->prepare("UPDATE requests SET purchase_order = '0', po_gt = ? WHERE id = ?");
        $updateStmt->execute([null, $id]);
    }

    $stmt = $pdo->prepare("UPDATE requests SET approval_status = ? WHERE id = ?");
    $stmt->execute([$approval_status, $id]);

    // Determine where to redirect
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if (strpos($referer, 'view_request.php') !== false) {
        header("Location: view_request.php?id=$id");
    } else {
        header("Location: requests.php");
    }
