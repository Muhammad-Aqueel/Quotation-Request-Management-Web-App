<?php
  include 'includes/header.php';

  $id = intval($_GET['id'] ?? 0);

  // Fetch full request details
  $stmt = $pdo->prepare("SELECT r.id AS request_id, r.title, r.description, r.event_date, r.status, r.approval_status,r.purchase_order, r.created_at, rc.name AS category_name, s.society_name, u_creator.username AS created_by, ( SELECT COUNT(*) FROM quotations q WHERE q.request_id = r.id ) AS quotations_count FROM requests r INNER JOIN request_categories rc ON r.category_id = rc.id INNER JOIN societies s ON r.society_id = s.id INNER JOIN users u_creator ON r.user_id = u_creator.id WHERE r.id = ? AND ( r.user_id = ? OR ( EXISTS ( SELECT 1 FROM users u WHERE u.id = ? AND u.role IN ('admin', 'osas') ) AND r.status = '0' ) )");
  $stmt->execute([$id, $_SESSION['user_id'], $_SESSION['user_id']]);
  $request = $stmt->fetch();

  if (!$request) {
    echo "<div class='container'><p class='alert alert-warning'><i class='fas fa-exclamation-circle'></i> Request not found.</p><a href='requests.php' class='btn btn-secondary btn-sm mt-3'><i class='fas fa-arrow-left'></i> Back</a></div>";
    include 'includes/footer.php';
    exit;
  }

  $stmt = $pdo->prepare("SELECT * FROM request_items WHERE request_id = ?");
  $stmt->execute([$id]);
  $items = $stmt->fetchAll();

  $stmt = $pdo->prepare("SELECT * FROM request_attachments WHERE request_id = ?");
  $stmt->execute([$id]);
  $attachments = $stmt->fetchAll();

  $approval_status = $request['approval_status'];
  $abadgeClass = 'bg-secondary'; // default
  switch ($approval_status) {
    case 'Approved':
      $abadgeClass = 'bg-success';
      break;
    case 'Rejected':
      $abadgeClass = 'text-dark bg-warning';
      break;
    case 'Pending':
    default:
      $abadgeClass = 'bg-secondary';
      break;
  }
  $status = $request['status'];
  $sbadgeClass = 'bg-secondary'; // default
  switch ($status) {
    case '0':
      $sbadgeClass = 'bg-secondary';
      $stext = 'Closed';
      break;
    case '1':
    default:
      $sbadgeClass = 'bg-success';
      $stext = 'Open';
      break;
  }
?>

<link rel="stylesheet" href="../assets/css/view_request.css">

<!-- Page Header with Back Button -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1 class="h3 mb-2 fw-bold text-body">Request Details</h1>
    <p class="text-muted mb-0">Complete overview of request information and status</p>
  </div>
  <a href="requests.php" class="back-btn">
    <i class="fas fa-arrow-left"></i> Back to Requests
  </a>
</div>

<!-- Main Request Information -->
<div class="status-card p-4 mb-4">
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="info-icon primary-icon">
        <i class="fas fa-clipboard-list"></i>
      </div>
      <h2 class="h4 fw-bold mb-3 text-body"><?= htmlspecialchars($request['title']) ?></h2>
      <h5 class="text-muted mb-3"><?= htmlspecialchars($request['description']) ?></h5>
      <div class="d-flex align-items-center gap-3 text-sm">
        <span class="text-muted">
          <i class="fas fa-user me-2"></i>Created by <strong><?= htmlspecialchars($request['created_by']) ?></strong>
        </span>
        <span class="text-muted">
          <i class="fas fa-clock me-2"></i><?= date('M d, Y \a\t g:i A', strtotime($request['created_at'])) ?>
        </span>
      </div>
    </div>
    <div class="col-lg-4 d-flex align-items-center justify-content-lg-end">
      <div class="text-lg-end">
        <div class="mb-3">
          <span class="badge-modern <?= $sbadgeClass ?>">
            <i class="fas fa-circle"></i> <?= $stext ?>
          </span>
        </div>
        <div class="mb-3">
          <span class="badge-modern <?= $abadgeClass ?>">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($request['approval_status']) ?>
          </span>
        </div>
        <?php if ($request['purchase_order'] == '1'): ?>
          <div>
            <span class="badge-modern bg-danger">
              <i class="fas fa-credit-card"></i> Payment Request
            </span>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Meta Information Grid -->
