<?php
    require_once 'includes/db.php';
    require_once 'includes/auth.php';
    require_login();
    require_admin();

    require_once '../libs/tcpdf/tcpdf.php';

    $request_id = $_GET['request_id'] ?? null;
    if (!$request_id || !is_numeric($request_id)) {
        include 'includes/header.php';
        echo "<div class='container'><div class='alert alert-warning'><i class='fas fa-exclamation-circle'></i> Invalid request.</div><a href='requests.php' class='btn btn-secondary btn-sm mt-3'><i class='fas fa-arrow-left'></i> Back</a></div>";
        include 'includes/footer.php';
        exit;
    }

    $request_id = intval($request_id);

    // Fetch request details + username
    $stmt = $pdo->prepare("
    SELECT r.id AS request_id, r.title, r.description, r.event_date, r.status, r.approval_status, r.created_at, rc.name AS category_name, s.society_name, u_creator.username AS created_by, ( SELECT COUNT(*) FROM quotations q WHERE q.request_id = r.id ) AS quotations_count FROM requests r INNER JOIN request_categories rc ON r.category_id = rc.id INNER JOIN societies s ON r.society_id = s.id INNER JOIN users u_creator ON r.user_id = u_creator.id WHERE r.id = ? AND r.status = '0' AND EXISTS ( SELECT 1 FROM users u WHERE u.id = ? AND u.role = 'admin')
    ");
    $stmt->execute([$request_id, $_SESSION['user_id']]);
    $request = $stmt->fetch();

    if (!$request) {
        include 'includes/header.php';
        echo "<div class='container'><div class='alert alert-warning'><i class='fas fa-exclamation-circle'></i> Request not found.</div><a href='requests.php' class='btn btn-secondary btn-sm mt-3'><i class='fas fa-arrow-left'></i> Back</a></div>";
        include 'includes/footer.php';
        exit;
    }

    // Mark this request as Purchase Order generated
    $updateStmt = $pdo->prepare("UPDATE requests SET purchase_order = '1' WHERE id = ? AND approval_status = 'Approved'");
    $updateStmt->execute([$request_id]);

    // Fetch approved quotations
    $stmt = $pdo->prepare("
        SELECT q.*, v.name, v.email, v.phone, v.company, v.ntn
        FROM quotations q
        JOIN vendors v ON q.vendor_id = v.id
        WHERE q.request_id = ? AND q.status = 'Approved'
    ");
    $stmt->execute([$request_id]);
    $quotations = $stmt->fetchAll();

    $pdf = new TCPDF();
    $pdf->SetCreator('Purchase Order System');
    $pdf->SetAuthor('System');
    $pdf->SetTitle('Purchase Order - Request #' . $request_id);
    $pdf->AddPage();

    // Request Header
    $html = "<h2>Purchase Order for Request: {$request['title']}</h2>";
    $html .= "<strong>Requested By:</strong> {$request['created_by']}<br>";
    $html .= "<strong>Event Date:</strong> {$request['event_date']}<br>";
    $html .= "<strong>Society:</strong> {$request['society_name']}<br>";
    $html .= "<strong>Category:</strong> {$request['category_name']}<br>";
    $html .= "<strong>Description:</strong> {$request['description']}<br><br>";

    if (empty($quotations)) {
        $html .= "<div>No approved quotations found for this request.</div>";
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('purchase_order.pdf', 'I');
        exit;
    }

    // For each approved quotation
    foreach ($quotations as $i => $q) {
        // Quotation items
        $stmt = $pdo->prepare("
            SELECT ri.item_name, ri.quantity, qi.unit_price 
            FROM quotation_items qi
            JOIN request_items ri ON qi.request_item_id = ri.id
            WHERE qi.quotation_id = ?
        ");
        $stmt->execute([$q['id']]);
        $items = $stmt->fetchAll();
        
        // For quotation serial number_format
        $html .= "<hr><h3>Approved Quotation #" . ($i + 1) . "</h3>";
        // $html .= "<hr><h3>Approved Quotation #{$q['id']}</h3>";

        $html .= "<strong>Vendor:</strong> {$q['name']}<br>";
        $html .= "<strong>Company:</strong> {$q['company']}<br>";
        $html .= "<strong>NTN:</strong> {$q['ntn']}<br>";
        $html .= "<strong>Email:</strong> {$q['email']}<br>";
        $html .= "<strong>Phone:</strong> {$q['phone']}<br>";
        $html .= "<strong>Message:</strong> {$q['message']}<br><br>";

        $html .= "<table border='1' cellpadding='5'>
            <thead>
                <tr style='background-color:#f2f2f2;'>
                    <th><strong>Item</strong></th>
                    <th><strong>Quantity</strong></th>
                    <th><strong>Unit Price</strong></th>
                    <th><strong>Total</strong></th>
                </tr>
            </thead><tbody>";

        $total = 0;
        foreach ($items as $item) {
            $line = $item['unit_price'] * $item['quantity'];
            $total += $line;
            $html .= "<tr>
                <td>{$item['item_name']}</td>
                <td>{$item['quantity']}</td>
                <td>" . number_format($item['unit_price'], 2) . "</td>
                <td>" . number_format($line, 2) . "</td>
            </tr>";
        }

        $html .= "<tr style='background-color:#eaf4fc;'>
            <td></td>
            <td></td>
            <td><strong>Total</strong></td>
            <td><strong>" . number_format($total, 2) . "</strong></td>
        </tr>";

        $html .= "</tbody></table><br>";
    }

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('purchase_order.pdf', 'I');