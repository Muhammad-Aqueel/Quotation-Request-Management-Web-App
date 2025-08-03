<?php
  include 'includes/header.php';
  require_once 'includes/csrf.php';

  $id = intval($_GET['id'] ?? 0);
  $stmt = $pdo->prepare("SELECT * FROM requests WHERE requests.id = ? AND user_id = ?");
  $stmt->execute([$id,$_SESSION['user_id']]);
  $request = $stmt->fetch();

  // Fetch categories
  $cats = $pdo->query("SELECT * FROM request_categories ORDER BY name")->fetchAll();
  // Fetch societies
  $socs = $pdo->query("SELECT * FROM societies ORDER BY society_name")->fetchAll();
  // Fetch terms and conditions
  $tandc = $pdo->query("SELECT * FROM terms_and_conditions")->fetchAll();

  if (!$request) {
      echo "<div class='container'><div class='alert alert-warning'><i class='fas fa-exclamation-circle'></i> Request not found.</div><a href='requests.php' class='btn btn-secondary btn-sm mt-3'><i class='fas fa-arrow-left'></i> Back</a></div>";
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

<div class="container">
  <h2 class="mb-4"><i class="fas fa-edit"></i> Edit Request</h2>
  <form id="edit_request_form" action="update_request.php" method="post" enctype="multipart/form-data" class="border p-3 rounded bg-light shadow-sm">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    <input type="hidden" name="id" value="<?= $id ?>">
    <div class="d-flex">
      <div class="mb-3" style="flex-grow: 6;">
        <label><i class="fas fa-heading"></i> Title</label>
        <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($request['title']) ?>" required>
      </div>
      <div class="mb-3 ms-2" style="flex-grow: 1;">
          <label><i class="fas fa-calendar-days"></i> Date From... to...</label>
          <input type="text" name="eventdate" id="date-range-picker" class="form-control" readonly value="<?= htmlspecialchars($request['event_date']) ?>">
        </div>
      <div class="mb-3 ms-2" style="flex-grow: 2;">
        <label><i class="fas fa-university"></i> Society</label>
        <select name="society_id" class="form-select" required>
          <?php foreach ($socs as $soc): ?>
            <option value="<?= $soc['id'] ?>" <?= ($request['society_id'] == $soc['id']) ? 'selected' : '' ?>><?= htmlspecialchars($soc['society_name']) ?></option>
          <?php endforeach; ?>
        </select>
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

    <div class="d-flex">
      <div class="mb-3" style="flex-grow: 6;">
        <label><i class="fas fa-note-sticky"></i> Event</label>
        <textarea name="description" class="form-control" rows="1" required><?= htmlspecialchars($request['description']) ?></textarea>
      </div>
      <div class="mb-2 ms-2" style="flex-grow: 6;">
        <label><i class="fa-solid fa-file-contract"></i> Terms and conditions</label>
        <textarea name="tandc" class="form-control bg-body-secondary" rows="1" readonly><?= $tandc[0]['content'] ?></textarea>
      </div>
    </div>

    <div class="mb-3">
      <label><i class="fas fa-boxes"></i> Items</label>
      <div id="items">
        <?php $index = 1; foreach ($items as $i): ?>
          <div class="d-flex gap-2 mb-2 item-row align-items-center" id="item-<?= $i['id'] ?>">
            <label class="form-label my-auto item-index" style="width: 20px;"><?= $index++ ?></label>
            <input type="hidden" name="item_id[]" value="<?= (int)$i['id'] ?>">
            
            <input type="text" name="item_name[]" class="form-control" value="<?= htmlspecialchars($i['item_name']) ?>" placeholder="Item/description with specifications" required>
            <input type="number" name="quantity[]" min="1" class="form-control" value="<?= $i['quantity'] ?>" placeholder="Quantity/unit of measure" required>

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
      <label><i class="fas fa-paperclip"></i> New Attachments (optional):  <i class="fas fa-exclamation-circle"></i> Only pdf, jpg, jpeg, png, docx and doc file formats allowed having max size of 1 MB and 5 MB in total for multiple files.</label>
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

    <button type="submit" class="btn btn-primary theme_bg_color theme_border_color"><i class="fas fa-save"></i> Save Changes</button>
    <a href="requests.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Cancel</a>
  </form>
</div>

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
            <label class="form-label my-auto item-index" style="width: 20px;"></label>
            <input type="hidden" name="item_id[]" value="">
            
            <input type="text" name="item_name[]" class="form-control" value="" placeholder="Item/description with specifications" required>
            <input type="number" name="quantity[]" min="1" class="form-control" value="" placeholder="Quantity/unit of measure" required>

            <button type="button"
                    class="btn btn-sm btn-outline-danger"
                    onclick="deleteRequestItem(0, 0, this)"
                    title="Delete Item">
              <i class="fas fa-trash-alt"></i>
            </button>`;
    document.getElementById('items').appendChild(row);
    updateSerialNumbers();
  }

  function deleteRequestItem(itemId, requestId, btn) {
    if (document.querySelectorAll('#items .item-row').length <= 1) {
        alert("At least one item is required.");
        return;
    }
    
    if(itemId == 0, requestId == 0){
        const container = document.getElementById('items');
        if (container.children.length > 1) {
            btn.parentElement.remove();
            updateSerialNumbers();
        }
        return;
    }

    if (!confirm("Delete this item?")) return;

    fetch('ajax/delete_request_item.php', {
        method: 'POST',
        headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `item_id=${itemId}&request_id=${requestId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
        const row = document.getElementById('item-' + itemId);
        if (row) row.remove();
        updateSerialNumbers();
        } else {
        alert(data.message || 'Failed to delete item.');
        }
    })
    .catch(() => alert('Server error while deleting item.'));
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
