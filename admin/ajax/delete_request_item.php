<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login();

$item_id = intval($_POST['item_id'] ?? 0);
$request_id = intval($_POST['request_id'] ?? 0);

if ($item_id && $request_id) {
  $stmt = $pdo->prepare("DELETE FROM request_items WHERE id = ? AND request_id = ?");
  $stmt->execute([$item_id, $request_id]);

  if ($stmt->rowCount()) {
    echo json_encode(['success' => true]);
    exit;
  }
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);
