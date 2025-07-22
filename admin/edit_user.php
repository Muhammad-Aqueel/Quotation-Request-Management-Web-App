<?php
require 'includes/db.php';
require 'includes/auth.php';
require_login();
require_admin();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: user_management.php");
    exit;
}

// Fetch user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    $no_user = "<div class='alert alert-warning'><i class='fas fa-exclamation-triangle'></i> User not found.</div><a href='user_management.php' class='btn btn-secondary btn-sm mt-3'><i class='fas fa-arrow-left'></i> Back</a>";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? $user['role'];
    $new_password = $_POST['password'] ?? '';

    // Basic validation
    if (!$email || !in_array($role, ['student', 'osas', 'admin'])) {
        $error = "<div class='alert alert-warning'><i class='fas fa-circle-xmark'></i> Invalid input.</div>";
    } else {
        // Update user info
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET email = ?, role = ?, password_hash = ? WHERE id = ?");
            $stmt->execute([$email, $role, $hashed_password, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET email = ?, role = ? WHERE id = ?");
            $stmt->execute([$email, $role, $id]);
        }
        header("Location: user_management.php");
        exit;
    }
}
?>
<?php
  include 'includes/header.php';
  if (isset($error)){
    echo $error;
  }elseif(isset($no_user)){
    echo $no_user; exit;
  } 
?>
<div class="container mt-4">
  <h3><i class="fas fa-user-edit"></i> Edit User</h3>
  <form method="post" class="border p-3 bg-light rounded shadow-sm">
    <div class="mb-3">
      <label class="form-label"><i class="fas fa-user"></i> Username</label>
      <input type="text" value="<?= htmlspecialchars($user['username']) ?>" class="form-control" disabled>
    </div>
    <div class="mb-3">
      <label class="form-label"><i class="fas fa-envelope"></i> Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label"><i class="fas fa-user-cog"></i> Role</label>
      <select name="role" class="form-select" required>
        <option value="student" <?= $user['role'] == 'student' ? 'selected' : '' ?>>Student</option>
        <option value="osas" <?= $user['role'] == 'osas' ? 'selected' : '' ?>>OSAS</option>
        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label"><i class="fas fa-lock"></i> New Password <small class="text-muted">(leave empty to keep current)</small></label>
      <input type="password" name="password" class="form-control" placeholder="Enter new password">
    </div>
    <div>
      <button class="btn btn-primary theme_bg_color theme_border_color"><i class="fas fa-save"></i> Update</button>
      <a href="user_management.php" class="btn btn-secondary"><i class="fas fa-circle-xmark"></i> Cancel</a>
    </div>
  </form>
</div>
<?php include 'includes/footer.php'; ?>