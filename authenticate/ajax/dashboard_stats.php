<?php
    require_once '../includes/db.php';
    require_once '../includes/auth.php';
    require_login();

    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['user_role'];

    if ($role === 'admin' || $role === 'osas') {
        // === Global statistics ===

        // Total requests
        $total_requests = $pdo->query("SELECT COUNT(*) FROM requests")->fetchColumn();

        // Quotations submitted today
        $today = date('Y-m-d');
        $quotations_today = $pdo->prepare("SELECT COUNT(*) FROM quotations WHERE DATE(submitted_at) = ?");
        $quotations_today->execute([$today]);
        $submitted_today = $quotations_today->fetchColumn();

        // Quotations this month
        $current_month = date('Y-m');
        $quotations_month = $pdo->prepare("SELECT COUNT(*) FROM quotations WHERE DATE_FORMAT(submitted_at, '%Y-%m') = ?");
        $quotations_month->execute([$current_month]);
        $month_total = $quotations_month->fetchColumn();

        // Pending quotations
        $pending_quotes = $pdo->query("SELECT COUNT(*) FROM quotations WHERE status = 'Pending'")->fetchColumn();

        // Vendors have quotations
        $vendor_count = $pdo->query("SELECT COUNT(DISTINCT vendor_id) FROM quotations")->fetchColumn();

        // Vendors have no quotations
        $inactive_vendor_count = $pdo->query("SELECT COUNT(*) 
            FROM vendors v
            LEFT JOIN quotations q ON v.id = q.vendor_id
            WHERE q.id IS NULL")->fetchColumn();

    } elseif ($role === 'student') {
        // === Student-specific statistics ===

        // Total requests by this student
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM requests WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $total_requests = $stmt->fetchColumn();

        // Quotations submitted today for this student's requests
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("SELECT COUNT(*) 
            FROM quotations q
            INNER JOIN requests r ON q.request_id = r.id
            WHERE r.user_id = ? AND DATE(q.submitted_at) = ?");
        $stmt->execute([$user_id, $today]);
        $submitted_today = $stmt->fetchColumn();

        // Quotations this month for this student's requests
        $current_month = date('Y-m');
        $stmt = $pdo->prepare("SELECT COUNT(*) 
            FROM quotations q
            INNER JOIN requests r ON q.request_id = r.id
            WHERE r.user_id = ? AND DATE_FORMAT(q.submitted_at, '%Y-%m') = ?");
        $stmt->execute([$user_id, $current_month]);
        $month_total = $stmt->fetchColumn();

        // Pending quotations for this student's requests
        $stmt = $pdo->prepare("SELECT COUNT(*) 
            FROM quotations q
            INNER JOIN requests r ON q.request_id = r.id
            WHERE r.user_id = ? AND q.status = 'Pending'");
        $stmt->execute([$user_id]);
        $pending_quotes = $stmt->fetchColumn();

        // Vendors who submitted quotations for this student's requests
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT q.vendor_id)
            FROM quotations q
            INNER JOIN requests r ON q.request_id = r.id
            WHERE r.user_id = ?");
        $stmt->execute([$user_id]);
        $vendor_count = $stmt->fetchColumn();

        // Vendors with no quotations for this student's requests
        $stmt = $pdo->prepare("SELECT COUNT(*) 
            FROM vendors v
            WHERE v.id NOT IN (
                SELECT DISTINCT q.vendor_id
                FROM quotations q
                INNER JOIN requests r ON q.request_id = r.id
                WHERE r.user_id = ?
            )");
        $stmt->execute([$user_id]);
        $inactive_vendor_count = $stmt->fetchColumn();
    }

    echo json_encode([
        'total_requests' => $total_requests,
        'submitted_today' => $submitted_today,
        'month_total' => $month_total,
        'pending_quotes' => $pending_quotes,
        'vendor_count' => $vendor_count,
        'inactive_vendor_count' => $inactive_vendor_count
    ]);