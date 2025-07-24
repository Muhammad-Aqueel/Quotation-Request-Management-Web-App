<?php
  include 'includes/header.php';
  require_once 'includes/csrf.php';
    require_login();

    $message = "";

    // Handle new Society addition
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
        $name = trim($_POST['society_name']);
        if ($name) {
            try {
                $stmt = $pdo->prepare("INSERT INTO societies (society_name) VALUES (?)");
                $stmt->execute([$name]);
                $message = "<div class='alert alert-success'><i class='fas fa-exclamation-triangle'></i> Society added.</div>";
            } catch (PDOException $e) {
                $message = "<div class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> Duplicate or error: " . $e->getMessage() . "</div>";
            }
        }
    }

    // Handle Society deletion
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
        $id = intval($_POST['delete_id']);
        $stmt = $pdo->prepare("DELETE FROM societies WHERE id = ?");
        $stmt->execute([$id]);
        $message = "<div class='alert alert-info'><i class='fas fa-exclamation-triangle'></i>  Society deleted.</div>";
    }

    // Fetch all Societies
    $societies = $pdo->query("SELECT * FROM societies ORDER BY society_name")->fetchAll();
?>

<h2 class="mb-3"><i class="fas fa-university"></i> Manage Societies</h2>

<?= $message ?>

<!-- Add Society Form -->
<form method="post" class="mb-4 row g-2">
  <div class="col-md-6">
    <input type="text" name="society_name" class="form-control" placeholder="New society name" required>
  </div>
  <div class="col-md-auto">
    <button type="submit" name="add" class="btn btn-primary theme_bg_color theme_border_color"><i class="fas fa-plus-circle"></i> Add</button>
  </div>
  <div class="col-md-auto">
    <a href="requests.php" class="btn btn-secondary">
      <i class="fas fa-arrow-left"></i> Back
    </a>
  </div>
</form>

<!-- Existing Societies -->
<table class="table table-bordered table-sm">
  <thead class="table-light">
    <tr>
      <th style="width: 500px;"><i class="fas fa-users"></i> Society</th>
      <th style="width: 200px;"><i class="fas fa-cogs"></i> Action</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($societies as $s): ?>
    <tr>
      <td><?= htmlspecialchars($s['society_name']) ?></td>
      <td class="text-center">
        <form method="post" onsubmit="return confirm('Delete this Society?')">
          <input type="hidden" name="delete_id" value="<?= $s['id'] ?>">
          <button class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i> Delete</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php include 'includes/footer.php'; ?>
