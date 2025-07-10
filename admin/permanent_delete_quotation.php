<?php
    require_once 'includes/db.php';
    require_once 'includes/auth.php';
    require_login();

    $id = intval($_GET['id']);
    $request_id = intval($_GET['request_id']);

    // cascade deletes due to foreign key constraints, if cascade delete not applied then uncomment all SQL code lines below
    if ($id) {
        // Delete related attachments
        $stmt = $pdo->prepare("SELECT filepath FROM quotation_attachments WHERE quotation_id = ? AND EXISTS (SELECT 1 FROM quotations WHERE id = ? AND status = 'Deleted')");
        $stmt->execute([$id, $id]);
        foreach ($stmt->fetchAll() as $file) {
            if (file_exists($file['filepath'])) {
                unlink($file['filepath']); // delete file
            }
        }

        // $pdo->prepare("DELETE FROM quotation_attachments WHERE quotation_id = ?")->execute([$id]);
        // $pdo->prepare("DELETE FROM quotation_items WHERE quotation_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM quotations WHERE id = ? AND status = 'Deleted'")->execute([$id]);
    }

    header("Location: quotations.php?request_id=$request_id");
