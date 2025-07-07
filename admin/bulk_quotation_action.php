<?php
require 'includes/db.php';
require 'includes/auth.php';
require_login();

$ids = $_POST['quotation_ids'] ?? [];
$action = $_POST['action'] ?? '';
$request_id = $_POST['request_id'] ?? 0;

if (empty($ids) || !$action) {
  header("Location: quotations.php?request_id=$request_id");
  exit;
}

$id_placeholders = implode(',', array_fill(0, count($ids), '?'));

switch ($action) {
  case 'approve':
    $stmt = $pdo->prepare("UPDATE quotations SET status = 'Approved' WHERE id IN ($id_placeholders)");
    break;
  case 'reject':
    $stmt = $pdo->prepare("UPDATE quotations SET status = 'Rejected' WHERE id IN ($id_placeholders)");
    break;
  case 'delete':
    $stmt = $pdo->prepare("UPDATE quotations SET status = 'Deleted' WHERE id IN ($id_placeholders)");
    break;
  case 'restore':
    $stmt = $pdo->prepare("UPDATE quotations SET status = 'Pending' WHERE id IN ($id_placeholders)");
    break;
  case 'permanent_delete':
    // Get all quotation's filepath
    $stmt = $pdo->prepare("SELECT filepath FROM quotation_attachments 
    WHERE quotation_id IN ($id_placeholders)");
    $stmt->execute($ids);
    
    // For each quotation, delete attachments from disk
    foreach ($stmt->fetchAll() as $file) {
      if (file_exists($file['filepath'])) {
        unlink($file['filepath']);
      }
    }
    // delete quotations
    $stmt = $pdo->prepare("DELETE FROM quotations WHERE id IN ($id_placeholders)");
    break;
  default:
    exit('Invalid action');
}

$stmt->execute($ids);
header("Location: quotations.php?request_id=$request_id");
