<?php
  include 'includes/header.php';
  require_login();
  require_admin();// Only admins can access this page

  $message = "";

  // Handle new Society addition
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
      $name = trim($_POST['society_name']);
      if ($name) {
          try {
              $stmt = $pdo->prepare("INSERT INTO societies (society_name) VALUES (?)");
              $stmt->execute([$name]);
              $message = "<div class='alert alert-success'><i class='fas fa-exclamation-circle'></i> Society added.</div>";
          } catch (PDOException $e) {
              if ($e->errorInfo[1] == 1062) {
                // Optional: extract more if needed
                $error = $e->errorInfo[2];
                $message = "<div class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> " . str_replace("for key", "for", $error) . "</div>";
              } else {
                $message = "<div class='alert alert-warning'><i class='fas fa-exclamation-circle'></i> " . $e->getMessage() . "</div>";
              }
          }
      }
  }

  // Handle Society deletion
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
      try {
        $id = intval($_POST['delete_id']);
        $stmt = $pdo->prepare("DELETE FROM societies WHERE id = ?");
        $stmt->execute([$id]);
        $message = "<div class='alert alert-info'><i class='fas fa-exclamation-circle'></i>  Society deleted.</div>";
      } catch (PDOException $e) {
        $message = "<div class='alert alert-warning'><i class='fas fa-exclamation-circle'></i> " . $e->getMessage() . "</div>";
      }
  }

  // Fetch all Societies
  $societies = $pdo->query("SELECT * FROM societies ORDER BY society_name")->fetchAll();
?>

<div class="container">
  <h2 class="mb-3"><i class="fas fa-university"></i> Manage Societies</h2>

  <?= $message ?>

  <!-- Add Society Form -->
  <form method="post" class="mb-4 row g-2">
    <div class="col-md-6 d-flex align-items-center gap-2">
      <input type="text" name="society_name" class="form-control" placeholder="New society name" required="">
      <button type="submit" name="add" class="btn btn-primary flex-shrink-0 theme_bg_color theme_border_color">
        <i class="fas fa-plus-circle"></i> Add
      </button>
    </div>
  </form>

  <!-- Existing Societies -->
  <table class="table table-bordered table-sm">
    <thead class="table-light">
      <tr>
        <th style="width: 500px;"><i class="fas fa-users"></i> Society</th>
        <th style="width: 200px;" class="text-center"><i class="fas fa-cogs"></i> Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($societies as $s): ?>
      <tr>
        <td><?= htmlspecialchars($s['society_name']) ?></td>
        <td class="text-center">
          <form method="post" onsubmit="return confirm('Delete this Society?')">
            <input type="hidden" name="delete_id" value="<?= $s['id'] ?>">
            <button class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include 'includes/footer.php'; ?>
