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

  $ids = array_map('intval', $ids);
  $id_placeholders = implode(',', array_fill(0, count($ids), '?'));
echo $id_placeholders . "<br>";
print_r($ids);
  switch ($action) {
    // cascade deletes due to foreign key constraints, if cascade delete not applied then uncomment all SQL code lines below
    case 'delete':
      // Delete request_attachments (files and db)
      $stmt = $pdo->prepare("SELECT filepath FROM request_attachments WHERE request_id IN ($id_placeholders)");
      $stmt->execute($ids);
      foreach ($stmt->fetchAll() as $row) {
        if (file_exists($row['filepath'])) unlink($row['filepath']);
      }
      // $pdo->prepare("DELETE FROM request_attachments WHERE request_id IN ($id_placeholders)")->execute($ids);

      // Get all quotation IDs for these requests
      $stmt = $pdo->prepare("SELECT id FROM quotations WHERE request_id IN ($id_placeholders)");
      $stmt->execute($ids);
      $quotation_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

      if ($quotation_ids) {
        $qid_placeholders = implode(',', array_fill(0, count($quotation_ids), '?'));

        // Delete quotation attachment files
        $stmt = $pdo->prepare("SELECT filepath FROM quotation_attachments WHERE quotation_id IN ($qid_placeholders)");
        $stmt->execute($quotation_ids);
        foreach ($stmt->fetchAll() as $row) {
          if (file_exists($row['filepath'])) unlink($row['filepath']);
        }

        // Delete entries
        // $pdo->prepare("DELETE FROM quotation_attachments WHERE quotation_id IN ($qid_placeholders)")->execute($quotation_ids);
        // $pdo->prepare("DELETE FROM quotation_items WHERE quotation_id IN ($qid_placeholders)")->execute($quotation_ids);
        // $pdo->prepare("DELETE FROM quotations WHERE id IN ($qid_placeholders)")->execute($quotation_ids);
      }

      // Delete request_items and requests
      // $pdo->prepare("DELETE FROM request_items WHERE request_id IN ($id_placeholders)")->execute($ids);
      $pdo->prepare("DELETE FROM requests WHERE id IN ($id_placeholders)")->execute($ids);
      break;

    case 'activate':
      $pdo->prepare("UPDATE requests SET status = '1' WHERE id IN ($id_placeholders)")->execute($ids);
      break;

    case 'deactivate':
      $pdo->prepare("UPDATE requests SET status = '0' WHERE id IN ($id_placeholders)")->execute($ids);
      break;

    default:
      exit('Invalid action');
  }

  header("Location: requests.php");
  exit;
