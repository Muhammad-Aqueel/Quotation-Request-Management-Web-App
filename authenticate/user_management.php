<?php
include 'includes/header.php';
require_login(); 
require_admin();// Only admins can access this page

$users = $pdo->query("SELECT * FROM users WHERE role != 'admin' ORDER BY role, username")->fetchAll();
?>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fas fa-users-cog"></i> User Management</h2>
    <div>
      <a href="add_user.php" class="btn btn-sm btn-outline-success"><i class="fas fa-user-plus"></i> Add User</a>
      <a href="vendors.php" class="btn btn-sm btn-outline-primary"><i class="fas fa-users"></i> Vendors</a>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead class="table-light">
        <tr>
          <th><i class="fas fa-user"></i> Name</th>
          <th><i class="fas fa-envelope"></i> Email</th>
          <th><i class="fas fa-id-card"></i> Role</th>
          <th class="text-center"><i class="fas fa-cogs"></i> Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= ucfirst($u['role']) ?></td>
            <td style="max-width: 20px;" class="text-center">
              <a href="edit_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-warning" title="Edit User">
                <i class="fas fa-edit"></i>
              </a>
              <?php 
                $stmt = $pdo->prepare("SELECT * FROM `users` INNER JOIN requests ON users.id = requests.user_id WHERE users.id = ?");
                $stmt->execute([$u['id']]);
                $result = $stmt->fetchAll();
              if(count($result) > 0):
              ?>
                <button class="btn btn-sm btn-secondary" title="User can't be deleted due to having a request.">
                  <i class="fas fa-ban"></i>
                </button>
              <?php else:?>
                <a href="delete_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" title="Delete User" onclick="return confirm('Delete user?')">
                  <i class="fas fa-trash-alt"></i>
                </a>
              <?php endif;?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if (isset($_SESSION['user_delete_message'])): ?>
  <div class="container-fluid">
    <div id="msg-modal" class="modal d-block" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fa-solid fa-comment-dots"></i> Feedback</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="close_modal()"></button>
          </div>
          <div class="modal-body">
            <!-- <p>Modal body text goes here.</p> -->
            <?= $_SESSION['user_delete_message'] ?>
          </div>
          <!-- <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary">Save changes</button>
          </div> -->
        </div>
      </div>
    </div>
    <div id="msg-backdrop" class="modal-backdrop fade show"></div>
  </div>
<?php unset($_SESSION['user_delete_message']); endif; ?>

<?php include 'includes/footer.php'; ?>
