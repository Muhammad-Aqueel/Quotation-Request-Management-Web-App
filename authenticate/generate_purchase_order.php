<?php
    include 'includes/header.php';

    require_login();
    require_admin();

    $request_id = $_GET['request_id'] ?? null;
    if (!$request_id || !is_numeric($request_id)) {
        echo "<div class='container'><div class='alert alert-warning'><i class='fas fa-exclamation-circle'></i> Invalid request.</div><a href='requests.php' class='btn btn-secondary btn-sm mt-3'><i class='fas fa-arrow-left'></i> Back</a></div>";
        include 'includes/footer.php';
        exit;
    }

    $request_id = intval($request_id);

    // Fetch request details + username
    $stmt = $pdo->prepare("
        SELECT r.id AS request_id, r.title, r.description, r.event_date, r.status, 
            r.approval_status, r.created_at, rc.name AS category_name, 
            s.society_name, u_creator.username AS created_by,
            (SELECT COUNT(*) FROM quotations q WHERE q.request_id = r.id) AS quotations_count
        FROM requests r
        INNER JOIN request_categories rc ON r.category_id = rc.id
        INNER JOIN societies s ON r.society_id = s.id
        INNER JOIN users u_creator ON r.user_id = u_creator.id
        WHERE r.id = ? AND r.status = '0'
        AND EXISTS (SELECT 1 FROM users u WHERE u.id = ? AND u.role = 'admin')
    ");
    $stmt->execute([$request_id, $_SESSION['user_id']]);
    $request = $stmt->fetch();

    if (!$request) {
        echo "<div class='container'><div class='alert alert-warning'><i class='fas fa-exclamation-circle'></i> Request not found.</div><a href='requests.php' class='btn btn-secondary btn-sm mt-3'><i class='fas fa-arrow-left'></i> Back</a></div>";
        include 'includes/footer.php';
        exit;
    }

    // Fetch approved quotations
    $stmt = $pdo->prepare("
        SELECT q.*, v.name, v.email, v.phone, v.company, v.ntn
        FROM quotations q
        JOIN vendors v ON q.vendor_id = v.id
        WHERE q.request_id = ? AND q.status = 'Approved'
    ");
    $stmt->execute([$request_id]);
    $quotations = $stmt->fetchAll();

    echo '<form action="print_purchase_order.php" method="post" class="border p-4 rounded bg-light shadow-sm">';
    echo "<input type='hidden' name='request_id' value='$request_id'>";
    echo "<h2 class='mb-3'><i class='fas fa-file-invoice'></i> Purchase Order / Work Order</h2>";
    echo "<table class='table table-sm table-bordered mb-4'>";
    
    // Request Details
    echo "<tr>
            <th>Event</th>
            <td><input type='text' name='event' value='" . htmlspecialchars($request['description']) . "' class='form-control-plaintext' readonly tabindex='-1'></td>
          </tr>";
    echo "<tr>
            <th>Event Date</th>
            <td><input type='text' name='event_date' value='" . htmlspecialchars($request['event_date']) . "' class='form-control-plaintext' readonly tabindex='-1'></td>
          </tr>";
    echo "<tr>
            <th>Society</th>
            <td><input type='text' name='society_name' value='" . htmlspecialchars($request['society_name']) . "' class='form-control-plaintext' readonly tabindex='-1'></td>
          </tr>";
    echo "</table>";
    
    // No quotation case
    if (empty($quotations)) {
        echo "<p>No approved quotations found for this request.</p>";
        include 'includes/footer.php';
        exit;
    }
    
    // Additional fields
    echo '<div class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="date_of_issue" id="date-range-picker" class="form-control" placeholder="Date of Issue" style="border: 1px solid #903035;" readonly>
            </div>
            <div class="col-md-4">
                <input type="text" name="bill_to" class="form-control" placeholder="Bill To." style="border: 1px solid #903035;" required>
            </div>
            <div class="col-md-4">
                <input type="text" name="place_of_delivery" class="form-control" placeholder="Place of delivery" value="IBA Main Campus University Road Karachi" style="border: 1px solid #903035;" required>
            </div>
        </div>';
    
    // subtotal accumulator
    $grandSubtotal = 0;
    
    // Loop quotations
    foreach ($quotations as $i => $q) {
        echo "<hr>";
        echo "<h5 class='mb-2'>Approved Quotation # " . ($i + 1) . "</h5>";
        echo "<table class='table table-sm table-bordered mb-3'>";
        echo "<tr>
                <th>Vendor</th>
                <td><input type='text' name='quotations[$i][vendor]' value='" . htmlspecialchars($q['name']) . "' class='form-control-plaintext' readonly tabindex='-1'></td>
              </tr>";
        echo "<tr>
                <th>Email</th>
                <td><input type='text' name='quotations[$i][email]' value='" . htmlspecialchars($q['email']) . "' class='form-control-plaintext' readonly tabindex='-1'></td>
              </tr>";
        echo "<tr>
                <th>Phone</th>
                <td><input type='text' name='quotations[$i][phone]' value='" . htmlspecialchars($q['phone']) . "' class='form-control-plaintext' readonly tabindex='-1'></td>
              </tr>";
        echo "</table>";
    
        // Quotation items
        $stmt = $pdo->prepare("
            SELECT ri.item_name, ri.quantity, qi.unit_price 
            FROM quotation_items qi
            JOIN request_items ri ON qi.request_item_id = ri.id
            WHERE qi.quotation_id = ?
        ");
        $stmt->execute([$q['id']]);
        $items = $stmt->fetchAll();
    
        echo "<table class='table table-sm table-striped table-bordered'>";
        echo "<thead class='table-light'><tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr></thead><tbody>";
    
        $total = 0;
        foreach ($items as $j => $item) {
            $line = $item['unit_price'] * $item['quantity'];
            $total += $line;
            $grandSubtotal += $line;
    
            echo "<tr>
                    <td><input type='text' name='quotations[$i][items][$j][name]' value='" . htmlspecialchars($item['item_name']) . "' class='form-control-plaintext' readonly tabindex='-1'></td>
                    <td><input type='text' name='quotations[$i][items][$j][qty]' value='" . intval($item['quantity']) . "' class='form-control-plaintext' readonly tabindex='-1'></td>
                    <td class='text-end'><input type='text' name='quotations[$i][items][$j][unit_price]' value='" . number_format($item['unit_price'], 2) . "' class='form-control-plaintext text-end' readonly tabindex='-1'></td>
                    <td class='text-end'><input type='text' name='quotations[$i][items][$j][total]' value='" . number_format($line, 2) . "' class='form-control-plaintext text-end' readonly tabindex='-1'></td>
                </tr>";
        }
    
        echo "<tr class='fw-bold table-body'>
                <td colspan='3' class='text-end'>Quotation Total:</td>
                <td class='text-end'><input type='text' name='quotations[$i][total]' value='" . number_format($total, 2) . "' class='form-control-plaintext text-end' readonly tabindex='-1'></td>
            </tr>";
        echo "</tbody></table>";
    }

?>

    <div class="row g-3 mb-3">
        <!-- Inputs column (subtotal, tax, total) -->
        <div class="col-12 col-md-3 order-1 order-md-2">
            <div class="vstack gap-2">
                <input type="text" name="subtotal" class="text-end py-1 px-1 form-control-plaintext border" value="<?= number_format($grandSubtotal, 2) ?>" placeholder="All Quotations Total (PKR)" readonly tabindex='-1'>
                <input type="number" step="0.01" name="tax_rate" class="form-control" placeholder="GST / SST Rate (%)" style="border: 1px solid #903035;" required>
                <input type="text" step="0.01" name="total_tax" class="text-end py-1 px-1 form-control-plaintext border" placeholder="Total Tax (PKR)" readonly tabindex='-1'>
                <input type="number" step="0.01" name="delivery_charges" class="form-control" placeholder="Delivery Charges (PKR)" style="border: 1px solid #903035;" required>
                <input type="text" step="0.01" name="grand_total" class="text-end py-1 px-1 form-control-plaintext border" placeholder="Grand Total (PKR)" readonly tabindex='-1'>
            </div>
        </div>

        <!-- Textarea (additional work) -->
        <div class="col-12 col-md-9 order-2 order-md-1">
            <textarea name="additional_work" class="form-control" placeholder="Details of additional work required" style="border: 1px solid #903035;height: 220px;"></textarea>
        </div>
    </div>

    <div class="mb-3 p-2 bg-body-secondary rounded small">
        <strong>Terms & Conditions:</strong>
        <ul class="mb-0">
            <li><strong>The amount specified in the Work Order is FINAL. No additional costs or revisions will be entertained beyond the agreed amount.</strong></li>
            <li>The Payment will be made via cross-cheque in the name of Vendor only. No cheque is issued on the name of any person*.</li>
            <li>The required items must be delivered to the venue on the time and date stated. IBA reserves the right to withhold payment incase of irregularities in the work.</li>
            <li>IBA will deduct withholding tax as follows: (11% for services, 5.5% for goods, 4% for transport) – rates will be double for non-filers.</li>
            <li>IBA will deduct withholding sales tax @ 20% on 15 % SST on Services and Sales Tax Registered Vendors.</li>
            <li>IBA will deduct withholding sales tax 15 % SST on Services and 18% GST on Supplies for Unregistered Sales Tax Vendors (if applicable).</li>
            <li>IBA will deduct withholding sales tax and Income Tax for other items and services as per applicable rule of SRB & FBR.</li>
            <li>Penalty at the rate of 2% per month on actual will be imposed on delayed delivery.</li>
            <li>Competent Authority reserves the right to change / alter / remove any item or article or reduce / enhance quantity without assigning any reason.</li>
            <li>In case of any increase in taxes, the IBA would not be responsible. But if any tax is reduced, the IBA should get its benefit.</li>
            <li>No increase in the value of above mentioned items will be accepted on account of either unit price, total price, any and all other charges, duties, taxes, scope of supply and/or any other head of account shall not be allowed.</li>
        </ul>
    </div>

    <div class="d-flex justify-content-center justify-content-md-between flex-wrap mt-4">
      <div class="text-center mb-4">
          <p style="margin-bottom: 0;">___________________________<br><small>Dean - Student Affairs</small></p>
          <span><small><input type="text" name="dean_name" placeholder="Dean Name" class="form-control text-center border" style="border-color: #903035 !important; height: 35%;font-size: .875em;" required></small></span>
      </div>
      <div class="text-center">
          <p>___________________________<br><small>Office of Student Societies</small></p>
      </div>
      <div class="text-center">
          <p>___________________________<br><small>Finance</small></p>
      </div>
    </div>

    <button type="submit" id="printBtn" class="btn btn-primary theme_bg_color theme_border_color mt-3">
        <i class="fas fa-eye"></i> Preview Purchase Order
    </button>
</form>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const subtotalEl = document.querySelector("input[name='subtotal']");
        const taxRateEl = document.querySelector("input[name='tax_rate']");
        const totalTaxEl = document.querySelector("input[name='total_tax']");
        const deliveryEl = document.querySelector("input[name='delivery_charges']");
        const grandTotalEl = document.querySelector("input[name='grand_total']");

        function calculateTotals() {
            const subtotal = parseFloat(subtotalEl.value.replace(/,/g, "")) || 0;
            const taxRate = parseFloat(taxRateEl.value) || 0;
            const delivery = parseFloat(deliveryEl.value) || 0;

            const totalTax = (subtotal * taxRate) / 100;
            const grandTotal = subtotal + totalTax + delivery;

            totalTaxEl.value = totalTax.toFixed(2);
            grandTotalEl.value = grandTotal.toFixed(2);
        }

        taxRateEl.addEventListener("input", calculateTotals);
        deliveryEl.addEventListener("input", calculateTotals);
    });
</script>
<?php include 'includes/footer.php'; ?>