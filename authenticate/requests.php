<?php
  include 'includes/header.php';
  require_once 'includes/csrf.php';

  if(isset($_GET['id']) && isset($_GET['status'])){
    if($_GET['status'] == 0 || $_GET['status'] == 1){
      // Update request status
      $stmt = $pdo->prepare("UPDATE requests SET status = ? WHERE id = ? AND user_id = ?");
      $stmt->execute([$_GET['status'], $_GET['id'], $_SESSION['user_id']]);
    } else {
      echo ('<div class="container-fluid mt-4">
          <div class="col-6 m-auto bg-body p-4 shadow-sm rounded">
              <div class="mb-0 alert alert-warning text-center">
                  <h4 class="mb-0 text-center">
                      <i class="fas fa-exclamation-circle"></i> Invalid method.
                  </h4>
              </div>
          </div>
      </div>');
      include 'includes/footer.php';
      exit;
    }
  }

  if ($_SESSION['user_role'] === 'student'){
    $stmt = $pdo->prepare("SELECT r.*, s.id AS soc_id, s.society_name, COUNT(q.id) AS quotations_count FROM requests r JOIN societies s ON r.society_id = s.id LEFT JOIN quotations q ON q.request_id = r.id LEFT JOIN vendors v ON q.vendor_id = v.id WHERE r.user_id = ? GROUP BY r.id ORDER BY r.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $requests = $stmt->fetchAll();
  } else {
    $requests = $pdo->query("SELECT r.*, s.id AS soc_id, s.society_name, COUNT(q.id) AS quotations_count FROM requests r JOIN societies s ON r.society_id = s.id LEFT JOIN quotations q ON q.request_id = r.id WHERE r.status = '0' GROUP BY r.id ORDER BY r.created_at DESC")->fetchAll();
  }

  if(isset($_SESSION['request_add_message'])){
    $msg = $_SESSION['request_add_message'];
  }

  // Fetch categories
  $cats = $pdo->query("SELECT * FROM request_categories ORDER BY name")->fetchAll();
  // Fetch societies
  $socs = $pdo->query("SELECT * FROM societies ORDER BY society_name")->fetchAll();
  // Fetch terms and conditions
  $tandc = $pdo->query("SELECT * FROM terms_and_conditions")->fetchAll();

  if(isset($_POST['tandcedit'])){
    $stmt = $pdo->prepare("UPDATE terms_and_conditions SET content=? WHERE id = 1");
    $stmt->execute([$_POST['tandc']]);
    $tandc = $pdo->query("SELECT * FROM terms_and_conditions")->fetchAll();
  }
?>

<?php if ($_SESSION['user_role'] === 'student'): ?>
  <div class="container">
    <div class="d-flex mb-3 align-items-center justify-content-between">
      <h2><i class="fas fa-tasks"></i> Manage Requests</h2>
      <div>
        <a href="request_category.php" class="btn btn-sm btn-outline-primary ">
          <i class="fa-solid fa-layer-group"></i> Categories
        </a>
      </div>
    </div>
    
    <form action="add_request.php" method="post" enctype="multipart/form-data" class="border p-3 rounded bg-light mb-4 shadow-sm">
      <h5><i class="fas fa-plus-circle"></i> Add New Request</h5>
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
      <div class="d-flex mb-3 row-gap-2 column-gap-2 flex-wrap">
        <div style="flex-grow: 6;">
          <input type="text" class="form-control bg-body-secondary" name="title" placeholder="Request Title" readonly required value="<?php
            $latestId = isset($requests[0]) ? $requests[0]['id'] : null; // Last ID from database
            $id = $latestId + 1; // Last ID + 1
            $formatted_title = sprintf("PR%04d", $id);
            echo $formatted_title; // Output e.g: PR0001
          ?>" >
        </div>
        <div style="flex-grow: 1;">
          <input type="text" name="eventdate" id="date-range-picker" class="form-control" placeholder="From... to..." readonly>
        </div>
        <div style="flex-grow: 2;">
          <select name="society_id" class="form-select" required>
            <option value="" disabled selected>Select Society</option>
            <?php foreach ($socs as $soc): ?>
              <option value="<?= $soc['id'] ?>"><?= htmlspecialchars($soc['society_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="flex-grow: 2;">
          <select name="category_id" class="form-select" required>
            <option value="" disabled selected>Select Category</option>
            <?php foreach ($cats as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="d-flex mb-2 row-gap-2 column-gap-2 flex-wrap">
        <div style="flex-grow: 6;">
          <textarea name="description" class="form-control" placeholder="Event" rows="1" required></textarea>
        </div>
        <div class="d-flex column-gap-2" style="flex-grow: 6;">
          <div style="flex-grow: 6;">
            <textarea name="tandc" class="form-control bg-body-secondary" placeholder="Terms and conditions" rows="1" readonly><?= $tandc[0]['content'] ?></textarea>
          </div>
          <button type="button" title="edit terms and conditions" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#tandcModal">
            <i class="fas fa-edit"></i>
          </button>
        </div>
      </div>
      <div class="mb-2">
        <label><i class="fas fa-box"></i> Items</label>
        <div id="items">
          <div class="d-flex gap-2 mb-2 item-row">
            <label class="form-label my-auto item-index" style="width: 20px;">1</label>
            <input type="text" name="item_name[]" class="form-control" placeholder="Item/description with specifications" required>
            <input type="number" name="quantity[]" min="1" class="form-control" placeholder="Quantity/unit of measure" required>
          </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addItem()">
          <i class="fas fa-plus"></i> Add Item
        </button>
      </div>
      <div class="mb-3">
        <label><i class="fas fa-paperclip"></i> Attachments (optional): <i class="fas fa-exclamation-circle"></i> Only pdf, jpg, jpeg, png, docx and doc files format allowed having max size of 1 MB and 5 MB in total for multiple files.</label>
        <input type="file" name="attachments[]" class="form-control" multiple>
      </div>
      <button type="submit" class="btn btn-primary theme_bg_color theme_border_color">
        <i class="fas fa-check-circle"></i> Create Request
      </button>
    </form>
  </div>
<?php endif; ?>
<form method="post" action="bulk_request_action.php">
  <div class="d-flex flex-wrap justify-content-between align-items-baseline">
    <h5><i class="fas fa-list-ul"></i> Existing Requests</h5>
    <?php if ($_SESSION['user_role'] === 'student'): ?>
      <div class="d-flex gap-2 mb-2">
        <button type="button" class="btn border-0" disabled><i class="fa-solid fa-list-check"></i> Bulk</button>
        <button type="submit" name="action" value="activate" class="btn btn-success btn-sm">
          <i class="fas fa-toggle-on"></i> Open
        </button>
        <button type="submit" name="action" value="deactivate" class="btn btn-secondary btn-sm">
          <i class="fas fa-toggle-off"></i> Close
        </button>
        <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm" onclick="return confirm('Delete permanently?')">
          <i class="fas fa-trash-alt"></i> Delete
        </button>  
      </div>
    <?php endif; ?>
  </div>

  <div class="table-responsive">
    <table id="requestsTable" class="table table-bordered table-hover align-middle table-striped">
      <thead class="table-light">
        <tr>
          <th><input type="checkbox" onclick="toggleAll(this)" title="Select All"></th>
          <th><i class="fas fa-heading"></i> Title</th>
          <th><i class="fas fa-university"></i> Society</th>
          <th><i class="fas fa-circle-info"></i> Event name</th>
          <th><i class="fas fa-calendar-alt"></i> Date</th>
          <th title="View Quotations"><i class="fas fa-money-check-dollar"></i></th>
          <th class="text-center"><i class="fa-solid fa-user-gear"></i>
            <?php if ($_SESSION['user_role'] === 'student'): ?>
              Approval &nbsp;<i class="fas fa-info-circle"></i> Status
            <?php else: ?>
              Approval
            <?php endif; ?>
          </th>
          <th><i class="fas fa-cogs"></i> Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($requests as $r): ?>
          <?php
            $astatus = $r['approval_status'];
            switch ($astatus) {
              case 'Approved':
                $abadgeClass = 'bg-success';
                break;
              case 'Rejected':
                $abadgeClass = 'text-dark bg-warning';
                break;
              case 'Pending':
              default:
                $abadgeClass = 'bg-secondary';
                break;
            }
            $status = $r['status'];
            switch ($status) {
              case '0':
                $sbadgeClass = 'bg-secondary';
                $stext = 'Closed';
                break;
              case '1':
              default:
                $sbadgeClass = 'bg-success';
                $stext = 'Open';
                break;
            }
          ?>
          <tr>
            <td><input type="checkbox" name="request_ids[]" value="<?= $r['id'] ?>"></td>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= htmlspecialchars($r['society_name']) ?></td>
            <td class="text-truncate" style="max-width: 100px;" title="<?= htmlspecialchars($r['description']) ?>"><?= htmlspecialchars($r['description']) ?></td>
            <td class="text-truncate" style="max-width: 100px;" title="<?= htmlspecialchars($r['event_date']) ?>"><?= $r['event_date'] ?></td>
            <td class="text-center">
                <a href="quotations.php?request_id=<?= $r['id'] ?>" class="btn btn-sm btn-info position-relative d-inline-block" target="_blank" title="View Quotations">
                  <i class="fas fa-eye"></i>
                  <span class="position-absolute opacity-75 badge rounded-pill theme_bg_color" style="top: -0.5rem; right: -0.5rem;"><?= $r['quotations_count'] ?></span>
                </a>
            </td>
            <td class="text-center">
              <span class="badge <?= $abadgeClass ?>"><?= htmlspecialchars($astatus) ?></span>
              <?php if ($_SESSION['user_role'] === 'student'): ?>
                &nbsp;
                <span class="badge <?= $sbadgeClass ?>"><?= $stext ?></span>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <?php if ($_SESSION['user_role'] === 'student'): ?>
                <?php if($r['status'] == '1'): ?>
                  <a href="requests.php?id=<?= $r['id'] ?>&status=0" class="btn btn-sm btn-secondary" title="Close Request">
                    <i class="fa-solid fa-eye-slash"></i>
                  </a>
                  <a href="edit_request.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning" title="Edit Request">
                    <i class="fas fa-edit"></i>
                  </a>
                <?php else: ?>
                  <a href="requests.php?id=<?= $r['id'] ?>&status=1" class="btn btn-sm btn-success" title="Open Request">
                    <i class="fas fa-eye"></i>
                  </a>
                  <?php endif; ?>
                  <a href="delete_request.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this request?')" title="Delete Request">
                    <i class="fas fa-trash-alt"></i>
                  </a>
              <?php else: ?>
                <a href="request_approval.php?id=<?= $r['id'] ?>&set=Pending" class="btn btn-sm btn-secondary" title="Pending Request">
                  <i class="fas fa-question-circle"></i>
                </a>
                <a href="request_approval.php?id=<?= $r['id'] ?>&set=Approved" class="btn btn-sm btn-success" title="Approve Request">
                  <i class="fas fa-check-circle"></i>
                </a>
                <a href="request_approval.php?id=<?= $r['id'] ?>&set=Rejected" class="btn btn-sm btn-warning" title="Reject Request">
                  <i class="fas fa-times-circle"></i>
                </a>
              <?php endif; ?>
                <a href="view_request.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-info" title="View Request">
                  <i class="fas fa-eye"></i>
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

<div class="container-fluid">
  <div class="modal fade" id="tandcModal"  data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa-solid fa-edit"></i> Terms and Conditions</h5>
          <button type="button" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="post">
          <div class="modal-body">
            <!-- Modal body goes here.-->
            <div class="mb-2">
              <textarea name="tandc" class="form-control" placeholder="Terms and conditions" rows="5"><?= $tandc[0]['content'] ?></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" name="tandcedit" class="btn btn-primary theme_bg_color theme_border_color"><i class="fas fa-save"></i> Save changes</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times-circle"></i> Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php if ($_SESSION['user_role'] === 'student'): ?>
  <script>
    function addItem() {
      const row = document.createElement('div');
      row.className = 'd-flex gap-2 mb-2 item-row';
      row.innerHTML = `
        <label class="form-label my-auto item-index" style="width: 20px;"></label>
        <input type="text" name="item_name[]" class="form-control" placeholder="Item/description with specifications" required>
        <input type="number" name="quantity[]" min="1" class="form-control" placeholder="Quantity/unit of measure" required>
        <button type="button" title="Delete Item" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
          <i class="fas fa-trash-alt"></i></button>
      `;
      document.getElementById('items').appendChild(row);
      updateSerialNumbers();
    }

    function removeItem(btn) {
      const container = document.getElementById('items');
      if (container.children.length > 1) {
        btn.parentElement.remove();
        updateSerialNumbers();
      } else {
        alert("At least one item is required.");
      }
    }
  </script>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>
