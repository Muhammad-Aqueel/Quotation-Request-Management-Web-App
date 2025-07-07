<?php
require_once 'includes/db.php';
require_once 'includes/csrf.php';
require_once 'includes/auth.php';
require_login();
require_once 'includes/functions.php';

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die('<!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <title>Add Request</title>
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
            <a href="requests.php" class="btn btn-secondary btn-sm mt-3"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>
    </body>
    </html>');
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $title = trim($_POST['title']);
    $category_id = trim($_POST['category_id']);
    $desc = trim($_POST['description']);
    $items = $_POST['item_name'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
}

if (!$title || count($items) < 1) {
    die('<!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <title>Add Request</title>
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
                    <i class="fas fa-exclamation-circle"></i> Title and at least one item required.
                </h4>
            </div>
            <a href="requests.php" class="btn btn-secondary btn-sm mt-3"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>
    </body>
    </html>');
}

$stmt = $pdo->prepare("INSERT INTO requests (title, category_id, description, status) VALUES (?, ?, ?, ?)");
$stmt->execute([$title, $category_id, $desc, '1']);
$request_id = $pdo->lastInsertId();

for ($i = 0; $i < count($items); $i++) {
    $stmt = $pdo->prepare("INSERT INTO request_items (request_id, item_name, quantity) VALUES (?, ?, ?)");
    $stmt->execute([$request_id, trim($items[$i]), intval($quantities[$i])]);
}

$files = upload_files('attachments', '../uploads/request_attachments/');
foreach ($files['uploaded'] as $f) {
    $stmt = $pdo->prepare("INSERT INTO request_attachments (request_id, filename, filepath) VALUES (?, ?, ?)");
    $stmt->execute([$request_id, $f['name'], $f['path']]);
}

$file_skipped = '';
if (!empty($files['skipped'])) {
    if($files['skipped'][0]['reason'] !== "Upload error"){
        $file_skipped = '<div class="alert alert-warning"><h5><i class="fas fa-exclamation-triangle"></i> File(s) were skipped.</h5>';
        foreach ($files['skipped'] as $skip) {
            $file_skipped .= '<h6><strong>' . htmlspecialchars($skip['name']) . '</strong>: ' . htmlspecialchars($skip['reason']) . '</h6>';
        }
        $file_skipped .= "</div>";
    }
}


$_SESSION['request_add_message'] = '<div class="alert alert-success text-center"><h4 class="text-center"><i class="fa-solid fa-file-arrow-up"></i> Request submitted successfully.</h4>'.$file_skipped.'</div>';

header("Location: requests.php");
