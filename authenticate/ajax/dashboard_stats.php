<?php
    require_once '../includes/db.php';
    require_once '../includes/auth.php';
    require_login();

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
    $inactive_vendor_count = $pdo->query("SELECT COUNT(*) AS inactive_vendor_count
    FROM vendors v
    LEFT JOIN quotations q ON v.id = q.vendor_id
    WHERE q.id IS NULL;")->fetchColumn();

    echo json_encode(['total_requests' => $total_requests, 'submitted_today' => $submitted_today, 'month_total' => $month_total, 'pending_quotes' => $pending_quotes, 'vendor_count' => $vendor_count, 'inactive_vendor_count' => $inactive_vendor_count]);
    