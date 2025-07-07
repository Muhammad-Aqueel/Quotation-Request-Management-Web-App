<?php
require_once 'includes/db.php';
require_once 'includes/csrf.php';
require_once 'includes/auth.php';
require_login();
require_once 'includes/functions.php';

$id = intval($_POST['id'] ?? 0); // request ID
$title = trim($_POST['title'] ?? '');
$category_id = trim($_POST['category_id'] ?? '');
$description = trim($_POST['description'] ?? '');
if(isset($_POST['item_id']) || isset($_POST['item_name']) || isset($_POST['quantity'])){
    $items_id = $_POST['item_id'] ?? [];
    $items = $_POST['item_name'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die('<!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <title>Update Request</title>
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <!-- Font Awesome CDN -->
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
      <!-- Bootstrap CSS -->
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
      <link rel="shortcut icon" href="../assets/images/favicon.png" type="image/x-icon">
      <style>
        a{
          text-decoration: none;
        }
      </style>
    </head>
    <body class="bg-light">
    <div class="container-fluid mt-4">
        <div class="col-6 m-auto bg-white p-4 shadow-sm rounded">
            <div class="mb-0 alert alert-danger text-center">
                <h4 class="mb-0 text-center">
                    <i class="fas fa-ban"></i> Invalid information.
                </h4>
            </div>
            <a href="edit_request.php?id=' . $id . '" class="btn btn-secondary btn-sm mt-3"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>
    </body>
    </html>');
}

// || count($items) < 1
if (!$id || !$title ) {
    header("Location: requests.php");
    exit;
}

// 1. Update request record
$stmt = $pdo->prepare("UPDATE requests SET title = ?, category_id = ?, description = ? WHERE id = ?");
$stmt->execute([$title, $category_id, $description, $id]);

// 2. Update old items and insert new ones
if(isset($_POST['item_id']) || isset($_POST['item_name']) || isset($_POST['quantity'])){
    foreach ($items as $i => $item_name) {
        $item_name = trim($item_name);
        $qty = intval($quantities[$i]);
        $item_id = intval($items_id[$i] ?? 0);

        if ($item_name && $qty > 0) {
            if ($item_id > 0) {
                // ✅ Update existing item
                $stmt = $pdo->prepare("UPDATE request_items SET item_name = ?, quantity = ? WHERE id = ? AND request_id = ?");
                $stmt->execute([$item_name, $qty, $item_id, $id]);
            } else {
                // ✅ Insert new item
                $stmt = $pdo->prepare("INSERT INTO request_items (request_id, item_name, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$id, $item_name, $qty]);
            }
        }
    }
}

// 3. Upload and store new attachments (keep old ones)
$uploaded = upload_files('attachments', '../uploads/request_attachments/');

foreach ($uploaded['uploaded'] as $file) {
    $stmt = $pdo->prepare("INSERT INTO request_attachments (request_id, filename, filepath) VALUES (?, ?, ?)");
    $stmt->execute([$id, $file['name'], $file['path']]);
}

$file_skipped = '';

if (!empty($uploaded['skipped'])) {
    if($uploaded['skipped'][0]['reason'] !== "Upload error"){
        $file_skipped = '<div class="alert alert-warning"><h5><i class="fas fa-exclamation-triangle"></i> File(s) were skipped.</h5>';
        foreach ($uploaded['skipped'] as $skip) {
            $file_skipped .= '<h6><strong>' . htmlspecialchars($skip['name']) . '</strong>: ' . htmlspecialchars($skip['reason']) . '</h6>';
        }
        $file_skipped .= "</div>";
    }
}

$_SESSION['request_update_message'] = '<div class="alert alert-success text-center"><h4 class="text-center"><i class="fa-solid fa-file-arrow-up"></i> Request update successfully.</h4>'.$file_skipped.'</div>';

header("Location: edit_request.php?id=$id");
