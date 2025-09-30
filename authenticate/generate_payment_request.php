<?php
    include 'includes/header.php';
    require_login();

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
            r.approval_status, r.created_at, rc.name AS category_name, r.po_gt,
            s.society_name, u_creator.username AS created_by,
            (SELECT COUNT(*) FROM quotations q WHERE q.request_id = r.id) AS quotations_count
        FROM requests r
        INNER JOIN request_categories rc ON r.category_id = rc.id
        INNER JOIN societies s ON r.society_id = s.id
        INNER JOIN users u_creator ON r.user_id = u_creator.id
        WHERE r.id = ? AND r.status = '0' AND approval_status = 'Approved' AND purchase_order = '1'
        AND EXISTS (SELECT 1 FROM users u WHERE u.id = ? AND u.role = 'student')
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

?>

<form action="print_payment_request.php" method="post" class="border p-3 rounded bg-light shadow-sm">
    <h5><i class="fas fa-file-invoice-dollar"></i> PAYMENT REQUEST (PR)</h5>
    <input type="hidden" name="request_id" value="<?= $request_id ?>">

    <!-- Top Section -->
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Club / Society Name</label>
            <input type="text" name="society_name" class="form-control-plaintext border" value="<?= htmlspecialchars($request['society_name']) ?>" style="padding-left: 0.7rem!important;" tabindex="-1" readonly>
        </div>
        <div class="col-md-4">
            <label class="form-label">Event Name</label>
            <input type="text" name="event_name" class="form-control-plaintext border" value="<?= htmlspecialchars($request['description']) ?>" style="padding-left: 0.7rem!important;" tabindex="-1" readonly>
        </div>
        <div class="col-md-4">
            <label class="form-label">Event Date</label>
            <input type="text" name="event_date" class="form-control-plaintext border" value="<?= htmlspecialchars($request['event_date']) ?>" style="padding-left: 0.7rem!important;" tabindex="-1" readonly>
        </div>
    </div>

    <?php 
        // No quotation case
        if (empty($quotations)) {
            echo "<p>No approved quotations found for this request.</p>";
            include 'includes/footer.php';
            exit;
        } 
    ?>
    
    <!-- Payee / Amounts -->
    <div class="row g-3 mb-3">
        <?php foreach ($quotations as $i => $q): ?>
            <div class="col-md-4">
                <label class="form-label">Name of Payee</label>
                <input type="text" name="payee_name[<?=$i?>]" class="form-control-plaintext border" value="<?= htmlspecialchars($q['name']) ?>" style="padding-left: 0.7rem!important;" tabindex="-1" readonly>
            </div>
        <?php endforeach; ?>
        <div class="col-md-4">
            <label class="form-label">Total Amount Payable (Gross)</label>
            <input type="text" name="gross_amount" class="form-control-plaintext border" value="<?= htmlspecialchars($request['po_gt']) ?>" style="padding-left: 0.7rem!important;" tabindex="-1" readonly>
        </div>
        <div class="col-md-4">
            <label class="form-label">Bill / Invoice #</label>
            <input type="text" name="invoice_no" class="form-control"  style="border-color: #903035 !important;" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Work / Purchase Order #</label>
            <input type="text" name="work_order_no" class="form-control"  style="border-color: #903035 !important;" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Date of Submission (PR)</label>
            <input type="text" name="submission_date" class="form-control" id="date-range-picker" style="border-color: #903035 !important;" readonly>
        </div>
        <div class="col-md-4">
            <label class="form-label">Nature of Payment</label>
            <input type="text" name="nature_of_payment" class="form-control"  style="border-color: #903035 !important;" required>
        </div>
    </div>

    <!-- Satisfactory Note -->
    <div class="mb-3">
        <label class="form-label">Satisfactory Note from the Dean – Student Affairs (Satisfied / Not Satisfied)</label>
        <textarea name="satisfactory_note" class="form-control" rows="2" style="border-color: #903035 !important;" required></textarea>
    </div>

    <!-- Signature -->
    <div class="d-flex justify-content-center justify-content-md-end my-4">
        <div class="text-center">
            <p style="margin-bottom: 0;">___________________________<br><small>Dean – Student Affairs</small></p>
            <span><small><input type="text" name="dean_name" placeholder="Dean Name" class="form-control text-center border" style="border-color: #903035 !important; height: 35%;font-size: .875em;" required></small></span>
        </div>
    </div>

    <!-- Enclosures -->
    <div class="mb-3 p-2 bg-body-secondary rounded small">
        <strong>Enclosures (Please mark ✔):</strong>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="enclosures[]" value="invoice" id="invoice">
            <label class="form-check-label" for="invoice">Bill/Invoice and/or GST Invoice (Original)</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="enclosures[]" value="work_order" id="work_order">
            <label class="form-check-label" for="work_order">Work/Purchase Order (copy) (Signed by the Patron)</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="enclosures[]" value="quotations" id="quotations">
            <label class="form-check-label" for="quotations">Three Quotations along with Comparative Summary</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="enclosures[]" value="justification" id="justification">
            <label class="form-check-label" for="justification">Justification in payment request where lowest supplier is not selected</label>
        </div>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary theme_bg_color theme_border_color mt-3">
        <i class="fas fa-eye"></i> Preview Payment Request
    </button>
</form>

<?php include 'includes/footer.php'; ?>