<div class="row g-4 mb-4">
  <!-- Event & Society Info -->
  <div class="col-lg-4">
    <div class="status-card p-4 h-100">
      <div class="info-icon primary-icon">
        <i class="fas fa-info-circle"></i>
      </div>
      <h6 class="fw-bold mb-3">Event Information</h6>
      
      <div class="meta-item">
        <div class="meta-icon">
          <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="meta-content">
          <div class="meta-label">Event Date</div>
          <p class="meta-value"><?= htmlspecialchars($request['event_date']) ?></p>
        </div>
      </div>

      <div class="meta-item">
        <div class="meta-icon">
          <i class="fas fa-university"></i>
        </div>
        <div class="meta-content">
          <div class="meta-label">Society</div>
          <p class="meta-value"><?= htmlspecialchars($request['society_name']) ?></p>
        </div>
      </div>

      <div class="meta-item">
        <div class="meta-icon">
          <i class="fas fa-layer-group"></i>
        </div>
        <div class="meta-content">
          <div class="meta-label">Category</div>
          <p class="meta-value"><?= htmlspecialchars($request['category_name']) ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div class="col-lg-4">
    <div class="status-card p-4 h-100">
      <div class="info-icon primary-icon">
        <i class="fas fa-cogs"></i>
      </div>
      <h6 class="fw-bold mb-3">Available Actions</h6>
      
      <div class="d-flex flex-wrap gap-2">
        <?php if ($_SESSION['user_role'] === 'student'): ?>
          <?php if($request['status'] == '1'): ?>
            <a href="requests.php?id=<?= $request['request_id'] ?>&status=0" class="action-btn btn btn-secondary" title="Close Request">
              <i class="fas fa-eye-slash"></i> Close
            </a>
            <a href="edit_request.php?id=<?= $request['request_id'] ?>" class="action-btn btn btn-warning" title="Edit Request">
              <i class="fas fa-edit"></i> Edit
            </a>
          <?php else: ?>
            <a href="requests.php?id=<?= $request['request_id'] ?>&status=1" class="action-btn btn btn-success" title="Open Request">
              <i class="fas fa-eye"></i> Reopen
            </a>
          <?php endif; ?>
          <a href="delete_request.php?id=<?= $request['request_id'] ?>" class="action-btn btn btn-danger" onclick="return confirm('Delete this request?')" title="Delete Request">
            <i class="fas fa-trash-alt"></i> Delete
          </a>
        <?php else: ?>
          <a href="request_approval.php?id=<?= $request['request_id'] ?>&set=Pending" class="action-btn btn btn-secondary" title="Set Pending">
            <i class="fas fa-question-circle"></i> Pending
          </a>
          <a href="request_approval.php?id=<?= $request['request_id'] ?>&set=Approved" class="action-btn btn btn-success" title="Approve Request">
            <i class="fas fa-check-circle"></i> Approve
          </a>
          <a href="request_approval.php?id=<?= $request['request_id'] ?>&set=Rejected" class="action-btn btn btn-warning text-dark" title="Reject Request">
            <i class="fas fa-times-circle"></i> Reject
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Quotations -->
  <div class="col-lg-4">
    <div class="status-card p-4 h-100">
      <div class="info-icon primary-icon">
        <i class="fas fa-money-check-dollar"></i>
      </div>
      <h6 class="fw-bold mb-3">Quotations</h6>
      
      <div class="text-center">
        <div class="display-4 fw-bold text-body mb-2"><?= $request['quotations_count'] ?></div>
        <p class="text-muted mb-3">Total quotations received</p>
        <a href="quotations.php?request_id=<?= $request['request_id'] ?>" class="action-btn theme_bg_color text-white" target="_blank">
          <i class="fas fa-eye"></i> View All Quotations
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Items and Attachments -->
<div class="row g-4 mb-4">
  <!-- Items -->
  <div class="col-lg-8">
    <div class="section-header">
      <h5 class="mb-0 fw-bold">
        <div class="info-icon primary-icon" style="display: inline-grid !important;margin-bottom: 0px;">
          <i class="fas fa-boxes"></i>
        </div> Request Items
      </h5>
    </div>
    <div class="section-body p-0">
      <?php if ($items): ?>
        <div class="table-responsive" style="border-radius: 0 0 4px 4px;">
          <table class="modern-table table table-hover">
            <thead>
              <tr>
                <th><i class="fas fa-box me-2"></i>Item Description & Specifications</th>
                <th class="text-center"><i class="fas fa-sort-numeric-up-alt me-2"></i>Quantity & Unit</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $index => $item): ?>
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
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="text-center py-5">
          <i class="fas fa-box-open text-muted" style="font-size: 3rem;"></i>
          <p class="text-muted mt-3 mb-0">No items found for this request</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Attachments -->
  <div class="col-lg-4">
    <div class="section-header">
      <h5 class="mb-0 fw-bold">
        <div class="info-icon primary-icon" style="display: inline-grid !important;margin-bottom: 0px;">
          <i class="fas fa-paperclip"></i>
        </div> Attachments
      </h5>
    </div>
    <div class="section-body p-3">
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
        <div class="text-center py-4">
          <i class="fas fa-paperclip text-muted" style="font-size: 2.5rem;"></i>
          <p class="text-muted mt-3 mb-0">No attachments available</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>