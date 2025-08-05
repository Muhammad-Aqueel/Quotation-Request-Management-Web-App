<?php
  include 'includes/header.php';

  $request_id = isset($_GET['request_id']) ? intval($_GET['request_id']) : 0;
  $in_bin = isset($_POST['filter']) && $_POST['filter'] === 'bin';
  $is_unread_filter = isset($_POST['filter']) && $_POST['filter'] === 'unread';

  $params = [];
  $sql = "SELECT q.*, v.name, v.company FROM quotations q
          JOIN vendors v ON q.vendor_id = v.id ";
  $filters = [];

  // Handle filters in order
  if ($request_id) {
      $filters[] = "q.request_id = ?";
      $params[] = $request_id;
  }

  if ($in_bin) {
      $filters[] = "q.status = 'Deleted'";
  } else {
      $filters[] = "q.status != 'Deleted'";

      // Only apply unread filter if not in bin
      if ($is_unread_filter) {
          $filters[] = "q.is_read = 0";
      }
  }

  if ($filters) {
      $sql .= "WHERE " . implode(' AND ', $filters);
  }

  $sort_order = ($_POST['sort'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
  $sql .= " ORDER BY q.submitted_at $sort_order";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $quotations = $stmt->fetchAll();
  
  if ($_SESSION['user_role'] === 'student'){
    $stmt = $pdo->prepare("SELECT * FROM requests WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $requests = $stmt->fetchAll();  
  } else {
    $requests = $pdo->query("SELECT * FROM requests WHERE status = '0'")->fetchAll();
  }

  $stmt = $pdo->prepare("SELECT r.id AS request_id, r.title, r.description, r.event_date, r.status, r.approval_status, r.created_at, rc.name AS category_name, s.society_name FROM requests r INNER JOIN request_categories rc ON r.category_id = rc.id INNER JOIN societies s ON r.society_id = s.id WHERE r.id = ? AND (r.user_id = ? OR (EXISTS ( SELECT 1 FROM users u WHERE u.id = ? AND u.role IN ('admin', 'osas')) AND r.status = '0' ))");
  $stmt->execute([$request_id, $_SESSION['user_id'], $_SESSION['user_id']]);
  $is_for_you_request = $stmt->fetch();

  if (isset($_GET['request_id']) && intval($_GET['request_id'])){
    $sql = "SELECT COUNT(q.id) AS all_count, COUNT(CASE WHEN q.status = 'Deleted' THEN 1 END) AS deleted_count, COUNT(CASE WHEN q.status != 'Deleted' AND q.is_read = 0 THEN 1 END) AS unread_count FROM quotations q JOIN vendors v ON q.vendor_id = v.id WHERE q.request_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $del_unread_all_count = $stmt->fetchAll();
  }
?>

<h2 class="mb-4"><i class="fas fa-money-check-dollar"></i> Quotations</h2>

<div class="d-flex p-2 justify-content-between align-items-baseline flex-wrap">
  <form method="get">
    <div>
      <select name="request_id" class="form-select" onchange="this.form.submit()">
        <option value="" selected disabled>Filter by Request</option>
        <?php foreach ($requests as $r): ?>
          <option value="<?= $r['id'] ?>" <?= $r['id'] == $request_id ? 'selected' : '' ?>>
            <?= htmlspecialchars($r['title']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>  
  <form action="mark_all_read.php" method="post">
    <a href="compare.php?request_id=<?= $request_id ?>" class="btn btn-sm btn-outline-primary ">
      <i class="fas fa-code-compare"></i> Compare All
    </a>
    <input type="hidden" name="request_id" value="<?= $request_id ?>">
    <button class="btn btn-sm btn-outline-success">
      <i class="fas fa-envelope-open"></i> Mark All as Read
    </button>
  </form>
</div>

<form method="post">
  <div class="d-flex p-2 justify-content-between align-items-center flex-wrap">
    <!-- Filter Navigation as fake-radio -->
    <nav class="nav">
      <?php
        $active_filter = $_POST['filter'] ?? '';
      ?>
      <input type="radio" class="btn-check" name="filter" value="" id="filter-all" autocomplete="off"
             onchange="this.form.submit()" <?= $active_filter === '' ? 'checked' : '' ?>>
      <label class="<?= $active_filter === '' ? 'btn btn-link nav-link fw-bold nav_active_link' : 'btn btn-link nav-link text-secondary' ?> position-relative" for="filter-all">
        <i class="fas fa-list"></i> All Quotations
        <?php if ($is_for_you_request && isset($_GET['request_id']) && intval($_GET['request_id'])):?><span class="position-absolute opacity-75 badge rounded-pill theme_bg_color"><?= $del_unread_all_count[0]['all_count'] ?></span><?php endif; ?>
      </label>

      <input type="radio" class="btn-check" name="filter" value="unread" id="filter-unread" autocomplete="off"
             onchange="this.form.submit()" <?= $active_filter === 'unread' ? 'checked' : '' ?>>
      <label class="<?= $active_filter === 'unread' ? 'btn btn-link nav-link fw-bold nav_active_link' : 'btn btn-link nav-link text-secondary' ?> position-relative" for="filter-unread">
        <i class="fas fa-envelope-open-text"></i> Unread
        <?php if ($is_for_you_request && isset($_GET['request_id']) && intval($_GET['request_id'])):?><span class="position-absolute opacity-75 badge rounded-pill theme_bg_color"><?= $del_unread_all_count[0]['unread_count'] ?></span><?php endif; ?>
      </label>

      <input type="radio" class="btn-check" name="filter" value="bin" id="filter-bin" autocomplete="off"
             onchange="this.form.submit()" <?= $active_filter === 'bin' ? 'checked' : '' ?>>
      <label class="<?= $active_filter === 'bin' ? 'btn btn-link nav-link fw-bold nav_active_link' : 'btn btn-link nav-link text-secondary' ?> position-relative" for="filter-bin">
        <i class="fas fa-trash-alt"></i> Recycle Bin
        <?php if ($is_for_you_request && isset($_GET['request_id']) && intval($_GET['request_id'])):?><span class="position-absolute opacity-75 badge rounded-pill theme_bg_color"><?= $del_unread_all_count[0]['deleted_count'] ?></span><?php endif; ?>
      </label>
    </nav>

    <!-- Sort Dropdown -->
    <div class="col-md-4">
      <select name="sort" class="form-select" onchange="this.form.submit()">
        <option value="">Sort by</option>
        <option value="desc" <?= ($_POST['sort'] ?? '') === 'desc' ? 'selected' : '' ?>>Newest First</option>
        <option value="asc" <?= ($_POST['sort'] ?? '') === 'asc' ? 'selected' : '' ?>>Oldest First</option>
      </select>
    </div>

  </div>
</form>

<?php if($is_for_you_request): ?>
  <?php if ($request_id && $quotations): ?>
    <form method="post" action="bulk_quotation_action.php">
      <input type="hidden" name="request_id" value="<?= $request_id ?>">
      <div class="my-2 d-flex gap-2 justify-content-end">
        <button type="button" class="btn border-0" disabled><i class="fa-solid fa-list-check"></i> Bulk</button>
        <?php if (!$in_bin): ?>
          <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">
            <i class="fas fa-check-circle"></i> Approve
          </button>
          <button type="submit" name="action" value="reject" class="btn btn-warning btn-sm">
            <i class="fas fa-times-circle"></i> Reject
          </button>
          <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm">
            <i class="fas fa-trash-alt"></i> Recycle
          </button>
        <?php endif; ?>
        <?php if ($in_bin): ?>
          <button type="submit" name="action" value="restore" class="btn btn-success btn-sm">
            <i class="fas fa-undo"></i> Restore
          </button>
          <button type="submit" name="action" value="permanent_delete" class="btn btn-danger btn-sm" onclick="return confirm('Delete permanently?')">
            <i class="fas fa-fire"></i> Delete
          </button>
        <?php endif; ?>
      </div>
      <div class="table-responsive">
        <table id="requestsTable" class="table table-bordered table-hover align-middle table-striped">
          <thead class="table-light">
            <tr>
              <?php //if (!$in_bin): ?>
                <th><input type="checkbox" onclick="toggleAll(this)" title="Select all"></th>
              <?php //endif; ?>
              <th><i class="fas fa-user"></i> Vendor</th>
              <th><i class="fas fa-building"></i> Company</th>
              <th><i class="fas fa-money-bill-wave"></i> Total</th>
              <th><i class="fas fa-info-circle"></i> Status</th>
              <th><i class="fas fa-cogs"></i> Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $lowest = min(array_column($quotations, 'total_amount'));
            foreach ($quotations as $q):
              $highlight = $q['total_amount'] == $lowest ? 'table-success' : '';
              $unreadClass = ($q['is_read'] == 0) ? 'table-warning' : '';
              $status = $q['status'];
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
            ?>
              <tr class="<?= "$highlight $unreadClass" ?>">
                <?php //if (!$in_bin): ?>
                  <td><input type="checkbox" name="quotation_ids[]" value="<?= $q['id'] ?>"></td>
                <?php //endif; ?>
                <td><?= htmlspecialchars($q['name']) ?></td>
                <td><?= htmlspecialchars($q['company']) ?></td>
                <td><?= number_format($q['total_amount'], 2) ?></td>
                <td class="text-center"><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span></td>
                <td class="text-center">
                  <a href="view_quotation.php?id=<?= $q['id'] ?>" class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i> View
                  </a>
                  <?php if ($q['status'] === 'Deleted'): ?>
                    <a href="update_quotation_status.php?id=<?= $q['id'] ?>&status=Pending&request_id=<?= $_GET['request_id'] ?>" class="btn btn-sm btn-success">
                      <i class="fas fa-undo"></i> Restore
                    </a>
                    <a href="permanent_delete_quotation.php?id=<?= $q['id'] ?>&request_id=<?= $_GET['request_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete permanently?')">
                      <i class="fas fa-fire"></i> Delete
                    </a>
                  <?php else: ?>
                    <a href="update_quotation_status.php?id=<?= $q['id'] ?>&status=Approved&request_id=<?= $_GET['request_id'] ?>" class="btn btn-sm btn-success">
                      <i class="fas fa-check-circle"></i> Approve
                    </a>
                    <a href="update_quotation_status.php?id=<?= $q['id'] ?>&status=Rejected&request_id=<?= $_GET['request_id'] ?>" class="btn btn-sm btn-warning">
                      <i class="fas fa-times-circle"></i> Reject
                    </a>
                    <a href="update_quotation_status.php?id=<?= $q['id'] ?>&status=Deleted&request_id=<?= $_GET['request_id'] ?>" class="btn btn-sm btn-danger">
                      <i class="fas fa-trash-alt"></i> Recycle
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php if (!$in_bin): ?>
        <div class="mt-3 d-flex gap-2">
          <button type="submit" class="btn btn-outline-primary theme_outline_btn_color" formaction="export_selected.php" formtarget="_blank">
            <i class="fas fa-file-pdf"></i> Export PDF
          </button>
          <button type="submit" class="btn btn-outline-primary theme_outline_btn_color" formaction="export_selected_zip.php" formtarget="_blank">
            <i class="fas fa-file-archive"></i> Export ZIP
          </button>
        </div>
      <?php endif; ?>
    </form>
  <?php elseif ($request_id): ?>
    <div class="mt-3 text-center">
      <p class="text-muted"><i class="fas fa-exclamation-circle"></i> 
        <?php if($in_bin) : ?>
          No quotation sended for this request to recycle bin.
        <?php elseif ($is_unread_filter): ?>
          No unread quotation for this request.
        <?php else: ?>
          No quotation submitted for this request.
        <?php endif; ?>
      </p>
    </div>
  <?php endif;?>
<?php endif;?>

<?php include 'includes/footer.php'; ?>