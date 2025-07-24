<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login();

$last = intval($_GET['last_id'] ?? 0);

$stmt = $pdo->prepare("SELECT id FROM quotations WHERE id > ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$last]);
$new = $stmt->fetch();

if ($new) {
    echo json_encode(['new_id' => $new['id']]);
} else {
    echo json_encode(['new_id' => 0]);
}
