<?php
require 'includes/db.php';
require 'includes/auth.php';
require_login(); 
require_admin();// Only admins can access this page

$users = $pdo->query("SELECT * FROM users WHERE role != 'admin' ORDER BY role, username")->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h2><i class="fas fa-users-cog"></i> User Management</h2>
  <a href="add_user.php" class="btn btn-primary theme_bg_color theme_border_color"><i class="fas fa-user-plus"></i> Add User</a>
</div>

<div class="table-responsive">
  <table class="table table-bordered table-hover">
    <thead class="table-light">
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= ucfirst($u['role']) ?></td>
          <td>
            <a href="edit_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-warning">
              <i class="fas fa-edit"></i> Edit
            </a>
            <a href="delete_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete user?')">
              <i class="fas fa-trash-alt"></i> Delete
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include 'includes/footer.php'; ?>
