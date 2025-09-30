<?php
  include 'includes/header.php';
  require_login();
  require_admin(); // Only admins can access

  // Handle deletion
  if (isset($_GET['delete'])) {
      $vendor_id = intval($_GET['delete']);
      // Check if vendor has no quotations before deleting
      $check = $pdo->prepare("SELECT COUNT(*) FROM quotations WHERE vendor_id = ?");
      $check->execute([$vendor_id]);
      if ($check->fetchColumn() == 0) {
          $del = $pdo->prepare("DELETE FROM vendors WHERE id = ?");
          if ($del->execute([$vendor_id])) {
              $_SESSION['vendor_message'] = '<div class="alert alert-success"><i class="fas fa-exclamation-circle"></i> Vendor deleted successfully.</div>';
          } else {
              $_SESSION['vendor_message'] = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Failed to delete vendor.</div>';
          }
      } else {
          $_SESSION['vendor_message'] = '<div class="alert alert-warning"><i class="fas fa-exclamation-circle"></i> Cannot delete vendor with quotations.</div>';
      }
      // header("Location: vendors.php");
      // exit;
  }

  // Fetch vendors and check status
  $sql = "
      SELECT v.*, 
            CASE WHEN q.id IS NOT NULL THEN 'Active' ELSE 'Inactive' END AS status
      FROM vendors v
      LEFT JOIN (
          SELECT DISTINCT vendor_id, id
          FROM quotations
      ) q ON v.id = q.vendor_id
      ORDER BY status DESC, v.name ASC
  ";
  $vendors = $pdo->query($sql)->fetchAll();
?>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fas fa-users"></i> Vendors List</h2>
    <!-- Back Button -->
    <div class="text-start">
      <a href="user_management.php" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back
      </a>
    </div>
  </div>

  <?php if (isset($_SESSION['vendor_message'])): ?>
      <?= $_SESSION['vendor_message']; unset($_SESSION['vendor_message']); ?>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th class="text-center"><i class="fas fa-hashtag"></i></th>
          <th><i class="fas fa-user"></i> Name</th>
          <th><i class="fas fa-envelope"></i> Email</th>
          <th><i class="fas fa-phone"></i> Phone</th>
          <th><i class="fas fa-building"></i> Company</th>
          <th><i class="fas fa-id-card"></i> NTN</th>
          <th class="text-center"><i class="fas fa-info-circle"></i> Status</th>
          <th class="text-center"><i class="fas fa-cogs"></i> Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($vendors as $i => $v): ?>
          <tr>
            <td class="text-center"><?= ++$i ?></td>
            <td><?= htmlspecialchars($v['name']) ?></td>
            <td><?= htmlspecialchars($v['email']) ?></td>
            <td><?= htmlspecialchars($v['phone']) ?></td>
            <td><?= htmlspecialchars($v['company']) ?></td>
            <td><?= htmlspecialchars($v['ntn']) ?></td>
            <td class="text-center">
              <?php if ($v['status'] === 'Active'): ?>
                <span class="badge bg-success">Active</span>
              <?php else: ?>
                <span class="badge bg-secondary">Inactive</span>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <?php if ($v['status'] === 'Inactive'): ?>
                <a href="?delete=<?= $v['id'] ?>" class="btn btn-sm btn-danger"  title="Delete Vendor"
                   onclick="return confirm('Are you sure you want to delete this vendor?');">
                  <i class="fas fa-trash-alt"></i>
                </a>
              <?php else: ?>
                <button class="btn btn-sm btn-secondary"  title="Active vendor can't be deleted.">
                  <i class="fas fa-ban"></i>
                </button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'includes/footer.php'; ?>