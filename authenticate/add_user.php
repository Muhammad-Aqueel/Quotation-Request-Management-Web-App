<?php
require 'includes/db.php';
require 'includes/auth.php';
require_login();
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['username']);
  $email = trim($_POST['email']);
  $role = $_POST['role'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  try {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $password, $role]);
    header('Location: user_management.php');
    exit;
  } catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) {
      // Optional: extract more if needed
      $error = $e->errorInfo[2];
      $error = str_replace("for key", "for", $error);
    } else {
      $other_error = $e->getMessage();
    }
  }
}
?>

<?php include 'includes/header.php'; if (isset($other_error)): ?>
  <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($other_error) ?></div>
<?php endif; ?>
<h2 class="mb-4 mt-5 text-center"><i class="fas fa-user-plus"></i> Add New User</h2>
<form method="post" class="border p-3 bg-light rounded shadow-sm m-auto col-md-8">
  <div class="mb-2">
    <label class="form-label"><i class="fas fa-user"></i> Username</label>
    <?php if (isset($error)): ?>
      <small class="alert text-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></small>
    <?php endif; ?>
    <input type="text" name="username" class="form-control" placeholder="User Name" required>
  </div>
  <div class="mb-2">
    <label class="form-label"><i class="fas fa-envelope"></i> Email</label>
    <input type="email" name="email" class="form-control" placeholder="Email" required>
  </div>
  <div class="mb-2">
    <label class="form-label"><i class="fas fa-user-cog"></i> Role</label>
    <select name="role" class="form-select" required>
      <option value="student">Student</option>
      <option value="osas">OSAS</option>
    </select>
  </div>
  <div class="mb-2">
    <label class="form-label"><i class="fas fa-lock"></i> Password</label>
    <input type="password" name="password" class="form-control" placeholder="Password" required>
  </div>
  <button class="btn btn-primary theme_bg_color theme_border_color"><i class="fas fa-save"></i> Create</button>
  <a href="user_management.php" class="btn btn-secondary"><i class="fas fa-circle-xmark"></i> Cancel</a>
</form>

<?php include 'includes/footer.php'; ?>
