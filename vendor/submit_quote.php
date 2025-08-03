<?php
    session_start();
    // include 'includes/header.php';
    require_once 'includes/db.php';
    require_once '../authenticate/includes/functions.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $captcha = $_POST['captcha'] ?? '';
        $captcha_value = $_POST['captcha_value'] ?? '';

        if ($captcha !== $captcha_value) {
            echo "<div class='alert alert-warning'><i class='fas fa-exclamation-circle'></i> CAPTCHA verification failed.</div>";
            echo "<a href='quote.php?request_id=" . intval($_POST['request_id']) . "' class='btn btn-secondary mt-3'>Try Again</a>";
            header("Location: index.php");
        }

        $request_id = intval($_POST['request_id']);
        $name = trim($_POST['name']);
        $company = trim($_POST['company']);
        $ntn = trim($_POST['ntn']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $message = trim($_POST['message']);
        if(isset($_POST['request_item_id']) || isset($_POST['unit_price'])){
            $item_ids = $_POST['request_item_id'];
            $unit_prices = $_POST['unit_price'];
        } else {
            $item_ids = 0;
            $unit_prices = 0;
        }

        try {
            $pdo->beginTransaction();

            // Insert vendor
            $stmt = $pdo->prepare("INSERT INTO vendors (name, email, phone, company, ntn) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $company, $ntn]);
            $vendor_id = $pdo->lastInsertId();

            // Calculate total
            $total = 0;
            if($unit_prices != 0){
                foreach ($unit_prices as $i => $price) {
                    $qty_stmt = $pdo->prepare("SELECT quantity FROM request_items WHERE id = ?");
                    $qty_stmt->execute([$item_ids[$i]]);
                    $qty = $qty_stmt->fetchColumn();
                    $total += floatval($price) * intval($qty);
                }
            }
            // Insert quotation
            $stmt = $pdo->prepare("INSERT INTO quotations (vendor_id, request_id, total_amount, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$vendor_id, $request_id, $total, $message]);
            $quotation_id = $pdo->lastInsertId();

            // Insert items
            if($unit_prices != 0){
            foreach ($unit_prices as $i => $price) {
                $stmt = $pdo->prepare("INSERT INTO quotation_items (quotation_id, request_item_id, unit_price) VALUES (?, ?, ?)");
                $stmt->execute([$quotation_id, $item_ids[$i], floatval($price)]);
            }
            }
            // Handle attachments
            // if (empty($_FILES['attachments'])) print_r($_FILES['attachments']);

            $result = upload_files('attachments', '../uploads/quotation_attachments/', $quotation_id, $pdo);
            
            $file_skipped = '';

            if (!empty($result['skipped'])) {
                if($result['skipped'][0]['reason'] !== "Upload error"){
                    $file_skipped = '<div class="alert alert-warning"><h5><i class="fas fa-exclamation-circle"></i> File(s) were skipped.</h5>';
                    foreach ($result['skipped'] as $skip) {
                        $file_skipped .= '<h6><strong>' . htmlspecialchars($skip['name']) . '</strong>: ' . htmlspecialchars($skip['reason']) . '</h6>';
                    }
                    $file_skipped .= "</div>";
                }
            }

            $pdo->commit();
            $_SESSION['quote_submit_message'] = '<div class="alert alert-success text-center"><h4 class="text-center"><i class="fa-solid fa-file-arrow-up"></i> Quotation submitted successfully.</h4>'.$file_skipped.'</div>';
            header("Location: index.php");
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['quote_submit_message'] = '<div class="alert alert-danger text-center"><h4 class="text-center"><i class="fa-solid fa-triangle-exclamation"></i></i> Quotation submission failed: </h4>' . htmlspecialchars($e->getMessage()) . '</div>';
            header("Location: index.php");
        }
    } else {
        header("Location: index.php");
    }

    // include 'includes/footer.php';
?>

