<?php
require 'includes/db.php';
require 'includes/auth.php';
require_login();

$ids = $_POST['request_ids'] ?? [];
$action = $_POST['action'] ?? '';

if (empty($ids) || !$action) {
  header("Location: requests.php");
  exit;
}

$id_placeholders = implode(',', array_fill(0, count($ids), '?'));

switch ($action) {
  case 'delete':
    // Step 1: Get all quotation IDs related to this request
    $stmt = $pdo->prepare("SELECT id FROM quotations WHERE request_id IN ($id_placeholders)");
    $stmt->execute($ids);
    $quotation_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Step 2: For each quotation, delete attachments from disk
    foreach ($quotation_ids as $qid) {
        $stmt = $pdo->prepare("SELECT filepath FROM quotation_attachments WHERE quotation_id = ?");
        $stmt->execute([$qid]);
        $attachments = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($attachments as $file) {
            if (file_exists($file)) {
                unlink($file); // Delete file
            }
        }
    }
    // delete request's attached files
    $stmt = $pdo->prepare("SELECT filepath FROM request_attachments WHERE request_id IN ($id_placeholders)");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $row) {
        if (file_exists($row['filepath'])) unlink($row['filepath']);
    }
    // delete requests
    $stmt = $pdo->prepare("DELETE FROM requests WHERE id IN ($id_placeholders)");
    break;
  case 'activate':
    $stmt = $pdo->prepare("UPDATE requests SET status = '1' WHERE id IN ($id_placeholders)");
    break;
  case 'deactivate':
    $stmt = $pdo->prepare("UPDATE requests SET status = '0' WHERE id IN ($id_placeholders)");
    break;
  default:
    exit('Invalid action');
}

$stmt->execute($ids);
header("Location: requests.php");
