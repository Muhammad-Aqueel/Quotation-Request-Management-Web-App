<?php
  include 'includes/header.php';

  $id = intval($_GET['id'] ?? 0);
  
  // ✅ Auto-mark as read
  $pdo->prepare("UPDATE quotations SET is_read = 1 WHERE id = ? AND is_read = 0")->execute([$id]);

  // Fetch full quotation + vendor details
  $stmt = $pdo->prepare("SELECT q.*, v.name, v.email, v.company, v.ntn, v.phone 
                        FROM quotations q 
                        JOIN vendors v ON q.vendor_id = v.id 
                        WHERE q.id = ?");
  $stmt->execute([$id]);
  $quotation = $stmt->fetch();

  if (!$quotation) {
    echo "<p class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> Quotation not found.</p>";
    include 'includes/footer.php';
    exit;
  }
  
  $stmt = $pdo->prepare("SELECT * FROM `requests` WHERE id = ?");
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

<h2>Request Title: <?= $request['title'] ?></h2>
<p>Description: <?= $request['description'] ?></p>
<!-- <h4 class="mb-4"><i class="fas fa-file-invoice-dollar"></i> Quotation #<?= $quotation['id'] ?></h4> -->
<h4 class="mb-4"><i class="fas fa-file-invoice-dollar"></i> Quotation #<?= $quote_serial['serial_number'] ?></h4>

<div class="row mb-4">
  <div class="col-md-6">
    <h5><i class="fas fa-user"></i> Vendor Info</h5>
    <p>
      <i class="fas fa-user-circle"></i> <strong>Name:</strong> <?= htmlspecialchars($quotation['name']) ?><br>
      <i class="fas fa-building"></i> <strong>Company:</strong> <?= htmlspecialchars($quotation['company']) ?><br>
      <i class="fas fa-building"></i> <strong>NTN:</strong> <?= htmlspecialchars($quotation['ntn']) ?><br>
      <i class="fas fa-envelope"></i> <strong>Email:</strong> <?= htmlspecialchars($quotation['email']) ?><br>
      <i class="fas fa-phone"></i> <strong>Phone:</strong> <?= htmlspecialchars($quotation['phone']) ?>
    </p>
  </div>
  <div class="col-md-6">
    <h5><i class="fas fa-info-circle"></i> Status</h5>
    <p>
      <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($quotation['status']) ?></span>
    </p>
    <p>
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
        <a href="permanent_delete_quotation.php?id=<?= $id ?>&request_id=<?= $request['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Permanently delete?')">
          <i class="fas fa-fire"></i> Delete Permanently
        </a>
      <?php endif; ?>
    </p>
  </div>
</div>

<h5><i class="fas fa-boxes"></i> Quotation Items</h5>
<table class="table table-bordered table-sm">
  <thead>
    <tr>
      <th><i class="fas fa-box"></i> Item</th>
      <th><i class="fas fa-sort-numeric-up-alt"></i> Qty</th>
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
      <th colspan="3" class="text-end">Total:</th>
      <th><?= number_format($total, 2) ?></th>
    </tr>
  </tfoot>
</table>

<h5><i class="fas fa-comment-alt"></i> Vendor Message</h5>
<p><?= nl2br(htmlspecialchars($quotation['message'])) ?></p>

<h5><i class="fas fa-paperclip"></i> Attachments</h5>
<?php if ($attachments): ?>
  <ul>
    <?php foreach ($attachments as $a): ?>
      <li><i class="fas fa-file"></i> <a href="<?= $a['filepath'] ?>" target="_blank"><?= htmlspecialchars($a['filename']) ?></a></li>
    <?php endforeach; ?>
  </ul>
<?php else: ?>
  <p class="text-muted"><i class="fas fa-ban"></i> No attachments.</p>
<?php endif; ?>

<a href="quotations.php?request_id=<?= $quotation['request_id'] ?>" class="btn btn-secondary btn-sm mt-3">
  <i class="fas fa-arrow-left"></i> Back to Quotations
</a>

<?php include 'includes/footer.php'; ?>
