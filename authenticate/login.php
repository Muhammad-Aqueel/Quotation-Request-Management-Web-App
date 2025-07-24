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
<html lang="en" data-bs-theme="light">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://getbootstrap.com/docs/5.3/examples/sign-in/sign-in.css" rel="stylesheet">
    <link rel="shortcut icon" href="../assets/images/favicon.png" type="image/x-icon">
    <style>
      /* Existing styles */
      .theme_bg_color {
        background-color: #903035 !important;
      }
      .theme_bg_color:hover {
        background-color: #975155 !important;
        border: 1px solid #975155 !important;
      }
      .theme_border_color {
        border: 1px solid #903035 !important;
      }
      .form-signin input[type="text"] {
        margin-bottom: -1px;
        border-bottom-right-radius: 0;
        border-bottom-left-radius: 0;
      }
      /* Make the main container responsive and centered */
      main.form-signin {
        max-width: 400px;
        width: 90%;
        padding: 2rem;
        /* border-radius: 8px; */
        /* box-shadow: 0 4px 12px rgba(0,0,0,0.1); */
      }
    </style>
  </head>
  <body class="d-flex align-items-center py-4 bg-body-tertiary">
    <main class="form-signin w-100 m-auto">
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
        <p class="mt-3 mb-3 text-muted text-center">
          © 2025
        </p>
      </form>
    </main>
  </body>
</html>
