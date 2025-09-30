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
    echo "<div class='container'><p class='alert alert-warning'><i class='fas fa-exclamation-circle'></i> Quotation not found.</p><a href='quotations.php' class='btn btn-secondary btn-sm mt-3'><i class='fas fa-arrow-left'></i> Back</a></div>";
    include 'includes/footer.php';
    exit;
  }
  
  $stmt = $pdo->prepare("SELECT r.id AS request_id, r.title, r.description, r.event_date, r.status, r.approval_status, r.created_at, rc.name AS category_name, s.society_name, u_creator.username AS created_by FROM requests r INNER JOIN request_categories rc ON r.category_id = rc.id INNER JOIN societies s ON r.society_id = s.id INNER JOIN users u_creator ON r.user_id = u_creator.id WHERE r.id = ? AND ( r.user_id = ? OR ( EXISTS ( SELECT 1 FROM users u WHERE u.id = ? AND u.role IN ('admin', 'osas') ) AND r.status = '0' ) )");
  $stmt->execute([$quotation ['request_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
  $request = $stmt->fetch();

  if (!$request) {
      echo "<div class='container'><p class='alert alert-warning'><i class='fas fa-exclamation-circle'></i> Quotation not found.</p><a href='quotations.php?request_id=" . $quotation['request_id'] . "' class='btn btn-secondary btn-sm mt-3'><i class='fas fa-arrow-left'></i> Back</a></div>";
      include 'includes/footer.php';
      exit;
  }

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

<link rel="stylesheet" href="../assets/css/view_quotation.css">

<!-- Page Header with Back Button -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1 class="h3 mb-2 fw-bold text-body">Quotation Details</h1>
    <p class="text-muted mb-0">Complete quotation information and vendor details</p>
  </div>
  <a href="quotations.php?request_id=<?= $quotation['request_id'] ?>" class="back-btn">
    <i class="fas fa-arrow-left"></i> Back to Quotations
  </a>
</div>

<!-- Quotation Header -->
<div class="quotation-header">
  <div class="row align-items-center position-relative">
    <div class="col-md-8">
      <h2 class="quotation-number">Quotation #<?= $quote_serial['serial_number'] ?></h2>
      <p class="quotation-subtitle">Submitted on <?= date('M d, Y \a\t g:i A', strtotime($quotation['submitted_at'])) ?></p>
    </div>
    <div class="col-md-4 text-md-end">
      <span class="badge-modern <?= $badgeClass ?> fs-6">
        <i class="fas fa-circle"></i> <?= htmlspecialchars($quotation['status']) ?>
      </span>
    </div>
  </div>
</div>

<!-- Request Information -->
<div class="status-card p-4 mb-4">
  <div class="row align-items-center">
    <div class="col-md-8">
      <div class="info-icon primary-icon">
        <i class="fas fa-clipboard-list"></i>
      </div>
      <h5 class="fw-bold mb-2">Associated Request</h5>
      <a href="view_request.php?id=<?= $request['request_id'] ?>" class="text-decoration-none">
        <h4 class="text-body mb-2"><?= htmlspecialchars($request['title']) ?></h4>
      </a>
      <p class="text-muted mb-3"><?= htmlspecialchars($request['description']) ?></p>
      <div class="d-flex flex-wrap gap-3 text-sm">
        <span class="text-muted">
          <i class="fas fa-user me-2"></i><?= htmlspecialchars($request['created_by']) ?>
        </span>
        <span class="text-muted">
          <i class="fas fa-calendar-alt me-2"></i><?= htmlspecialchars($request['event_date']) ?>
        </span>
        <span class="text-muted">
          <i class="fas fa-university me-2"></i><?= htmlspecialchars($request['society_name']) ?>
        </span>
      </div>
    </div>
    <div class="col-md-4 text-md-end">
      <a href="view_request.php?id=<?= $request['request_id'] ?>" class="action-btn theme_bg_color text-white">
        <i class="fas fa-external-link-alt"></i> View Request Details
      </a>
    </div>
  </div>
</div>

<!-- Vendor Information and Actions -->
<div class="row g-4 mb-4">
  <!-- Vendor Information -->
  <div class="col-lg-6">
    <div class="vendor-card">
      <div class="info-icon primary-icon">
        <i class="fas fa-building"></i>
      </div>
      <h5 class="fw-bold mb-3">Vendor Information</h5>
      
      <div class="vendor-info-item">
        <div class="vendor-info-icon">
          <i class="fas fa-user-circle"></i>
        </div>
        <div>
          <div class="meta-label">Contact Person</div>
          <div class="meta-value"><?= htmlspecialchars($quotation['name']) ?></div>
        </div>
      </div>

      <div class="vendor-info-item">
        <div class="vendor-info-icon">
          <i class="fas fa-building"></i>
        </div>
        <div>
          <div class="meta-label">Company</div>
          <div class="meta-value"><?= htmlspecialchars($quotation['company']) ?></div>
        </div>
      </div>

      <div class="vendor-info-item">
        <div class="vendor-info-icon">
          <i class="fas fa-id-card"></i>
        </div>
        <div>
          <div class="meta-label">NTN</div>
          <div class="meta-value"><?= htmlspecialchars($quotation['ntn']) ?></div>
        </div>
      </div>

      <div class="vendor-info-item">
        <div class="vendor-info-icon">
          <i class="fas fa-envelope"></i>
        </div>
        <div>
          <div class="meta-label">Email</div>
          <div class="meta-value"><?= htmlspecialchars($quotation['email']) ?></div>
        </div>
      </div>

      <div class="vendor-info-item">
        <div class="vendor-info-icon">
          <i class="fas fa-phone"></i>
        </div>
        <div>
          <div class="meta-label">Phone</div>
          <div class="meta-value"><?= htmlspecialchars($quotation['phone']) ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Actions and Attachments -->
  <div class="col-lg-6">
    <!-- Actions -->
    <div class="status-card p-4 mb-4">
      <div class="info-icon primary-icon">
        <i class="fas fa-cogs"></i>
      </div>
      <h5 class="fw-bold mb-3">Available Actions</h5>
      
      <div class="d-flex flex-wrap gap-2">
        <?php if ($quotation['status'] !== 'Deleted'): ?>
          <a href="update_quotation_status.php?id=<?= $id ?>&status=Approved&request_id=<?= $request['request_id'] ?>" class="action-btn btn btn-success" title="Approve Quotation">
            <i class="fas fa-check-circle"></i> Approve
          </a>
          <a href="update_quotation_status.php?id=<?= $id ?>&status=Rejected&request_id=<?= $request['request_id'] ?>" class="action-btn btn btn-warning text-dark" title="Reject Quotation">
            <i class="fas fa-times-circle"></i> Reject
          </a>
          <a href="update_quotation_status.php?id=<?= $id ?>&status=Deleted&request_id=<?= $request['request_id'] ?>" class="action-btn btn btn-danger" title="Move to Recycle">
            <i class="fas fa-trash-alt"></i> Recycle
          </a>
        <?php else: ?>
          <a href="update_quotation_status.php?id=<?= $id ?>&status=Pending&request_id=<?= $request['request_id'] ?>" class="action-btn btn btn-success" title="Restore Quotation">
            <i class="fas fa-undo"></i> Restore
          </a>
          <a href="permanent_delete_quotation.php?id=<?= $id ?>&request_id=<?= $request['request_id'] ?>" 
             class="action-btn btn btn-danger" onclick="return confirm('Permanently delete this quotation?')" title="Delete Permanently">
            <i class="fas fa-fire"></i> Delete Forever
          </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Attachments -->
    <div class="status-card p-4">
      <div class="info-icon primary-icon">
        <i class="fas fa-paperclip"></i>
      </div>
      <h5 class="fw-bold mb-3">Attachments</h5>
      
      <?php if ($attachments): ?>
        <div class="attachment-list">
          <?php foreach ($attachments as $attachment): ?>
            <div class="attachment-item">
              <div class="attachment-icon">
                <i class="fas fa-file"></i>
              </div>
              <div class="flex-grow-1">
                <a href="<?= htmlspecialchars($attachment['filepath']) ?>" target="_blank" class="text-decoration-none fw-medium">
                  <?= htmlspecialchars($attachment['filename']) ?>
                </a>
              </div>
              <div class="flex-shrink-0">
                <a href="<?= htmlspecialchars($attachment['filepath']) ?>" target="_blank" class="text-decoration-none fw-medium">
                  <i class="fas fa-external-link-alt text-muted"></i>
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-center py-3">
          <i class="fas fa-paperclip text-muted" style="font-size: 2rem;"></i>
          <p class="text-muted mt-3 mb-0">No attachments available</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Quotation Items -->
<div class="section-header">
  <h5 class="mb-0 fw-bold">
    <div class="info-icon primary-icon" style="display: inline-grid !important;margin-bottom: 0px;">
      <i class="fas fa-boxes"></i>
    </div> Quotation Items & Pricing
  </h5>
</div>
<div class="section-body p-0 mb-4">
  <?php if ($items): ?>
    <?php 
    $total = 0;
    foreach ($items as $item) {
      $total += $item['unit_price'] * $item['quantity'];
    }
    ?>
    
    <div class="total-amount">
      <p class="amount">Rs. <?= number_format($total, 2) ?></p>
      <p class="label">Total Quotation Amount</p>
    </div>

    <div class="table-responsive">
      <table class="modern-table table table-hover">
        <thead>
          <tr>
            <th><i class="fas fa-box me-2"></i>Item Description & Specifications</th>
            <th class="text-center"><i class="fas fa-sort-numeric-up-alt me-2"></i>Quantity</th>
            <th class="text-end"><i class="fas fa-dollar-sign me-2"></i>Unit Price</th>
            <th class="text-end"><i class="fas fa-calculator me-2"></i>Total</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $index => $item): 
            $line_total = $item['unit_price'] * $item['quantity'];
          ?>
          <tr>
            <td>
              <div class="d-flex align-items-start gap-3">
                <div class="flex-shrink-0">
                  <span class="badge theme_bg_color rounded-circle" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                    <?= $index + 1 ?>
                  </span>
                </div>
                <div>
                  <div class="fw-medium"><?= htmlspecialchars($item['item_name']) ?></div>
                </div>
              </div>
            </td>
            <td class="text-center">
              <span class="badge bg-secondary fs-6 px-3 py-2"><?= htmlspecialchars($item['quantity']) ?></span>
            </td>
            <td class="text-end">
              <span class="fw-medium">Rs. <?= number_format($item['unit_price'], 2) ?></span>
            </td>
            <td class="text-end">
              <span class="fw-bold text-body">Rs. <?= number_format($line_total, 2) ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3" class="text-end gt-th">
              <i class="fas fa-coins me-2"></i>Grand Total
            </th>
            <th class="text-end to-th">Rs. <?= number_format($total, 2) ?></th>
          </tr>
        </tfoot>
      </table>
    </div>
  <?php else: ?>
    <div class="text-center py-5">
      <i class="fas fa-box-open text-muted" style="font-size: 3rem;"></i>
      <p class="text-muted mt-3 mb-0">No items found for this quotation</p>
    </div>
  <?php endif; ?>
</div>

<!-- Vendor Message -->
<div class="section-header">
  <h5 class="mb-0 fw-bold">
    <div class="info-icon primary-icon" style="display: inline-grid !important;margin-bottom: 0px;">
      <i class="fas fa-comment-alt"></i>
    </div> Vendor Message
  </h5>
</div>
<div class="section-body">
  <div class="vendor-message">
    <div class="d-flex align-items-start gap-3">
      <div class="flex-shrink-0">
        <div class="vendor-info-icon">
          <i class="fas fa-quote-left"></i>
        </div>
      </div>
      <div class="flex-grow-1">
        <p class="mb-0"><?= nl2br(htmlspecialchars($quotation['message'])) ?></p>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>