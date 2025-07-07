<?php
  include 'includes/header.php';
  require_once 'includes/csrf.php';

  if(isset($_GET['id']) && isset($_GET['status'])){
    if($_GET['status'] == 0 || $_GET['status'] == 1){
      // Update request status
      $stmt = $pdo->prepare("UPDATE requests SET status = ? WHERE id = ?");
      $stmt->execute([$_GET['status'], $_GET['id']]);
    } else {
      die('<div class="container-fluid mt-4">
          <div class="col-6 m-auto bg-white p-4 shadow-sm rounded">
              <div class="mb-0 alert alert-danger text-center">
                  <h4 class="mb-0 text-center">
                      <i class="fas fa-ban"></i> Invalid method.
                  </h4>
              </div>
          </div>
      </div>');
    }
  }
  $stmt = $pdo->query("SELECT requests.*,request_categories.id as cat_id,request_categories.name as category_name FROM requests JOIN request_categories ON requests.category_id = request_categories.id ORDER BY created_at DESC");
  $requests = $stmt->fetchAll();
  if(isset($_SESSION['request_add_message'])){
    $msg = $_SESSION['request_add_message'];
  }

  // Fetch categories
  $cats = $pdo->query("SELECT * FROM request_categories ORDER BY name")->fetchAll();
?>

<div class="d-flex mb-3 align-items-center justify-content-between">
  <h2><i class="fas fa-tasks"></i> Manage Requests</h2>
  <a href="request_category.php" class="btn btn-sm btn-outline-primary ">
  <i class="fa-solid fa-layer-group"></i> Categories
  </a>
</div>

<form action="add_request.php" method="post" enctype="multipart/form-data" class="border p-3 rounded bg-light mb-4 shadow-sm">
  <h5><i class="fas fa-plus-circle"></i> Add New Request</h5>
  <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
  <div class="d-flex">
    <div class="mb-2" style="flex-grow: 6;">
      <input type="text" class="form-control" name="title" placeholder="Request Title" required>
    </div>
    <div class="mb-3 ms-2" style="flex-grow: 2;">
      <select name="category_id" class="form-select" required>
        <option value="" disabled selected>Select Category</option>
        <?php foreach ($cats as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="mb-2">
    <textarea name="description" class="form-control" placeholder="Description"></textarea>
  </div>
  <div class="mb-2">
    <label><i class="fas fa-box"></i> Items</label>
    <div id="items">
      <div class="d-flex gap-2 mb-2 item-row">
        <input type="text" name="item_name[]" class="form-control" placeholder="Item Name" required>
        <input type="number" name="quantity[]" min="1" class="form-control" placeholder="Quantity" required>
      </div>
    </div>
    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addItem()">
      <i class="fas fa-plus"></i> Add Item
    </button>
  </div>
  <div class="mb-3">
    <label><i class="fas fa-paperclip"></i> Attachments (optional): <i class="fas fa-exclamation-triangle"></i> Only pdf, jpg, jpeg, png, docx and doc files format allowed having max size of 1 MB and 5 MB in total for multiple files.</label>
    <input type="file" name="attachments[]" class="form-control" multiple>
  </div>

  <button type="submit" class="btn btn-primary theme_bg_color theme_border_color">
    <i class="fas fa-check-circle"></i> Create Request
  </button>
</form>

<form method="post" action="bulk_request_action.php">
  <div class="d-flex flex-wrap justify-content-between align-items-baseline">
    <h5><i class="fas fa-list-ul"></i> Existing Requests</h5>
    <div class="d-flex gap-2 mb-2">
      <button type="button" class="btn border-light" disabled><i class="fa-solid fa-list-check"></i> Bulk</button>
      <button type="submit" name="action" value="activate" class="btn btn-success btn-sm">
        <i class="fas fa-toggle-on"></i> Activate
      </button>
      <button type="submit" name="action" value="deactivate" class="btn btn-secondary btn-sm">
        <i class="fas fa-toggle-off"></i> Deactivate
      </button>
      <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm" onclick="return confirm('Delete permanently?')">
        <i class="fas fa-trash-alt"></i> Delete
      </button>  
    </div>
  </div>

  <div class="table-responsive">
    <table id="requestsTable" class="table table-bordered table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th><input type="checkbox" onclick="toggleAll(this)" title="Select All"></th>
          <th><i class="fas fa-heading"></i> Title</th>
          <th><i class="fas fa-layer-group"></i> Category</th>
          <th><i class="fas fa-circle-info"></i> Description</th>
          <th><i class="fas fa-calendar-alt"></i> Created</th>
          <th><i class="fas fa-cogs"></i> Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($requests as $r): ?>
          <tr>
            <td><input type="checkbox" name="request_ids[]" value="<?= $r['id'] ?>"></td>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= htmlspecialchars($r['category_name']) ?></td>
            <td class="text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($r['description']) ?>"><?= htmlspecialchars($r['description']) ?></td>
            <td><?= date('d-M-Y h:i A', strtotime($r['created_at'])) ?></td>
            <td class="text-center">
              <?php if($r['status'] == '1'): ?>
                <a href="requests.php?id=<?= $r['id'] ?>&status=0" class="btn btn-sm btn-secondary">
                  <i class="fa-solid fa-eye-slash"></i> Deactivate
                </a>
                <a href="edit_request.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">
                  <i class="fas fa-edit"></i> Edit
                </a>
              <?php else: ?>
                <a href="requests.php?id=<?= $r['id'] ?>&status=1" class="btn btn-sm btn-success">
                  <i class="fas fa-eye"></i> Active
                </a>
              <?php endif; ?>
              <a href="delete_request.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this request?')">
                <i class="fas fa-trash-alt"></i> Delete
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</form>

<?php if (isset($_SESSION['request_add_message'])): ?>
  <div class="container-fluid">
    <div id="msg-modal" class="modal d-block" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fa-solid fa-comment-dots"></i> Submission Feedback</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="close_modal()"></button>
          </div>
          <div class="modal-body">
            <!-- <p>Modal body text goes here.</p> -->
            <?= $msg ?>
          </div>
          <!-- <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary">Save changes</button>
          </div> -->
        </div>
      </div>
    </div>
    <div id="msg-backdrop" class="modal-backdrop fade show"></div>
  </div>
<?php unset($_SESSION['request_add_message']); endif; ?>

<script>
  function addItem() {
    const row = document.createElement('div');
    row.className = 'd-flex gap-2 mb-2 item-row';
    row.innerHTML = `
      <input type="text" name="item_name[]" class="form-control" placeholder="Item Name" required>
      <input type="number" name="quantity[]" min="1" class="form-control" placeholder="Quantity" required>
      <button type="button" title="Delete Item" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
        <i class="fas fa-trash-alt"></i></button>
    `;
    document.getElementById('items').appendChild(row);
  }

  function removeItem(btn) {
    const container = document.getElementById('items');
    if (container.children.length > 1) {
      btn.parentElement.remove();
    } else {
      alert("At least one item is required.");
    }
  }
</script>

<?php include 'includes/footer.php'; ?>
