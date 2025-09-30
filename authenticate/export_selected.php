<?php
    require_once 'includes/db.php';
    require_once 'includes/auth.php';
    require_login();

    require_once '../libs/fpdf/fpdf.php';

    class PDF extends FPDF {
        function Header() {
            // Add header if needed
        }

        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial','I',8);
            $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
        }
    }

    $quotation_ids = $_POST['quotation_ids'] ?? [];
    if (empty($quotation_ids)) {
        include 'includes/header.php';
        echo '<div class="container-fluid mt-4">
            <div class="col-6 m-auto bg-body p-4 shadow-sm rounded">
                <div class="mb-0 alert alert-warning text-center">
                    <h4 class="mb-0 text-center">
                        <i class="fas fa-exclamation-circle"></i> No quotations selected.
                    </h4>
                </div>
            </div>
        </div>';
        include 'includes/footer.php';
        exit;
    }

    $pdf = new PDF();
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);

    $total_ids = count($quotation_ids);

    foreach ($quotation_ids as $i =>  $id) {
        $id = intval($id);

        $stmt = $pdo->prepare("SELECT q.*, v.name, v.ntn, v.email, v.company, v.phone 
                                FROM quotations q 
                                JOIN vendors v ON q.vendor_id = v.id 
                                WHERE q.id = ?");
        $stmt->execute([$id]);
        $q = $stmt->fetch();

        $stmt = $pdo->prepare("SELECT r.id AS request_id, r.title, r.description, r.event_date, r.status, r.approval_status, r.created_at, rc.name AS category_name, s.society_name, u_creator.username AS created_by 
                                FROM requests r 
                                INNER JOIN request_categories rc ON r.category_id = rc.id 
                                INNER JOIN societies s ON r.society_id = s.id 
                                INNER JOIN users u_creator ON r.user_id = u_creator.id 
                                WHERE r.id = ?");
        $stmt->execute([$q['request_id']]);
        $request = $stmt->fetch();

        $stmt = $pdo->prepare("SELECT ri.item_name, ri.quantity, qi.unit_price 
                                FROM quotation_items qi 
                                JOIN request_items ri ON qi.request_item_id = ri.id 
                                WHERE qi.quotation_id = ?");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll();

        $stmt = $pdo->prepare("WITH QuotationsList AS (
                                SELECT ROW_NUMBER() OVER (ORDER BY q.submitted_at) AS serial_number, r.id AS request_id, r.title, q.id AS quotation_id 
                                FROM requests r 
                                LEFT JOIN quotations q ON r.id = q.request_id 
                                WHERE r.id = ? 
                            ) 
                            SELECT * FROM QuotationsList WHERE quotation_id = ?");
        $stmt->execute([$q['request_id'], $id]);
        $quote_serial = $stmt->fetch();

        // --- Page content ---
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, "Request Title: " . $request['title'], 0, 1);
        $pdf->SetFont('Arial', '', 12);

        $pdf->SetFont('Arial', 'B', 12); $pdf->Cell(40, 6, "Requested By:", 0, 0);
        $pdf->SetFont('Arial', '', 12); $pdf->MultiCell(0, 6, $request['created_by']);

        $pdf->SetFont('Arial', 'B', 12); $pdf->Cell(40, 6, "Event:", 0, 0);
        $pdf->SetFont('Arial', '', 12); $pdf->MultiCell(0, 6, $request['description']);

        $pdf->SetFont('Arial', 'B', 12); $pdf->Cell(40, 6, "Event Date:", 0, 0);
        $pdf->SetFont('Arial', '', 12); $pdf->Cell(0, 6, $request['event_date'], 0, 1);

        $pdf->SetFont('Arial', 'B', 12); $pdf->Cell(40, 6, "Society:", 0, 0);
        $pdf->SetFont('Arial', '', 12); $pdf->Cell(0, 6, $request['society_name'], 0, 1);

        $pdf->SetFont('Arial', 'B', 12); $pdf->Cell(40, 6, "Category:", 0, 0);
        $pdf->SetFont('Arial', '', 12); $pdf->Cell(0, 8, $request['category_name'], 0, 1);
        $pdf->Ln(2);

        // Draw horizontal line across the page
        $pdf->SetLineWidth(0.1); // default is 0.2 mm
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Line($pdf->GetX(), $pdf->GetY(), 200, $pdf->GetY());

        $pdf->SetFont('Arial', 'B', 14); $pdf->Cell(40, 8, "Quotation # " . $i + 1, 0, 1);
        // $pdf->SetFont('Arial', '', 12); $pdf->Cell(0, 8, $quote_serial['serial_number'], 0, 1);

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

        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 12); $pdf->Cell(50, 6, "Message/Notes:", 0, 1);
        $pdf->SetFont('Arial', '', 12); $pdf->MultiCell(0, 8, $q['message']);

        $pdf->Ln(4);

        // --- Table header ---
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->Cell(70, 8, 'Item', 1, 0, 'L', true);
        $pdf->Cell(30, 8, 'Qty', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'Unit Price', 1, 0, 'R', true);
        $pdf->Cell(40, 8, 'Total', 1, 1, 'R', true);

        // --- Table rows ---
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

        // --- Total row ---
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(140, 8, 'Total:', 1, 0, 'R', true);
        $pdf->Cell(40, 8, number_format($total, 2), 1, 1, 'R', true);

        // --- Page break for next quote ---
        if ($i < $total_ids - 1) {
            $pdf->AddPage();
        }
    }

    $pdf->Output('quotations.pdf', 'I');
