<?php
  require 'includes/db.php';
  require 'includes/auth.php';
  require_login();

  $ids = $_POST['quotation_ids'] ?? [];
  $action = $_POST['action'] ?? '';
  $request_id = intval($_POST['request_id'] ?? 0);

  if (empty($ids) || !$action) {
    header("Location: quotations.php?request_id=$request_id");
    exit;
  }

  $ids = array_map('intval', $ids);
  $id_placeholders = implode(',', array_fill(0, count($ids), '?'));

  switch ($action) {
    case 'approve':
      $pdo->prepare("UPDATE quotations SET status = 'Approved' WHERE id IN ($id_placeholders)")->execute($ids);
      break;

    case 'reject':
      $pdo->prepare("UPDATE quotations SET status = 'Rejected' WHERE id IN ($id_placeholders)")->execute($ids);
      break;

    case 'delete':
      $pdo->prepare("UPDATE quotations SET status = 'Deleted' WHERE id IN ($id_placeholders)")->execute($ids);
      break;

    case 'restore':
      $pdo->prepare("UPDATE quotations SET status = 'Pending' WHERE id IN ($id_placeholders)")->execute($ids);
      break;

    // cascade deletes due to foreign key constraints, if cascade delete not applied then uncomment all SQL code lines below
    case 'permanent_delete':
      // Delete files
      $stmt = $pdo->prepare("SELECT filepath FROM quotation_attachments WHERE quotation_id IN ($id_placeholders)");
      $stmt->execute($ids);
      foreach ($stmt->fetchAll() as $row) {
        if (file_exists($row['filepath'])) unlink($row['filepath']);
      }

      // Delete related data
      // $pdo->prepare("DELETE FROM quotation_attachments WHERE quotation_id IN ($id_placeholders)")->execute($ids);
      // $pdo->prepare("DELETE FROM quotation_items WHERE quotation_id IN ($id_placeholders)")->execute($ids);
      $pdo->prepare("DELETE FROM quotations WHERE id IN ($id_placeholders)")->execute($ids);
      break;

    default:
      exit('Invalid action');
  }

  header("Location: quotations.php?request_id=$request_id");
  exit;
