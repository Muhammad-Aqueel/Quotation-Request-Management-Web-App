<?php
  $config_path = '../config.php';

  $success = '';

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $host = $_POST['db_host'];
      $user = $_POST['db_user'];
      $pass = $_POST['db_pass'];
      $name = $_POST['db_name'];

      $admin_user = trim($_POST['admin_user']);
      $admin_pass = $_POST['admin_pass'];
      $admin_email = trim($_POST['admin_email']);

      try {
          $pdo = new PDO("mysql:host=$host", $user, $pass);
          $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
          $pdo->exec("USE `$name`");

          $sql = file_get_contents("schema.sql");
          $pdo->exec($sql);

          $hash = password_hash($admin_pass, PASSWORD_DEFAULT);
          $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, email, role) VALUES (?, ?, ?, ?)");
          $stmt->execute([$admin_user, $hash, $admin_email, 'admin']);

          $config = "<?php\n";
          $config .= "define('DB_HOST', '$host');\n";
          $config .= "define('DB_NAME', '$name');\n";
          $config .= "define('DB_USER', '$user');\n";
          $config .= "define('DB_PASS', '$pass');\n";
          file_put_contents($config_path, $config);

          $success = "Installation complete.";

      } catch (PDOException $e) {
          $error = $e->getMessage();
      }
  }
?>

<!DOCTYPE html>
<html>
<head>
  <title>Install Application</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container col-md-8 mt-5 bg-body p-4 shadow-sm rounded">
  <h3 class="mb-4 text-center"><i class="fas fa-cogs text-primary"></i> App Installer</h3>

  <?php if (isset($success)): ?>

  <?php endif; ?>

  <?php
    if (file_exists($config_path)) {
      die("<div class='alert alert-success text-center' style='padding:20px;font-family:sans-serif;'>
        <i class='fas fa-check-circle'></i> Application is installed.<br><br>
        <a href='../authenticate/login.php' class='btn btn-success'><i class='fas fa-sign-in-alt'></i> Login</a>
      </div>");
    }
  ?>

  <?php if (isset($error)): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post">
    <h5><i class="fas fa-database text-secondary"></i> Database Configuration</h5>

    <div class="input-group mb-2">
      <span class="input-group-text"><i class="fas fa-server"></i></span>
      <input name="db_host" class="form-control" placeholder="Database Host" value="localhost" required>
    </div>

    <div class="input-group mb-2">
      <span class="input-group-text"><i class="fas fa-user"></i></span>
      <input name="db_user" class="form-control" value="root" placeholder="Database Username" required>
    </div>

    <div class="input-group mb-2">
      <span class="input-group-text"><i class="fas fa-key"></i></span>
      <input name="db_pass" class="form-control" type="password" placeholder="Database Password">
    </div>

    <div class="input-group mb-2">
      <span class="input-group-text"><i class="fas fa-database"></i></span>
      <input name="db_name" class="form-control" value="quotedb" placeholder="Database Name" required>
    </div>

    <div class="mb-3 d-flex justify-content-between">
      <button type="button" class="btn btn-outline-secondary" onclick="checkConnection()">
        <i class="fas fa-plug"></i> Check Host Connection
      </button>
      <button type="button" class="btn btn-outline-secondary" onclick="checkDatabase()">
        <i class="fas fa-database"></i> Check Database Existence
      </button>
    </div>
    <div id="check-result" class="mt-2"></div>

    <h5><i class="fas fa-user-shield text-secondary"></i> Admin Account</h5>

    <div class="input-group mb-2">
      <span class="input-group-text"><i class="fas fa-user"></i></span>
      <input name="admin_user" class="form-control" placeholder="Admin Username" required>
    </div>

    <div class="input-group mb-2">
      <span class="input-group-text"><i class="fas fa-lock"></i></span>
      <input name="admin_pass" class="form-control" type="password" placeholder="Admin Password" required>
    </div>

    <div class="input-group mb-3">
      <span class="input-group-text"><i class="fas fa-envelope"></i></span>
      <input name="admin_email" class="form-control" type="email" placeholder="Admin Email" required>
    </div>

    <button class="btn btn-primary w-100"><i class="fas fa-download"></i> Install Now</button>
  </form>
</div>

<script>
  function checkConnection() {
    const data = new FormData();
    data.append('host', document.querySelector('[name="db_host"]').value);
    data.append('user', document.querySelector('[name="db_user"]').value);
    data.append('pass', document.querySelector('[name="db_pass"]').value);
    // data.append('name', document.querySelector('[name="db_name"]').value);

    fetch('test_connection.php', { method: 'POST', body: data })
      .then(res => res.text())
      .then(html => document.getElementById('check-result').innerHTML = html)
      .catch(() => document.getElementById('check-result').innerHTML = '<div class="text-danger"><i class="fas fa-times-circle"></i> Error checking connection.</div>');
  }
  function checkDatabase() {
    const data = new FormData();
    data.append('host', document.querySelector('[name="db_host"]').value);
    data.append('user', document.querySelector('[name="db_user"]').value);
    data.append('pass', document.querySelector('[name="db_pass"]').value);
    data.append('name', document.querySelector('[name="db_name"]').value);

    fetch('test_connection.php', { method: 'POST', body: data })
      .then(res => res.text())
      .then(html => document.getElementById('check-result').innerHTML = html)
      .catch(() => document.getElementById('check-result').innerHTML = '<div class="text-danger"><i class="fas fa-times-circle"></i> Error checking connection.</div>');
  }
</script>
</body>
</html>

