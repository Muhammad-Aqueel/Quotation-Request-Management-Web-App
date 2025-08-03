<?php
  require_once 'includes/db.php';
  require_once 'includes/auth.php';
  require_login();
  require_once '../libs/tcpdf/tcpdf.php';

  $quotation_ids = $_POST['quotation_ids'] ?? [];
  if (!$quotation_ids) {
    include 'includes/header.php';
    echo ('
    <div class="container-fluid mt-4">
        <div class="col-6 m-auto bg-body p-4 shadow-sm rounded">
            <div class="mb-0 alert alert-warning text-center">
                <h4 class="mb-0 text-center">
                    <i class="fas fa-exclamation-circle"></i> No quotations selected.
                </h4>
            </div>
        </div>
    </div>');
    include 'includes/footer.php';
    exit;
  }

  $tempDir = __DIR__ . '/../uploads/tmp/';

  if (!is_dir($tempDir)) {
      mkdir($tempDir, 0755, true);
  }

  $zip_path = 'temp_export_' . time() . '.zip';
  $zip = new ZipArchive();
  $zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

  foreach ($quotation_ids as $id) {
      $id = intval($id);

      $stmt = $pdo->prepare("SELECT q.*, v.name, v.ntn, v.email, v.company, v.phone 
                            FROM quotations q 
                            JOIN vendors v ON q.vendor_id = v.id 
                            WHERE q.id = ?");
      $stmt->execute([$id]);
      $q = $stmt->fetch();

      $stmt = $pdo->prepare("SELECT r.id AS request_id, r.title, r.description, r.event_date, r.status, r.approval_status, r.created_at, rc.name AS category_name, s.society_name FROM requests r INNER JOIN request_categories rc ON r.category_id = rc.id INNER JOIN societies s ON r.society_id = s.id WHERE r.id = ?");
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
      
      $pdf = new TCPDF();
      $pdf->SetCreator('Quotation App');
      $pdf->SetTitle("Quotation #{$id}");
      $pdf->AddPage();

      // Build HTML
      $html = "<h2>Request Title: {$request['title']}</h2>";
      $html .= "<h4><strong>Event:</strong> {$request['description']}</h4>";
      $html .= "<strong>Event Date:</strong> {$request['event_date']}<br>";
      $html .= "<strong>Society:</strong> {$request['society_name']}<br>";
      $html .= "<strong>Category:</strong> {$request['category_name']}<br>";
      // $html .= "<h4>Quotation #{$q['id']}</h4>";
      $html .= "<h4>Quotation # {$quote_serial['serial_number']}</h4>";
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
                </tr></tbody></table>";

      $pdf->writeHTML($html, true, false, true, false, '');

      $tempPdfPath = $tempDir . "quotation_$id.pdf";
      $pdf->Output($tempPdfPath, 'F'); // ✅ Save to tmp dir
      $zip->addFile($tempPdfPath, "quotation_$id/quotation_$id.pdf");
      
      // $pdf_file = "quotation_{$id}.pdf";
      // $pdf->Output($pdf_file, 'F');
      // $zip->addFile($pdf_file, "quotation_{$id}/$pdf_file");

      // Add attachments
      $stmt = $pdo->prepare("SELECT * FROM quotation_attachments WHERE quotation_id = ?");
      $stmt->execute([$id]);
      foreach ($stmt->fetchAll() as $att) {
          if (file_exists($att['filepath'])) {
              $zip->addFile($att['filepath'], "quotation_{$id}/attachments/{$att['filename']}");
          }
      }
  }

  $zip->close();

  header('Content-Type: application/zip');
  header("Content-Disposition: attachment; filename=\"quotations_export.zip\"");
  header('Content-Length: ' . filesize($zip_path));
  readfile($zip_path);
  // ✅ Clean up PDFs
  foreach ($quotation_ids as $id) {
    $tempFile = $tempDir . "quotation_$id.pdf";
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }
  }
  unlink($zip_path); // cleanup
