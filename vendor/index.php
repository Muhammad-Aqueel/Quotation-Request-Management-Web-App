<?php
  session_start();
  include 'includes/header.php';
  require_once 'includes/db.php';

  $stmt = $pdo->query("SELECT * FROM requests");
  $requests = $stmt->fetchAll();
  if(isset($_SESSION['quote_submit_message'])){
      $msg = $_SESSION['quote_submit_message'];
  }
?>
<div class="bg-img"></div>
<!-- <h3 class="mb-4 text-center"><i class="fas fa-file-invoice"></i> Vendors Portal</h3> -->
<h2 class="mb-4"><i class="fas fa-bullhorn"></i> Available Requests</h2>
<!-- Filter Sidebar Widget -->
<div id="filterSidebar" class="filter-sidebar shadow-lg">
  <div class="card" style="border-radius: 6px 0px 0px 6px;">
    <div class="card-header bg-primary text-white fw-bold py-2" style="background-color: #903035 !important;border-radius: 5px 0px 0px 0px;">
      <div class="d-flex align-items-center justify-content-between">
        <div><i class="fas fa-filter"></i> Filter Requests</div>
        <i id="closeFilterBtn" class="fas fa-close rounded-1 py-1 px-2 theme_outline_btn_color"></i>
      </div>
    </div>
    <div class="card-body">
      <form id="filterForm" class="row g-2">
        <div class="col-12">
          <label class="form-label">Category</label>
          <select name="category_id" class="form-select form-select-sm">
            <option value="">-- All Categories --</option>
            <?php
              $categories = $pdo->query("SELECT * FROM request_categories ORDER BY name")->fetchAll();
              foreach ($categories as $cat):
            ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6">
          <label class="form-label">From</label>
          <input type="date" name="date_from" class="form-control form-control-sm">
        </div>
        <div class="col-6">
          <label class="form-label">To</label>
          <input type="date" name="date_to" class="form-control form-control-sm">
        </div>
        <div class="col-12 mt-2">
          <button type="submit" class="btn btn-sm btn-primary theme_bg_color theme_bg_color theme_border_color w-100">
            <i class="fas fa-search"></i> Apply Filter
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Filter Toggle Button -->
<button id="toggleFilterBtn" class="active btn btn-primary theme_bg_color theme_border_color shadow-sm toggle-filter-btn" title="Filter Requests">
  <i class="fas fa-filter"></i>
</button>


<?php if ($requests): ?>
  <!-- Requests will be loaded here -->
  <div id="requestsList" class="row g-3">
    <!-- Loaded by AJAX -->
  </div>
  <?php else: ?>
  <p class="text-muted"><i class="fas fa-info-circle"></i> No active requests found.</p>
<?php endif; ?>

<?php if (isset($_SESSION['quote_submit_message'])): ?>
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
<?php unset($_SESSION['quote_submit_message']); endif; ?>

<script>
  document.getElementById("filterForm").addEventListener("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch("fetch_requests.php", {
      method: "POST",
      body: formData
    })
    .then(res => res.text())
    .then(html => {
      document.getElementById("requestsList").innerHTML = html;
    })
    .catch(() => {
      document.getElementById("requestsList").innerHTML = "<div class='alert alert-danger'>Failed to load.</div>";
    });
  });

  // Auto-load all on page load
  document.getElementById("filterForm").dispatchEvent(new Event("submit"));

  document.getElementById("toggleFilterBtn").addEventListener("click", function () {
    document.getElementById("filterSidebar").classList.toggle("active");
    document.getElementById("toggleFilterBtn").classList.toggle("active");
  });

  document.getElementById("closeFilterBtn").addEventListener("click", function () {
    document.getElementById("filterSidebar").classList.toggle("active");
    document.getElementById("toggleFilterBtn").classList.toggle("active");
  });
</script>


<?php include 'includes/footer.php'; ?>
