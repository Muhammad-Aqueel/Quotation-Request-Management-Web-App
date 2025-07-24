<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();

$id = intval($_GET['id']);
$status = $_GET['status'];

$allowed = ['Pending', 'Approved', 'Rejected', 'Deleted'];
if (!in_array($status, $allowed)) die("Invalid status.");

$stmt = $pdo->prepare("UPDATE quotations SET status = ? WHERE id = ?");
$stmt->execute([$status, $id]);

header("Location: view_quotation.php?id=$id");
