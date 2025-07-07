<?php
    require_once 'includes/db.php';
    require_once 'includes/auth.php';
    require_login();
    
    require_once '../libs/tcpdf/tcpdf.php';

    $quotation_ids = $_POST['quotation_ids'] ?? [];
    if (empty($quotation_ids)) {
        die('<!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <title>Export PDF</title>
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
                        <i class="fas fa-ban"></i> No quotations selected.
                    </h4>
                </div>
            </div>
        </div>
        </body>
        </html>');
    }

    $pdf = new TCPDF();
    $pdf->SetCreator('Quotation App');
    $pdf->SetAuthor('System');
    $pdf->SetTitle('Selected Quotations');
    $pdf->AddPage();

    foreach ($quotation_ids as $id) {
        $id = intval($id);

        $stmt = $pdo->prepare("SELECT q.*, v.name, v.ntn, v.email, v.company, v.phone 
                            FROM quotations q 
                            JOIN vendors v ON q.vendor_id = v.id 
                            WHERE q.id = ?");
        $stmt->execute([$id]);
        $q = $stmt->fetch();

        $stmt = $pdo->prepare("SELECT requests.*,request_categories.id as cat_id,request_categories.name as category_name FROM requests JOIN request_categories ON requests.category_id = request_categories.id WHERE requests.id = ?");
        $stmt->execute([$q['request_id']]);
        $request = $stmt->fetch();
        
        $stmt = $pdo->prepare("SELECT ri.item_name, ri.quantity, qi.unit_price 
                            FROM quotation_items qi 
                            JOIN request_items ri ON qi.request_item_id = ri.id 
                            WHERE qi.quotation_id = ?");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll();

        // For quotation serial number_format
        $stmt = $pdo->prepare("WITH QuotationsList AS ( SELECT ROW_NUMBER() OVER (ORDER BY q.submitted_at) AS serial_number, r.id AS request_id, r.title, q.id AS quotation_id FROM requests r LEFT JOIN quotations q ON r.id = q.request_id WHERE r.id = ? ) SELECT * FROM QuotationsList WHERE quotation_id = ?;");
        $stmt->execute([$q['request_id'], $id]);
        $quote_serial = $stmt->fetch();

        // Build HTML
        $html = "<h2>Request Title: {$request['title']}</h2>";
        $html .= "<p><strong>Request Category:</strong> {$request['category_name']}</p>";
        $html .= "<p><strong>Description:</strong> {$request['description']}</p>";
        // $html .= "<h4>Quotation #{$q['id']}</h4>";
        $html .= "<h4>Quotation #{$quote_serial['serial_number']}</h4>";
        $html .= "<strong>Vendor Name:</strong> {$q['name']}<br>";
        $html .= "<strong>Company:</strong> {$q['company']}<br>";
        $html .= "<strong>NTN:</strong> {$q['ntn']}<br>";
        $html .= "<strong>Email:</strong> {$q['email']}<br>";
        $html .= "<strong>Phone:</strong> {$q['phone']}<br><br>";
        $html .= "<strong>Message/Notes:</strong> {$q['message']}<br><br>";

        $html .= "<table border='1' cellpadding='5'>
                    <thead>
                        <tr style='background-color:#f2f2f2;'>
                            <th><strong>Item</strong></th>
                            <th><strong>Qty</strong></th>
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
                    <td><strong>Total:</strong></td>
                    <td><strong>" . number_format($total, 2) . "</strong></td>
                </tr>";

        $html .= "</tbody></table><br><hr><br>";

        $pdf->writeHTML($html, true, false, true, false, '');
    }

    $pdf->Output('quotations.pdf', 'I');
