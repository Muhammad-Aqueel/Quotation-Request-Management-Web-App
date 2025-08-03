<?php
  include 'includes/header.php';

  $id = intval($_GET['id'] ?? 0);
  
  // Auto-mark as read
  $pdo->prepare("UPDATE quotations SET is_read = 1 WHERE id = ? AND is_read = 0")->execute([$id]);

  // Fetch full quotation + vendor details
  $stmt = $pdo->prepare("SELECT q.*, v.name, v.email, v.company, v.ntn, v.phone 
                        FROM quotations q 
                        JOIN vendors v ON q.vendor_id = v.id 
                        WHERE q.id = ?");
  $stmt->execute([$id]);
  $quotation = $stmt->fetch();

  if (!$quotation) {
    echo "<div class='container'><p class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> Quotation not found.</p></div>";
    include 'includes/footer.php';
    exit;
  }
  
  $stmt = $pdo->prepare("SELECT r.id AS request_id, r.title, r.description, r.event_date, r.status, r.approval_status, r.created_at, rc.name AS category_name, s.society_name FROM requests r INNER JOIN request_categories rc ON r.category_id = rc.id INNER JOIN societies s ON r.society_id = s.id WHERE r.id = ?");
  $stmt->execute([$quotation ['request_id']]);
  $request = $stmt->fetch();

  // Mark as read
  $pdo->prepare("UPDATE quotations SET is_read = 1 WHERE id = ?")->execute([$id]);

  $stmt = $pdo->prepare("SELECT q.*, v.name, v.email, v.company, v.ntn, v.phone 
                        FROM quotations q 
                        JOIN vendors v ON q.vendor_id = v.id 
                        WHERE q.id = ?");
  $stmt->execute([$id]);
  $quotation = $stmt->fetch();

  // For quotation serial number_format
  $stmt = $pdo->prepare("WITH QuotationsList AS ( SELECT ROW_NUMBER() OVER (ORDER BY q.submitted_at) AS serial_number, r.id AS request_id, r.title, q.id AS quotation_id FROM requests r LEFT JOIN quotations q ON r.id = q.request_id WHERE r.id = ? ) SELECT * FROM QuotationsList WHERE quotation_id = ?;");
  $stmt->execute([$quotation ['request_id'], $id]);
  $quote_serial = $stmt->fetch();

  $status = $quotation['status'];
  $badgeClass = 'bg-secondary'; // default
  switch ($status) {
    case 'Approved':
      $badgeClass = 'bg-success';
      break;
    case 'Rejected':
      $badgeClass = 'text-dark bg-warning';
      break;
    case 'Deleted':
      $badgeClass = 'bg-danger';
      break;
    case 'Pending':
    default:
      $badgeClass = 'bg-secondary';
      break;
  }

  $stmt = $pdo->prepare("SELECT ri.item_name, ri.quantity, qi.unit_price 
                        FROM quotation_items qi 
                        JOIN request_items ri ON qi.request_item_id = ri.id 
                        WHERE qi.quotation_id = ?");
  $stmt->execute([$id]);
  $items = $stmt->fetchAll();

  $stmt = $pdo->prepare("SELECT * FROM quotation_attachments WHERE quotation_id = ?");
  $stmt->execute([$id]);
  $attachments = $stmt->fetchAll();
?>

  <!-- Header -->
  <div class="row mb-4 align-items-stretch row-gap-2">
    <!-- Left Section: Titles -->
    <div class="col-md-6 d-flex">
      <a href="view_request.php?id=<?= $request['request_id'] ?>" class="text-body text-decoration-none w-100">
        <div class="border rounded p-3 w-100 h-100 transition reqlink">
          <h2 class="fw-bold mb-2">Request Title: <?= $request['title'] ?></h2>
          <h4 class="text-muted mb-0">Event: <?= $request['description'] ?></h4>
        </div>
      </a>
    </div>

    <!-- Right Section: Meta Info -->
    <div class="col-md-6 d-flex">
      <div class="border rounded p-3 w-100 h-100 ms-md-1">
        <p class="mb-2">
          <i class="fas fa-calendar-alt"></i>
          <strong>Event Date:</strong> <?= htmlspecialchars($request['event_date']) ?>
        </p>
        <p class="mb-2">
          <i class="fas fa-university"></i>
          <strong>Society:</strong> <?= htmlspecialchars($request['society_name']) ?>
        </p>
        <p class="mb-0">
          <i class="fas fa-layer-group"></i>
          <strong>Category:</strong> <?= htmlspecialchars($request['category_name']) ?>
        </p>
      </div>
    </div>
  </div>

  <h4 class="mb-3">
    <i class="fas fa-file-invoice-dollar"></i> 
    Quotation # <?= $quote_serial['serial_number'] ?>
  </h4>
  <!-- Vendor Info and Status, Attachments -->
  <div class="row mb-3">
    <!-- Vendor Info -->
    <div class="col-md-4 mb-3">
      <div class="card h-100 shadow-sm">
        <div class="card-header bg-light">
          <h5 class="mb-0">
            <i class="fas fa-user"></i> Vendor Info
          </h5>
        </div>
        <div class="card-body">
          <p class="mb-1">
            <i class="fas fa-user-circle"></i> <strong>Name:</strong> <?= htmlspecialchars($quotation['name']) ?>
          </p>
          <p class="mb-1">
            <i class="fas fa-building"></i> <strong>Company:</strong> <?= htmlspecialchars($quotation['company']) ?>
          </p>
          <p class="mb-1">
            <i class="fas fa-id-card"></i> <strong>NTN:</strong> <?= htmlspecialchars($quotation['ntn']) ?>
          </p>
          <p class="mb-1">
            <i class="fas fa-envelope"></i> <strong>Email:</strong> <?= htmlspecialchars($quotation['email']) ?>
          </p>
          <p class="mb-0">
            <i class="fas fa-phone"></i> <strong>Phone:</strong> <?= htmlspecialchars($quotation['phone']) ?>
          </p>
        </div>
      </div>
    </div>
    <!-- Status -->
    <div class="col-md-4 mb-3">
      <div class="card h-100 shadow-sm">
        <div class="card-header bg-light">
          <h5 class="mb-0">
            <i class="fas fa-info-circle"></i> Status
          </h5>
        </div>
        <div class="card-body">
          <p>
            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($quotation['status']) ?></span>
          </p>
          <div class="d-flex flex-wrap gap-2">
            <?php if ($quotation['status'] !== 'Deleted'): ?>
              <a href="update_quotation_status.php?id=<?= $id ?>&status=Approved" class="btn btn-success btn-sm">
                <i class="fas fa-check-circle"></i> Approve
              </a>
              <a href="update_quotation_status.php?id=<?= $id ?>&status=Rejected" class="btn btn-warning btn-sm">
                <i class="fas fa-times-circle"></i> Reject
              </a>
              <a href="update_quotation_status.php?id=<?= $id ?>&status=Deleted" class="btn btn-danger btn-sm">
                <i class="fas fa-trash-alt"></i> Recycle
              </a>
            <?php else: ?>
              <a href="update_quotation_status.php?id=<?= $id ?>&status=Pending" class="btn btn-success btn-sm">
                <i class="fas fa-undo"></i> Restore
              </a>
              <a href="permanent_delete_quotation.php?id=<?= $id ?>&request_id=<?= $request['request_id'] ?>" 
                 class="btn btn-danger btn-sm" onclick="return confirm('Permanently delete?')">
                <i class="fas fa-fire"></i> Delete Permanently
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <!-- Attachments -->
    <div class="col-md-4 mb-3">
      <div class="card h-100 shadow-sm">
        <div class="card-header bg-light">
          <h5 class="mb-0">
            <i class="fas fa-paperclip"></i> Attachments
          </h5>
        </div>
        <div class="card-body">
        <?php if ($attachments): ?>
          <ul class="list-unstyled">
            <?php foreach ($attachments as $a): ?>
              <li>
                <i class="fas fa-file"></i> 
                <a href="<?= $a['filepath'] ?>" target="_blank"><?= htmlspecialchars($a['filename']) ?></a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-muted"><i class="fas fa-ban"></i> No attachments.</p>
        <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Quotation Items -->
  <div class="mb-3">
    <h5 class="mb-3"><i class="fas fa-boxes"></i> Quotation Items</h5>
    <div class="table-responsive">
      <table class="table table-bordered table-sm align-middle table-striped">
        <thead class="table-light">
          <tr>
            <th><i class="fas fa-box"></i> Item/description with specifications</th>
            <th><i class="fas fa-sort-numeric-up-alt"></i> Quantity/unit of measure</th>
            <th><i class="fas fa-dollar-sign"></i> Unit Price</th>
            <th><i class="fas fa-calculator"></i> Total</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $total = 0;
          foreach ($items as $item):
            $line = $item['unit_price'] * $item['quantity'];
            $total += $line;
          ?>
          <tr>
            <td><?= htmlspecialchars($item['item_name']) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td><?= number_format($item['unit_price'], 2) ?></td>
            <td><?= number_format($line, 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3" class="text-end"><i class="fas fa-coins"></i> Total&nbsp;</th>
            <th><?= number_format($total, 2) ?></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <!-- Vendor Message -->
  <div class="mb-3">
    <h5><i class="fas fa-comment-alt"></i> Vendor Message</h5>
    <div class="border rounded p-3 bg-light">
      <p class="mb-0"><?= nl2br(htmlspecialchars($quotation['message'])) ?></p>
    </div>
  </div>

  <!-- Back Button -->
  <div class="text-start">
    <a href="quotations.php?request_id=<?= $quotation['request_id'] ?>" class="btn btn-secondary btn-sm">
      <i class="fas fa-arrow-left"></i> Back to Quotations
    </a>
  </div>

<?php include 'includes/footer.php'; ?>
