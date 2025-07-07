<?php
    require_once 'includes/db.php';
    require_once 'includes/auth.php';
    require_login();

    $id = intval($_GET['id'] ?? 0);

    // Step 1: Get all quotation IDs related to this request
    $stmt = $pdo->prepare("SELECT id FROM quotations WHERE request_id = ?");
    $stmt->execute([$id]);
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

    // delete attached files
    $stmt = $pdo->prepare("SELECT filepath FROM request_attachments WHERE request_id = ?");
    $stmt->execute([$id]);
    foreach ($stmt->fetchAll() as $row) {
        if (file_exists($row['filepath'])) unlink($row['filepath']);
    }

    // cascade deletes due to foreign key constraints
    $stmt = $pdo->prepare("DELETE FROM requests WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: requests.php");
