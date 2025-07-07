<?php

function sanitize_filename($filename) {
    return preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);
}

function upload_files($input_name, $upload_dir, $quotation_id = null, $pdo = null) {
    if (empty($_FILES[$input_name])) return ['uploaded' => '', 'skipped' => ''];

    $uploaded = [];
    $skipped = [];

    $max_file_size = 1024 * 1024; // 1MB per file
    $max_total_size = 5 * 1024 * 1024; // 5MB total

    $allowed_mimes = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $total_size = array_sum($_FILES[$input_name]['size']);

    if ($total_size > $max_total_size) {
        return [
            'uploaded' => [],
            'skipped' => [['name' => 'ALL FILES', 'reason' => 'Total file size exceeds 5MB']]
        ];
    }

    foreach ($_FILES[$input_name]['tmp_name'] as $i => $tmp) {
        $error = $_FILES[$input_name]['error'][$i];
        $size = $_FILES[$input_name]['size'][$i];
        $original = sanitize_filename($_FILES[$input_name]['name'][$i]);

        if ($error !== UPLOAD_ERR_OK) {
            $skipped[] = ['name' => $original, 'reason' => 'Upload error'];
            continue;
        }

        if ($size > $max_file_size) {
            $skipped[] = ['name' => $original, 'reason' => 'File exceeds 1MB'];
            continue;
        }

        // Secure MIME type validation
        $mime = $finfo->file($tmp);
        if (!in_array($mime, $allowed_mimes)) {
            $skipped[] = ['name' => $original, 'reason' => "Invalid file type: $mime"];
            continue;
        }

        // Final destination
        $filename = time() . '_' . bin2hex(random_bytes(4)) . '_' . $original;
        $dest = rtrim($upload_dir, '/') . '/' . $filename;

        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        if (!move_uploaded_file($tmp, $dest)) {
            $skipped[] = ['name' => $original, 'reason' => 'Failed to move file'];
            continue;
        }

        if ($quotation_id && $pdo) {
            $stmt = $pdo->prepare("INSERT INTO quotation_attachments (quotation_id, filename, filepath) VALUES (?, ?, ?)");
            $stmt->execute([$quotation_id, $original, $dest]);
        }

        $uploaded[] = ['name' => $original, 'path' => $dest];
    }

    return ['uploaded' => $uploaded, 'skipped' => $skipped];
}
