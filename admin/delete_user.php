<?php
require 'includes/db.php';
require 'includes/auth.php';
require_login();
require_admin();

$id = intval($_GET['id'] ?? 0);
if ($id) {
  $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
}

header("Location: user_management.php");
exit;
