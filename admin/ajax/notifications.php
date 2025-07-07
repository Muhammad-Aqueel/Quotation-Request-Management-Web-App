<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login();

$stmt = $pdo->prepare("SELECT q.id, v.name, v.company, q.submitted_at 
                       FROM quotations q 
                       JOIN vendors v ON q.vendor_id = v.id 
                       WHERE q.is_read = 0 
                       ORDER BY q.submitted_at DESC");
$stmt->execute();

header('Content-Type: application/json');
echo json_encode($stmt->fetchAll());
