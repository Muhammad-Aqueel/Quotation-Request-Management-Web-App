<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();
require_once '../libs/fpdf/fpdf.php';

// --- Custom PDF class with footer ---
class PDF extends FPDF {
    function Footer() {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(128);

        // Page number
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

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

foreach ($quotation_ids as $i => $id) {
    $id = intval($id);

    // Fetch quotation
    $stmt = $pdo->prepare("SELECT q.*, v.name, v.ntn, v.email, v.company, v.phone 
                          FROM quotations q 
                          JOIN vendors v ON q.vendor_id = v.id 
                          WHERE q.id = ?");
    $stmt->execute([$id]);
    $q = $stmt->fetch();

    // Fetch request
    $stmt = $pdo->prepare("SELECT r.id AS request_id, r.title, r.description, r.event_date, 
                                  r.status, r.approval_status, r.created_at, 
                                  rc.name AS category_name, s.society_name 
                           FROM requests r 
                           INNER JOIN request_categories rc ON r.category_id = rc.id 
                           INNER JOIN societies s ON r.society_id = s.id 
                           WHERE r.id = ?");
    $stmt->execute([$q['request_id']]);
    $request = $stmt->fetch();

    // Fetch items
    $stmt = $pdo->prepare("SELECT ri.item_name, ri.quantity, qi.unit_price 
                          FROM quotation_items qi 
                          JOIN request_items ri ON qi.request_item_id = ri.id 
                          WHERE qi.quotation_id = ?");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll();

    // Quotation serial
    $stmt = $pdo->prepare("WITH QuotationsList AS (
                               SELECT ROW_NUMBER() OVER (ORDER BY q.submitted_at) AS serial_number, 
                                      r.id AS request_id, q.id AS quotation_id 
                               FROM requests r 
                               LEFT JOIN quotations q ON r.id = q.request_id 
                               WHERE r.id = ? 
                           ) 
                           SELECT * FROM QuotationsList WHERE quotation_id = ?");
    $stmt->execute([$q['request_id'], $id]);
    $quote_serial = $stmt->fetch();

    // === Build PDF ===
    $pdf = new PDF();
    $pdf->AliasNbPages(); // allows {nb} in footer
    $pdf->AddPage();

    // Request header
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, "Request Title: " . $request['title'], 0, 1);

    $pdf->SetFont('Arial', 'B', 12); $pdf->Cell(40, 6, "Event:", 0, 0);
    $pdf->SetFont('Arial', '', 12); $pdf->MultiCell(0, 6, $request['description']);

    $pdf->SetFont('Arial', 'B', 12); $pdf->Cell(40, 6, "Event Date:", 0, 0);
    $pdf->SetFont('Arial', '', 12); $pdf->Cell(0, 6, $request['event_date'], 0, 1);

    $pdf->SetFont('Arial', 'B', 12); $pdf->Cell(40, 6, "Society:", 0, 0);
    $pdf->SetFont('Arial', '', 12); $pdf->Cell(0, 6, $request['society_name'], 0, 1);

    $pdf->SetFont('Arial', 'B', 12); $pdf->Cell(40, 6, "Category:", 0, 0);
    $pdf->SetFont('Arial', '', 12); $pdf->Cell(0, 6, $request['category_name'], 0, 1);

    $pdf->Ln(2);
    $pdf->SetLineWidth(0.1);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Line($pdf->GetX(), $pdf->GetY(), 200, $pdf->GetY());

    // Quotation info
    $pdf->Ln(2);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 8, "Quotation # " . ($i + 1), 0, 1);

    $pdf->SetFont('Arial', 'B', 12); $pdf->Cell(40, 6, "Vendor:", 0, 0);
    $pdf->SetFont('Arial', '', 12); $pdf->Cell(0, 6, $q['name'], 0, 1);

    $pdf->SetFont('Arial', 'B', 12); $pdf->Cell(40, 6, "Company:", 0, 0);
    $pdf->SetFont('Arial', '', 12); $pdf->Cell(0, 6, $q['company'], 0, 1);

    $pdf->SetFont('Arial', 'B', 12); $pdf->Cell(40, 6, "NTN:", 0, 0);
    $pdf->SetFont('Arial', '', 12); $pdf->Cell(0, 6, $q['ntn'], 0, 1);

    $pdf->SetFont('Arial', 'B', 12); $pdf->Cell(40, 6, "Email:", 0, 0);
    $pdf->SetFont('Arial', '', 12); $pdf->Cell(0, 6, $q['email'], 0, 1);

    $pdf->SetFont('Arial', 'B', 12); $pdf->Cell(40, 6, "Phone:", 0, 0);
    $pdf->SetFont('Arial', '', 12); $pdf->Cell(0, 6, $q['phone'], 0, 1);

    $pdf->Ln(3);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 6, "Message/Notes:", 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 8, $q['message']);
    $pdf->Ln(4);

    // Items table
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetFillColor(220, 220, 220);
    $pdf->Cell(70, 8, 'Item', 1, 0, 'L', true);
    $pdf->Cell(30, 8, 'Qty', 1, 0, 'C', true);
    $pdf->Cell(40, 8, 'Unit Price', 1, 0, 'R', true);
    $pdf->Cell(40, 8, 'Total', 1, 1, 'R', true);

    $pdf->SetFont('Arial', '', 12);
    $total = 0;
    foreach ($items as $item) {
        $line = $item['unit_price'] * $item['quantity'];
        $total += $line;

        $pdf->Cell(70, 8, $item['item_name'], 1);
        $pdf->Cell(30, 8, $item['quantity'], 1, 0, 'C');
        $pdf->Cell(40, 8, number_format($item['unit_price'], 2), 1, 0, 'R');
        $pdf->Cell(40, 8, number_format($line, 2), 1, 1, 'R');
    }

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(140, 8, 'Total:', 1, 0, 'R', true);
    $pdf->Cell(40, 8, number_format($total, 2), 1, 1, 'R', true);

    $tempPdfPath = $tempDir . "quotation_$id.pdf";
    $pdf->Output('F', $tempPdfPath);
    $zip->addFile($tempPdfPath, "quotation_$id/quotation_$id.pdf");

    // Attachments
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

// Clean up
foreach ($quotation_ids as $id) {
    $tempFile = $tempDir . "quotation_$id.pdf";
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }
}
unlink($zip_path);
