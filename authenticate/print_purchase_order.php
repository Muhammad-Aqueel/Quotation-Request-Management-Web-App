<?php
    require_once 'includes/db.php';
    require_once 'includes/auth.php';
    require_login();
    require_admin();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // User clicked submit → Render Print Layout with submitted data
        $request_id    = $_POST['request_id'] ?? '';
        $event         = $_POST['event'] ?? '';
        $event_date    = $_POST['event_date'] ?? '';
        $society_name  = $_POST['society_name'] ?? '';
        $date_of_issue = $_POST['date_of_issue'] ?? '';
        $bill_to       = $_POST['bill_to'] ?? '';
        $place_of_delivery = $_POST['place_of_delivery'] ?? '';
        $additional_work   = $_POST['additional_work'] ?? '';
        $subtotal      = $_POST['subtotal'] ?? 0;
        $tax_rate      = $_POST['tax_rate'] ?? 0;
        $total_tax     = $_POST['total_tax'] ?? 0;
        $delivery      = $_POST['delivery_charges'] ?? 0;
        $grand_total   = $_POST['grand_total'] ?? 0;
        $dean_name     = $_POST['dean_name'] ?? '';
    
        $quotations = $_POST['quotations'] ?? [];

        // Mark this request as Purchase Order generated
        $updateStmt = $pdo->prepare("UPDATE requests SET purchase_order = '1', po_gt = ? WHERE id = ? AND approval_status = 'Approved'");
        $updateStmt->execute([$grand_total, $request_id]);
?>
    <!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Purchase Order / Work Order</title>
            <link rel="shortcut icon" href="../assets/images/favicon.png" type="image/x-icon">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link rel="stylesheet" href="../assets/css/main.css">
            <link rel="stylesheet" href="../assets/css/purchase_order.css">
        </head>
        <body>
            <!-- PRINT LAYOUT -->
            <div class="print-container">
                <div class="header">
                    <h1><i class="fas fa-file-invoice"></i> Purchase Order / Work Order</h1>
                    <div class="subtitle">Official Procurement Document</div>
                </div>
        
                <div class="info-grid">
                    <div class="info-box"><div class="info-label">Event Name</div><div class="info-value"><?= htmlspecialchars($event) ?></div></div>
                    <div class="info-box"><div class="info-label">Event Date</div><div class="info-value"><?= htmlspecialchars($event_date) ?></div></div>
                    <div class="info-box"><div class="info-label">Society</div><div class="info-value"><?= htmlspecialchars($society_name) ?></div></div>
                    <div class="info-box highlight"><div class="info-label">Date of Issue</div><div class="info-value"><?= htmlspecialchars($date_of_issue) ?></div></div>
                    <div class="info-box"><div class="info-label">Bill To</div><div class="info-value"><?= htmlspecialchars($bill_to) ?></div></div>
                    <div class="info-box"><div class="info-label">Place of Delivery</div><div class="info-value"><?= htmlspecialchars($place_of_delivery) ?></div></div>
                </div>
        
                <?php foreach ($quotations as $i => $q): ?>
                    <div class="section-title"><i class="fas fa-money-check-dollar"></i> Approved Quotation #<?= $i+1 ?></div>
                    <div class="vendor-info">
                        <div class="vendor-field"><strong>Vendor:</strong> <span><?= htmlspecialchars($q['vendor']) ?></span></div>
                        <div class="vendor-field"><strong>Email:</strong> <span><?= htmlspecialchars($q['email']) ?></span></div>
                        <div class="vendor-field"><strong>Phone:</strong> <span><?= htmlspecialchars($q['phone']) ?></span></div>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Item Description</th>
                                <th style="width:80px;">Quantity</th>
                                <th style="width:100px;" class="text-end">Unit Price (PKR)</th>
                                <th style="width:100px;" class="text-end">Total (PKR)</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($q['items'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= intval($item['qty']) ?></td>
                                <td class="text-end"><?= number_format($item['unit_price'],2) ?></td>
                                <td class="text-end"><?= number_format($item['total'],2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                            <tr class="quotation-total">
                                <td colspan="3" class="text-end"><strong>Quotation Total:</strong></td>
                                <td class="text-end"><strong><?= number_format($q['total'],2) ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                <?php endforeach; ?>
        
                <div class="totals-section">
                    <div class="additional-work">
                        <div class="additional-work-title">Additional Work / Special Instructions</div>
                        <div style="font-size: 10px; color: #555;"><?= nl2br(htmlspecialchars($additional_work)) ?></div>
                    </div>
                    <div class="totals-table">
                        <div class="totals-row"><span>Subtotal:</span><strong>PKR <?= number_format($subtotal,2) ?></strong></div>
                        <div class="totals-row"><span>Tax Rate (<?= $tax_rate ?>%):</span><strong>PKR <?= number_format($total_tax,2) ?></strong></div>
                        <div class="totals-row"><span>Delivery Charges:</span><strong>PKR <?= number_format($delivery,2) ?></strong></div>
                        <div class="totals-row grand"><span>GRAND TOTAL:</span><strong>PKR <?= number_format($grand_total,2) ?></strong></div>
                    </div>
                </div>
        
                <!-- Terms & Signatures same as your static HTML -->
                <div class="terms">
                    <div class="terms-title"><i class="fas fa-exclamation-circle"></i> Terms & Conditions</div>
                    <ul>
                        <li><strong>The amount specified in the Work Order is FINAL. No additional costs or revisions will be entertained beyond the agreed amount.</strong></li>
                        <li>Payment will be made via cross-cheque in the vendor's name only. No cheque is issued to individuals.</li>
                        <li>Items must be delivered on time. IBA reserves the right to withhold payment for irregularities.</li>
                        <li>IBA will deduct withholding tax: 11% for services, 5.5% for goods, 4% for transport (double for non-filers).</li>
                        <li>IBA will deduct withholding sales tax @ 20% on 15% SST for registered vendors.</li>
                        <li>IBA will deduct 15% SST on services and 18% GST on supplies for unregistered vendors.</li>
                        <li>Penalty of 2% per month on actual will be imposed on delayed delivery.</li>
                        <li>Competent Authority reserves the right to change/alter/remove items or adjust quantities.</li>
                        <li>IBA is not responsible for tax increases but should benefit from tax reductions.</li>
                        <li>No increase in value, unit price, or any charges shall be accepted.</li>
                    </ul>
                </div>
        
                <div class="signatures">
                    <div class="signature-box"><div class="signature-title">Dean - Student Affairs</div><div class="signature-name"><?= $dean_name ?></div></div>
                    <div class="signature-box"><div class="signature-title">Office of Student Societies</div></div>
                    <div class="signature-box"><div class="signature-title">Finance Department</div></div>
                </div>
        
                <div class="no-print" style="text-align:center;margin-top:20px;">
                    <a href="generate_purchase_order.php?request_id=<?=$request_id?>" class="btn btn-secondary btn-md">
                        <i class="fas fa-arrow-left"></i> Go Back
                    </a>
                    <button onclick="window.print()" class="btn btn-primary btn-md theme_bg_color theme_border_color">
                        <i class="fas fa-print"></i> Print Purchase Order
                    </button>
                </div>
            </div>
        </body>
    </html>

<?php
    } else {
        header("location: dashboard.php");
    }