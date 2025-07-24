<?php
  include 'includes/header.php';
  require_once 'includes/csrf.php';
    require_login();

    $message = "";

    // Handle new category addition
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
        $name = trim($_POST['category_name']);
        if ($name) {
            try {
                $stmt = $pdo->prepare("INSERT INTO request_categories (name) VALUES (?)");
                $stmt->execute([$name]);
                $message = "<div class='alert alert-success'><i class='fas fa-exclamation-triangle'></i> Category added.</div>";
            } catch (PDOException $e) {
                $message = "<div class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> Duplicate or error: " . $e->getMessage() . "</div>";
            }
        }
    }

    // Handle category deletion
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
        $id = intval($_POST['delete_id']);
        $stmt = $pdo->prepare("DELETE FROM request_categories WHERE id = ?");
        $stmt->execute([$id]);
        $message = "<div class='alert alert-info'><i class='fas fa-exclamation-triangle'></i>  Category deleted.</div>";
    }

    // Fetch all categories
    $categories = $pdo->query("SELECT * FROM request_categories ORDER BY name")->fetchAll();
?>

<h2 class="mb-3"><i class="fas fa-tags"></i> Manage Request Categories</h2>

<?= $message ?>

<!-- Add Category Form -->
<form method="post" class="mb-4 row g-2">
  <div class="col-md-6">
    <input type="text" name="category_name" class="form-control" placeholder="New category name" required>
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

<!-- Existing Categories -->
<table class="table table-bordered table-sm">
  <thead class="table-light">
    <tr>
      <th style="width: 500px;"><i class="fa-solid fa-layer-group"></i> Category</th>
      <th style="width: 200px;"><i class="fas fa-cogs"></i> Action</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($categories as $c): ?>
    <tr>
      <td><?= htmlspecialchars($c['name']) ?></td>
      <td class="text-center">
        <form method="post" onsubmit="return confirm('Delete this category?')">
          <input type="hidden" name="delete_id" value="<?= $c['id'] ?>">
          <button class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i> Delete</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php include 'includes/footer.php'; ?>
