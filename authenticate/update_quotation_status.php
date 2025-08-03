<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();

$id = intval($_GET['id']);
$request_id = intval($_GET['request_id']);
$status = $_GET['status'];

$allowed = ['Pending', 'Approved', 'Rejected', 'Deleted'];
if (!in_array($status, $allowed)){
    header("Location: quotations.php?request_id=$request_id");
}

$stmt = $pdo->prepare("UPDATE quotations SET status = ? WHERE id = ?");
$stmt->execute([$status, $id]);

header("Location: quotations.php?request_id=$request_id");
