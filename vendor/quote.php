<?php
  include 'includes/header.php';
  require_once 'includes/db.php';

  $request_id = intval($_GET['request_id'] ?? 0);
  if (!$request_id) {
    echo "<div class='alert alert-warning'><i class='fas fa-exclamation-triangle'></i> No request selected.</div>";
    include 'includes/footer.php';
    exit;
  }

  $stmt = $pdo->prepare("SELECT requests.*,request_categories.id as cat_id,request_categories.name as category_name FROM requests JOIN request_categories ON requests.category_id = request_categories.id WHERE requests.id = ?");
  $stmt->execute([$request_id]);
  $request = $stmt->fetch();

  if (!$request) {
    echo "<div class='alert alert-danger'><i class='fas fa-ban'></i> Request not found.</div>";
    include 'includes/footer.php';
    exit;
  }

  $stmt = $pdo->prepare("SELECT * FROM request_attachments WHERE request_id = ?");
  $stmt->execute([$request_id]);
  $attachments = $stmt->fetchAll();

  $stmt = $pdo->prepare("SELECT * FROM request_items WHERE request_id = ?");
  $stmt->execute([$request_id]);
  $items = $stmt->fetchAll();
?>

<h2 class="mb-3"><i class="fas fa-file-signature"></i> Quotation(<?= htmlspecialchars($request['category_name']) ?>): <?= htmlspecialchars($request['title']) ?></h2>

<form method="post" action="submit_quote.php" enctype="multipart/form-data" class="border p-3 rounded bg-light mb-4 shadow-sm">
  <input type="hidden" name="request_id" value="<?= $request_id ?>">
  <h5 class="mb-3"><i class="fas fa-circle-info"></i> Description</h5>
  <div class="mb-2">
    <textarea class="form-control" name="description" tabindex="-1" rows="2" readonly disabled><?= htmlspecialchars($request['description']) ?></textarea>
  </div>
  <?php if ($attachments): ?>
    <h6><i class="fas fa-folder-open"></i> See Attachments</h6>
    <ul>
      <?php foreach ($attachments as $a): ?>
        <li>
          <a href="<?= $a['filepath'] ?>" target="_blank">
            <i class="fas fa-paperclip"></i> <?= htmlspecialchars($a['filename']) ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  <h5 class="mt-4 mb-3"><i class="fas fa-id-badge"></i> Vendor Info</h5>
  <div class="mb-2">
    <input class="form-control" name="name" placeholder="Your Name" required>
  </div>
  <div class="mb-2">
    <input class="form-control" name="company" placeholder="Company Name" required>
  </div>
  <div class="mb-2">
    <input class="form-control" name="ntn" placeholder="NTN" required>
  </div>
  <div class="mb-2">
    <input class="form-control" name="email" type="email" placeholder="Email" required>
  </div>
  <div class="mb-2">
    <input class="form-control" name="phone" placeholder="Phone" required>
  </div>

  <h5 class="mt-4 mb-3"><i class="fas fa-boxes"></i> Quotation Items</h5>
  <?php foreach ($items as $item): ?>
    <div class="mb-3">
      <label class="form-label">
        <i class="fas fa-box"></i> <?= htmlspecialchars($item['item_name']) ?> (Qty: <?= $item['quantity'] ?>)
      </label>
      <input type="hidden" name="request_item_id[]" value="<?= $item['id'] ?>">
      <input type="number" name="unit_price[]" class="form-control" step="0.01" min="0.01" placeholder="Unit Price" required>
    </div>
  <?php endforeach; ?>

  <h5 class="mt-4 mb-3"><i class="fas fa-comment-alt"></i> Message / Notes</h5>
  <textarea class="form-control mb-3" name="message" rows="4" placeholder="Optional message..."></textarea>

  <h5 class="mb-2"><i class="fas fa-paperclip"></i> Attachments (optional): </h5><h6><i class="fas fa-exclamation-triangle"></i> Only pdf, jpg, jpeg, png, docx and doc files format allowed having max size of 1 MB and 5 MB in total for multiple files.</h6>
  <input type="file" name="attachments[]" class="form-control mb-3" multiple>

  <!-- CAPTCHA Section -->
  <div id="captchaWrapper" class="my-3"></div>

  <!-- Submit Button -->
  <button type="submit" class="btn btn-primary theme_bg_color theme_border_color">
    <i class="fas fa-paper-plane"></i> Submit Quotation
  </button>
</form>
<div class="container-fluid">
    <div id="msg-modal" class="modal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h6 class="modal-title"><i class="fa-solid fa-circle-info"></i> Validation</h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="close_modal()"></button>
          </div>
          <div class="modal-body">
            <!-- <p>Modal body text goes here.</p> -->
            <div class='alert alert-danger'><i class='fas fa-ban'></i> CAPTCHA verification failed.</div>
          </div>
          <!-- <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary">Save changes</button>
          </div> -->
        </div>
      </div>
    </div>
    <div id="msg-backdrop" class="modal-backdrop fade d-none"></div>
</div>
<?php include 'includes/footer.php'; ?>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("form");
    form.addEventListener("submit", function (e) {
      const input = document.getElementById("captchaInput");
      const actual = document.getElementById("captcha-value");

      if (input && actual && input.value.trim() !== actual.value.trim()) {
        e.preventDefault(); // stop form submission
        // alert("CAPTCHA verification failed. Please try again.");
        msg_m.classList.add('d-block');
        msg_b.classList.add('show');
        msg_b.classList.remove('d-none');
        input.focus();
      }
    });
  });
  document.addEventListener("DOMContentLoaded", function () {
    const captchaObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          loadCaptcha();
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });

    const captchaWrapper = document.getElementById("captchaWrapper");
    if (captchaWrapper) captchaObserver.observe(captchaWrapper);

    function loadCaptcha() {
      const captchaValue = generateCaptcha();
      const captchaHTML = `
        <div class="captcha">
          <label class="form-label"><i class="fas fa-robot"></i> Enter the code below:</label>
          <div class="d-flex gap-2"><div class="p-2 rounded bg-dark border text-light text-center mb-2 fw-bold fs-5" id="captcha-image">${captchaValue}</div>
          <input id="captchaInput" type="text" name="captcha" class="form-control mb-2" placeholder="Enter CAPTCHA" required></div>
          <input id="captcha-value" type="hidden" name="captcha_value" value="${captchaValue}">
        </div>`;
      captchaWrapper.innerHTML = captchaHTML;
    }

    function generateCaptcha() {
      let result = '';
      for (let i = 0; i < 4; i++) {
        result += Math.floor(Math.random() * 10);
      }
      return result;
    }
  });
</script>