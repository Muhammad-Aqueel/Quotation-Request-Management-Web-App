<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();

$request_id = intval($_POST['request_id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM requests r WHERE r.id = ? AND (r.user_id = ? OR (EXISTS ( SELECT 1 FROM users u WHERE u.id = ? AND u.role IN ('admin', 'osas')) AND r.status = '0' ))");
$stmt->execute([$request_id, $_SESSION['user_id'], $_SESSION['user_id']]);
$requests = $stmt->fetchAll();
if(!$requests){
  header("Location: quotations.php?request_id=$request_id");
  exit;
}

if ($request_id > 0) {
  $stmt = $pdo->prepare("UPDATE quotations SET is_read = 1 WHERE request_id = ? AND is_read = 0");
  $stmt->execute([$request_id]);
}

header("Location: quotations.php?request_id=$request_id");
