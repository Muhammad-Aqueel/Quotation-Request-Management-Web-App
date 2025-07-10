<?php
    require_once 'includes/db.php';
    require_once 'includes/auth.php';
    require_login();

    $id = intval($_GET['id'] ?? 0);

    // cascade deletes due to foreign key constraints, if cascade delete not applied then uncomment all SQL code lines below
    if ($id) {
        // Delete request attachments (and files)
        $stmt = $pdo->prepare("SELECT filepath FROM request_attachments WHERE request_id = ?");
        $stmt->execute([$id]);
        foreach ($stmt->fetchAll() as $file) {
            if (file_exists($file['filepath'])) {
                unlink($file['filepath']);
            }
        }
        // $pdo->prepare("DELETE FROM request_attachments WHERE request_id = ?")->execute([$id]);

        // Get all request_item IDs
        $stmt = $pdo->prepare("SELECT id FROM request_items WHERE request_id = ?");
        $stmt->execute([$id]);
        $item_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Get quotation_ids from those items
        $in_clause = str_repeat('?,', count($item_ids) - 1) . '?';
        $stmt = $pdo->prepare("SELECT DISTINCT quotation_id FROM quotation_items WHERE request_item_id IN ($in_clause)");
        $stmt->execute($item_ids);
        $quotation_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Delete all quotation data
        if (!empty($quotation_ids)) {
            $in_quotes = str_repeat('?,', count($quotation_ids) - 1) . '?';

            // delete attached files
            $stmt = $pdo->prepare("SELECT filepath FROM quotation_attachments WHERE quotation_id IN ($in_quotes)");
            $stmt->execute($quotation_ids);
            foreach ($stmt->fetchAll() as $file) {
                if (file_exists($file['filepath'])) {
                    unlink($file['filepath']);
                }
            }

            // $pdo->prepare("DELETE FROM quotation_attachments WHERE quotation_id IN ($in_quotes)")->execute($quotation_ids);
            // $pdo->prepare("DELETE FROM quotation_items WHERE quotation_id IN ($in_quotes)")->execute($quotation_ids);
            // $pdo->prepare("DELETE FROM quotations WHERE id IN ($in_quotes)")->execute($quotation_ids);
        }

        // Delete items and finally request
        // $pdo->prepare("DELETE FROM request_items WHERE request_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM requests WHERE id = ?")->execute([$id]);
    }

    header("Location: requests.php");
