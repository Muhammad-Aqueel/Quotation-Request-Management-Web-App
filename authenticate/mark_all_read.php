<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();

$request_id = intval($_POST['request_id'] ?? 0);

if ($request_id > 0) {
  $stmt = $pdo->prepare("UPDATE quotations SET is_read = 1 WHERE request_id = ? AND is_read = 0");
  $stmt->execute([$request_id]);
}

header("Location: quotations.php?request_id=$request_id");
