<?php
  include 'includes/header.php';

  $id = intval($_GET['id'] ?? 0);
  
  // Fetch full request details
  $stmt = $pdo->prepare("SELECT r.id AS request_id, r.title, r.description, r.event_date, r.status, r.approval_status, r.created_at, rc.name AS category_name, s.society_name FROM requests r INNER JOIN request_categories rc ON r.category_id = rc.id INNER JOIN societies s ON r.society_id = s.id WHERE r.id = ?");
  $stmt->execute([$id]);
  $request = $stmt->fetch();

  if (!$request) {
    echo "<div class='container'><p class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> Request not found.</p></div>";
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

  <!-- Header -->
  <div class="row mb-4 align-items-stretch row-gap-2">
    <!-- Left Section: Titles -->
    <div class="col-md-6 d-flex">
        <div class="border rounded p-3 w-100 h-100 transition">
          <h2 class="fw-bold mb-2">Request Title: <?= $request['title'] ?></h2>
          <h4 class="text-muted mb-0">Event: <?= $request['description'] ?></h4>
        </div>
    </div>

    <!-- Mid Section: Meta Info -->
    <div class="col-md-3 d-flex">
      <div class="border rounded p-3 w-100 h-100">
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

    <!-- Right Section: Status Info -->
    <div class="col-md-3 d-flex">
      <div class="border rounded p-3 w-100 h-100">
        <p class="mb-2">
          <i class="fas fa-user-gear"></i>
          <strong>Approval Status:</strong> <span class="badge <?= $abadgeClass ?>"><?= htmlspecialchars($request['approval_status']) ?></span>
        </p>
        <p class="mb-2">
          <i class="fas fa-info-circle"></i>
          <strong>Status:</strong> <span class="badge <?= $sbadgeClass ?>"><?= $stext ?></span>
        </p>
        <p class="mb-0">
          <i class="fas fa-money-check-dollar"></i>
          <strong>Quotations:</strong> <a href="quotations.php?request_id=<?= $request['request_id'] ?>" class="btn btn-sm btn-info" target="_blank"><i class="fas fa-eye"></i></a>
        </p>
      </div>
    </div>
  </div>

  <!-- Items and Attachments -->
  <div class="row mb-3">
    <!-- Approval Status -->
    <div class="col-md-8 mb-3">
      <div class="card h-100 shadow-sm">
        <div class="card-header bg-light">
          <h5 class="mb-0">
            <i class="fas fa-boxes"></i> Items
          </h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle table-striped">
              <thead class="table-light">
                <tr>
                  <th><i class="fas fa-box"></i> Item/description with specifications</th>
                  <th><i class="fas fa-sort-numeric-up-alt"></i> Quantity/unit of measure</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                  <td><?= htmlspecialchars($item['item_name']) ?></td>
                  <td><?= $item['quantity'] ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
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

  <!-- Back Button -->
  <div class="text-start">
    <a href="requests.php" class="btn btn-secondary btn-sm">
      <i class="fas fa-arrow-left"></i> Back to Requests
    </a>
  </div>

<?php include 'includes/footer.php'; ?>
