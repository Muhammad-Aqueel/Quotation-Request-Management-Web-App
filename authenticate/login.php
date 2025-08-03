<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/csrf.php';

if (isset($_SESSION['user_id'] ) || isset($_SESSION['user_username'])) {
  header("Location: dashboard.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid login credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://getbootstrap.com/docs/5.3/examples/sign-in/sign-in.css" rel="stylesheet">
    <link rel="shortcut icon" href="../assets/images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/main.css">
  </head>
  <body class="d-flex align-items-center py-4 bg-body-tertiary">
    <main class="form-signin w-100 m-auto mt-3">
      <div class="text-center mb-4">
        <img src="../assets/images/favicon.png" alt="theme logo"width="80">
      </div>
      <form method="post">
        <h1 class="h3 mb-3 fw-normal card-title mb-4 text-center"><i class="fas fa-user-tie"></i> User Login</h1>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <div class="form-floating">
          <input id="floatingInput" type="text" class="form-control" name="username" placeholder="Username" required>
          <label for="floatingInput">
            <i class="fas fa-user"></i>&nbsp;Username
          </label>
        </div>
        <div class="form-floating">
          <input id="floatingPassword" type="password" class="form-control" name="password" placeholder="Password" required>
          <label for="floatingPassword">
            <i class="fas fa-lock"></i>&nbsp;Password
          </label>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 theme_border_color theme_bg_color">
          <i class="fas fa-arrow-right-to-bracket"></i> Login
        </button>
        <div class="mt-3 mb-3 text-muted text-center small">
            © 2025 &nbsp;|&nbsp; <i class="fas fa-calendar-day"></i> <?php date_default_timezone_set('Asia/Karachi'); echo date('d-M-Y'); ?>
        </div>
      </form>
    </main>
    <footer class="text-center text-muted small py-3 mt-auto w-100 fixed-bottom d-flex align-items-center">
        <div class="container">
            &nbsp;
        </div>
        <div class="me-3">
            <input type="checkbox" class="modeSwitch" id="modeSwitch">
            <label for="modeSwitch" class="modeSwitch-label">
                <i class="fas fa-moon"></i>
                <i class="fas fa-sun"></i>
                <span class="ball"></span>
            </label>
        </div>
    </footer>
    <script src="../assets/js/themeswitch.js"></script>
  </body>
</html>
