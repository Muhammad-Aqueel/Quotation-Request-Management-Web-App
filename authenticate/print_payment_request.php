<?php
    require_once 'includes/auth.php';
    require_login();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize inputs
        $request_id         = htmlspecialchars($_POST['request_id'] ?? '');
        $society_name       = htmlspecialchars($_POST['society_name'] ?? '');
        $event_name         = htmlspecialchars($_POST['event_name'] ?? '');
        $event_date         = htmlspecialchars($_POST['event_date'] ?? '');
        $payee_name         = $_POST['payee_name'] ?? [];
        $gross_amount       = htmlspecialchars($_POST['gross_amount'] ?? '');
        $invoice_no         = htmlspecialchars($_POST['invoice_no'] ?? '');
        $work_order_no      = htmlspecialchars($_POST['work_order_no'] ?? '');
        $submission_date    = htmlspecialchars($_POST['submission_date'] ?? '');
        $nature_of_payment  = htmlspecialchars($_POST['nature_of_payment'] ?? '');
        $satisfactory_note  = htmlspecialchars($_POST['satisfactory_note'] ?? '');
        $dean_name          = htmlspecialchars($_POST['dean_name'] ?? '');
        $enclosures         = $_POST['enclosures'] ?? []; // array
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Request (PR)</title>
        <link rel="shortcut icon" href="../assets/images/favicon.png" type="image/x-icon">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="../assets/css/main.css">
        <link rel="stylesheet" href="../assets/css/payment_request.css">
    </head>
    <body>
        <div class="print-container">
            <div class="header">
                <h1><i class="fas fa-file-invoice-dollar"></i> Payment Request (PR)</h1>
                <div class="subtitle">Official Payment Authorization Document</div>
            </div>
            
            <div class="info-grid">
                <div class="info-box">
                    <div class="info-label">Club / Society Name</div>
                    <div class="info-value"><?= $society_name ?></div>
                </div>
                <div class="info-box">
                    <div class="info-label">Event Name</div>
                    <div class="info-value"><?= $event_name ?></div>
                </div>
                <div class="info-box">
                    <div class="info-label">Event Date</div>
                    <div class="info-value"><?= $event_date ?></div>
                </div>
                <?php foreach ($payee_name as $i => $p): ?>
                    <div class="info-box highlight">
                        <div class="info-label">Name of Payee</div>
                        <div class="info-value"><?= htmlspecialchars($p) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="section-title">
                <i class="fas fa-money-check-alt"></i> Payment Details
            </div>
            
            <div class="info-grid">
                <div class="info-box highlight">
                    <div class="info-label">Total Amount Payable (Gross)</div>
                    <div class="info-value" style="font-size: 14px; font-weight: 700;">PKR <?= $gross_amount ?></div>
                </div>
                <div class="info-box">
                    <div class="info-label">Bill / Invoice #</div>
                    <div class="info-value"><?= $invoice_no ?></div>
                </div>
                <div class="info-box">
                    <div class="info-label">Work / Purchase Order #</div>
                    <div class="info-value"><?= $work_order_no ?></div>
                </div>
                <div class="info-box">
                    <div class="info-label">Date of Submission PR</div>
                    <div class="info-value"><?= $submission_date ?></div>
                </div>
                <div class="info-box full-width">
                    <div class="info-label">Nature of Payment</div>
                    <div class="info-value"><?= $nature_of_payment ?></div>
                </div>
            </div>
            
            <div class="section-title">
                <i class="fas fa-clipboard-check"></i> Approval & Verification
            </div>
            
            <div class="satisfactory-note">
                <div class="satisfactory-note-title">Satisfactory Note from the Dean – Student Affairs (Satisfied / Not Satisfied)</div>
                <div class="satisfactory-note-content"><?= nl2br($satisfactory_note) ?></div>
            </div>
            
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-title">Dean – Student Affairs</div>
                    <div class="signature-name"><?= $dean_name ?></div>
                </div>
            </div>
            
            <div class="section-title">
                <i class="fas fa-envelope"></i> Enclosures
            </div>
            
            <div class="enclosures">
                <div class="enclosures-title">Please mark (✓) all submitted documents:</div>
                
                <div class="enclosure-item">
                    <span class="checkbox <?= in_array('invoice', $enclosures) ? 'checked' : '' ?>"></span>
                    <span>Bill/Invoice and/or GST Invoice (Original)</span>
                </div>
                <div class="enclosure-item">
                    <span class="checkbox <?= in_array('work_order', $enclosures) ? 'checked' : '' ?>"></span>
                    <span>Work/Purchase Order (copy) (Signed by the Patron)</span>
                </div>
                <div class="enclosure-item">
                    <span class="checkbox <?= in_array('quotations', $enclosures) ? 'checked' : '' ?>"></span>
                    <span>Three Quotations along with Comparative Summary</span>
                </div>
                <div class="enclosure-item">
                    <span class="checkbox <?= in_array('justification', $enclosures) ? 'checked' : '' ?>"></span>
                    <span>Justification in payment request where lowest supplier is not selected</span>
                </div>
            </div>
            
            <div class="no-print" style="text-align: center; margin-top: 20px;">
                <a href="generate_payment_request.php?request_id=<?= $request_id ?>" class="btn btn-secondary btn-md">
                    <i class="fas fa-arrow-left"></i> Go Back
                </a>
                <button onclick="window.print()" class="btn btn-primary btn-md theme_bg_color theme_border_color">
                    <i class="fas fa-print"></i> Print Payment Request
                </button>
            </div>
        </div>
    </body>
    </html>
<?php
    } else {
        header("location: dashboard.php");
    }