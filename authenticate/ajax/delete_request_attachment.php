<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login();

$id = intval($_POST['id'] ?? 0);
$request_id = intval($_POST['request_id'] ?? 0);

if ($id && $request_id) {
    // Get file path
    $stmt = $pdo->prepare("SELECT filepath FROM request_attachments WHERE id = ? AND request_id = ?");
    $stmt->execute([$id, $request_id]);
    $file = $stmt->fetch();

    if ($file && file_exists("../" . $file['filepath'])) {
        unlink("../" . $file['filepath']); // delete file
    }

    // Delete DB record
    $stmt = $pdo->prepare("DELETE FROM request_attachments WHERE id = ? AND request_id = ?");
    $stmt->execute([$id, $request_id]);

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);
