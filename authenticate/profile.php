<?php
include 'includes/header.php';

$id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET email = ?, password_hash = ? WHERE id = ?");
        $stmt->execute([$email, $hash, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$email, $id]);
    }

    echo "<div class='alert alert-success'><i class='fas fa-check-circle'></i> Profile updated.</div>";
}
?>

<div class="container">
  <h2 class="mb-4 mt-5 text-center"><i class="fas fa-user-cog"></i> Admin Profile</h2>
  <form method="post" class="border p-3 rounded bg-light m-auto shadow-sm col-md-6">
    <div class="mb-3">
      <label class="form-label"><i class="fas fa-user"></i> Username</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
    </div>
    <div class="mb-3">
      <label class="form-label"><i class="fas fa-envelope"></i> Email</label>
      <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label"><i class="fas fa-lock"></i> New Password <small class="text-muted">(leave empty to keep current)</small></label>
      <input type="password" class="form-control" name="password" placeholder="Enter new password">
    </div>
    <button type="submit" class="btn btn-primary theme_bg_color theme_border_color">
      <i class="fas fa-save"></i> Save Changes
    </button>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
