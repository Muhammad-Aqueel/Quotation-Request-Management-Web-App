<?php
  include 'includes/header.php';
  require_once 'includes/csrf.php';

  $id = intval($_GET['id'] ?? 0);
  $stmt = $pdo->prepare("SELECT *,request_categories.name as category_name FROM requests JOIN request_categories ON requests.category_id = request_categories.id WHERE requests.id = ?");
  $stmt->execute([$id]);
  $request = $stmt->fetch();

  // Fetch categories
  $cats = $pdo->query("SELECT * FROM request_categories ORDER BY name")->fetchAll();

  if (!$request) {
      echo "<div class='alert alert-danger'><i class='fas fa-ban'></i> Request not found.</div>";
      include 'includes/footer.php';
      exit;
  }

  $stmt = $pdo->prepare("SELECT * FROM request_items WHERE request_id = ?");
  $stmt->execute([$id]);
  $items = $stmt->fetchAll();

  $stmt = $pdo->prepare("SELECT * FROM request_attachments WHERE request_id = ?");
  $stmt->execute([$id]);
  $attachments = $stmt->fetchAll();
  if(isset($_SESSION['request_update_message'])){
    $msg = $_SESSION['request_update_message'];
  }
?>

<h2 class="mb-4"><i class="fas fa-edit"></i> Edit Request</h2>

<form id="edit_request_form" action="update_request.php" method="post" enctype="multipart/form-data" class="border p-3 rounded bg-light shadow-sm">
  <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
  <input type="hidden" name="id" value="<?= $id ?>">
  <div class="d-flex">
    <div class="mb-3" style="flex-grow: 6;">
      <label><i class="fas fa-heading"></i> Title</label>
      <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($request['title']) ?>" required>
    </div>

    <div class="mb-3 ms-2" style="flex-grow: 2;">
      <label><i class="fas fa-layer-group"></i> Category</label>
      <select name="category_id" class="form-select" required>
        <?php foreach ($cats as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= ($request['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="mb-3">
    <label><i class="fas fa-align-left"></i> Description</label>
    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($request['description']) ?></textarea>
  </div>

  <div class="mb-3">
    <label><i class="fas fa-boxes"></i> Items</label>
    <div id="items">
      <?php foreach ($items as $i): ?>
        <div class="d-flex gap-2 mb-2 item-row align-items-center" id="item-<?= $i['id'] ?>">
          <input type="hidden" name="item_id[]" value="<?= (int)$i['id'] ?>">
          
          <input type="text" name="item_name[]" class="form-control" value="<?= htmlspecialchars($i['item_name']) ?>" placeholder="Item Name" required>
          <input type="number" name="quantity[]" min="1" class="form-control" value="<?= $i['quantity'] ?>" placeholder="Quantity" required>

          <button type="button"
                  class="btn btn-sm btn-outline-danger"
                  onclick="deleteRequestItem(<?= $i['id'] ?>, <?= $id ?>, this)"
                  title="Delete Item">
            <i class="fas fa-trash-alt"></i>
          </button>
        </div>
      <?php endforeach; ?>
    </div>
    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addItem()">
      <i class="fas fa-plus"></i> Add Item
    </button>
  </div>

  <div class="mb-3">
    <label><i class="fas fa-paperclip"></i> New Attachments (optional):  <i class="fas fa-exclamation-triangle"></i> Only pdf, jpg, jpeg, png, docx and doc file formats allowed having max size of 1 MB and 5 MB in total for multiple files.</label>
    <input type="file" name="attachments[]" class="form-control" multiple>
  </div>

  <?php if ($attachments): ?>
    <h6><i class="fas fa-folder-open"></i> Existing Attachments</h6>
    <ul id="attachment-list">
      <?php foreach ($attachments as $a): ?>
        <li id="attachment-<?= $a['id'] ?>">
          <a href="<?= $a['filepath'] ?>" target="_blank">
            <i class="fas fa-paperclip"></i> <?= htmlspecialchars($a['filename']) ?>
          </a>
          <button type="button"
            class="btn btn-link p-0 text-danger ms-2"
            onclick="deleteAttachment(<?= $a['id'] ?>, <?= $id ?>)">
            <i class="fas fa-trash-alt"></i>
          </button>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
  <a href="requests.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Cancel</a>
</form>

<?php if (isset($_SESSION['request_update_message'])): ?>
  <div class="container-fluid">
    <div id="msg-modal" class="modal d-block" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fa-solid fa-comment-dots"></i> Updation Feedback</h5>
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
<?php unset($_SESSION['request_update_message']); endif; ?>

<script>
function addItem() {
  const row = document.createElement('div');
  row.className = 'd-flex gap-2 mb-2 item-row align-items-center';
  row.innerHTML = `
          <input type="hidden" name="item_id[]" value="">
          
          <input type="text" name="item_name[]" class="form-control" value="" placeholder="Item Name" required>
          <input type="number" name="quantity[]" min="1" class="form-control" value="" placeholder="Quantity" required>

          <button type="button"
                  class="btn btn-sm btn-outline-danger"
                  onclick="deleteRequestItem(0, 0, this)"
                  title="Delete Item">
            <i class="fas fa-trash-alt"></i>
          </button>`;
  document.getElementById('items').appendChild(row);
}

// function removeItem(btn) {
  // const container = document.getElementById('items');

  // if (container.children.length > 1) {
  //   // btn.parentElement.remove();
  //   btn.parentElement.classList.add('d-none');
  //   btn.parentElement.querySelector('.delete-flag').value = "1";
  // } else {
  //   alert("At least one item is required.");
  // }
// }
</script>

<?php include 'includes/footer.php'; ?>
