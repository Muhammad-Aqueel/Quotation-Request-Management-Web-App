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
    <style>
      body, html {
        height: 100%;
        overflow: hidden;
      }
      canvas {
        display: block;
        position: absolute;
        z-index: 0;
      }
      button{
        position: relative;
        z-index: 1000;
      }
    </style>
  </head>
  <body class="d-flex align-items-center py-4 bg-body-tertiary">
    <canvas id="canvas"></canvas>
    <main class="form-signin w-100 m-auto mt-3">
      <div class="text-center mb-4">
        <img src="../assets/images/favicon.png" alt="theme logo"width="80">
      </div>
      <form method="post">
        <h1 class="h3 mb-3 fw-normal card-title mb-4 text-center"><i class="fas fa-user-tie"></i> User Login</h1>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <div class="form-floating">
          <input id="floatingInput" type="text" class="form-control" name="username" placeholder="Username" required style="border-radius: 4px 4px 0 0 !important;">
          <label for="floatingInput">
            <i class="fas fa-user"></i>&nbsp;Username
          </label>
        </div>
        <div class="form-floating">
          <input id="floatingPassword" type="password" class="form-control" name="password" placeholder="Password" required style="border-radius: 0 0 4px 4px !important;">
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
    <script>
      const canvas = document.getElementById("canvas");
      const ctx = canvas.getContext("2d");

      // Set canvas size to fill the screen
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;

      // Dot settings
      const dotCount = 100;
      const maxDistance = 100; // Maximum distance for lines between dots
      const dots = [];

      // Dot class
      class Dot {
        constructor(x, y) {
          this.x = x;
          this.y = y;
          this.size = 3;
          this.speedX = (Math.random() - 0.5) * 0.5;
          this.speedY = (Math.random() - 0.5) * 0.5;
          this.color = "#903035";
          this.alpha = 1;
        }

        update() {
          // Move the dot
          this.x += this.speedX;
          this.y += this.speedY;

          // Keep the dot within the canvas
          if (this.x < 0 || this.x > canvas.width) this.speedX *= -1;
          if (this.y < 0 || this.y > canvas.height) this.speedY *= -1;
        }

        draw() {
          ctx.beginPath();
          ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
          ctx.fillStyle = this.color;
          ctx.fill();
        }
      }

      // Create dots
      for (let i = 0; i < dotCount; i++) {
        const dot = new Dot(Math.random() * canvas.width, Math.random() * canvas.height);
        dots.push(dot);
      }

      // Mouse position tracking
      let mouseX = null;
      let mouseY = null;

      canvas.addEventListener("mousemove", (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
      });

      canvas.addEventListener("mouseleave", () => {
        mouseX = null;
        mouseY = null;
      });

      // Function to connect dots
      function connectDots() {
        for (let i = 0; i < dotCount; i++) {
          for (let j = i + 1; j < dotCount; j++) {
            const dot1 = dots[i];
            const dot2 = dots[j];
            const dist = Math.hypot(dot1.x - dot2.x, dot1.y - dot2.y);

            // Draw a line between dots if they are close enough
            if (dist < maxDistance) {
              const opacity = 1 - dist / maxDistance;
              ctx.beginPath();
              ctx.moveTo(dot1.x, dot1.y);
              ctx.lineTo(dot2.x, dot2.y);
              ctx.strokeStyle = `rgba(144, 48, 53, ${opacity})`;
              ctx.lineWidth = 0.5;
              ctx.stroke();
            }
          }
        }
      }

      // Animation loop
      function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Update and draw each dot
        dots.forEach(dot => {
          dot.update();
          dot.draw();
        });

        // Connect dots if mouse is over the canvas
        if (mouseX !== null && mouseY !== null) {
          dots.forEach(dot => {
            const dist = Math.hypot(dot.x - mouseX, dot.y - mouseY);
            if (dist < 100) {
              const angle = Math.atan2(mouseY - dot.y, mouseX - dot.x);
              dot.speedX += Math.cos(angle) * 0.1;
              dot.speedY += Math.sin(angle) * 0.1;
            }
          });
        }

        // Connect all dots to form a network
        connectDots();

        // Request the next frame
        requestAnimationFrame(animate);
      }

      // Start animation
      animate();
    </script>
  </body>
</html>
