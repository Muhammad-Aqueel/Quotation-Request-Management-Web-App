<?php
  include 'includes/header.php';
  require_once 'includes/db.php';

  $request_id = intval($_GET['request_id'] ?? 0);
  if (!$request_id) {
    echo "<div class='alert alert-warning'><i class='fas fa-exclamation-circle'></i> No request selected.</div>";
    include 'includes/footer.php';
    exit;
  }

  $stmt = $pdo->prepare("SELECT requests.*,request_categories.id as cat_id,request_categories.name as category_name FROM requests JOIN request_categories ON requests.category_id = request_categories.id WHERE requests.id = ?");
  $stmt->execute([$request_id]);
  $request = $stmt->fetch();

  if (!$request) {
    echo "<div class='alert alert-warning'><i class='fas fa-exclamation-circle'></i> Request not found.</div>";
    include 'includes/footer.php';
    exit;
  }

  $stmt = $pdo->prepare("SELECT * FROM request_attachments WHERE request_id = ?");
  $stmt->execute([$request_id]);
  $attachments = $stmt->fetchAll();

  $stmt = $pdo->prepare("SELECT * FROM request_items WHERE request_id = ?");
  $stmt->execute([$request_id]);
  $items = $stmt->fetchAll();

  // Fetch terms and conditions
  $tandc = $pdo->query("SELECT * FROM terms_and_conditions")->fetchAll();
?>
  <!-- Header -->
  <div class="row mb-4 align-items-stretch row-gap-2">
    <!-- Left Section: Titles -->
    <div class="col-md-6 d-flex">
        <div class="border rounded p-3 w-100 h-100 transition">
          <h2 class="fw-bold mb-2">Quotation: <?= $request['title'] ?></h2>
          <h4 class="text-muted mb-0">Event: <?= $request['description'] ?></h4>
        </div>
    </div>
    <!-- Mid Section: Meta Info -->
    <div class="col-md-3 d-flex">
      <div class="border rounded p-3 w-100 h-100">
        <p class="mb-2">
          <i class="fas fa-calendar-alt"></i>
          <strong>Event Date:</strong><br> <?= htmlspecialchars($request['event_date']) ?>
        </p>
        <p class="mb-0">
          <i class="fas fa-layer-group"></i>
          <strong>Category:</strong> <?= htmlspecialchars($request['category_name']) ?>
        </p>
      </div>
    </div>
    <!-- Attachments -->
    <div class="col-md-3 d-flex">
      <div class="card h-100 w-100 shadow-sm">
        <div class="card-header bg-light">
          <h6 class="mb-0">
          <i class="fas fa-folder-open"></i> See Attachments
          </h6>
        </div>
        <div class="card-body">
        <?php if ($attachments): ?>
          <ul class="list-unstyled">
            <?php foreach ($attachments as $a): ?>
              <li>
                <i class="fas fa-paperclip"></i>
                <a href="<?= $a['filepath'] ?>" target="_blank"><?= htmlspecialchars($a['filename']) ?></a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-muted"><i class="fas fa-exclamation-circle"></i> No attachments.</p>
        <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <!-- Items and Attachments -->
  <div class="row mb-3">
    <!-- Terms and conditions -->
    <div class="col mb-3">
      <div class="card h-100 shadow-sm">
        <div class="card-header bg-light">
          <h5 class="mb-0">
            <i class="fa-solid fa-edit"></i> Terms and Conditions
          </h5>
        </div>
        <div class="card-body">
          <textarea name="tandc" class="form-control bg-body-secondary" placeholder="Terms and conditions" rows="1" readonly><?= $tandc[0]['content'] ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <form method="post" action="submit_quote.php" enctype="multipart/form-data" class="border p-3 rounded bg-light mb-4 shadow-sm">
      <input type="hidden" name="request_id" value="<?= $request_id ?>">
      <h5 class="mt-4 mb-3"><i class="fas fa-id-badge"></i> Vendor Info</h5>
      <div class="row g-3">
        <!-- Name -->
        <div class="col-12 col-md-4">
          <input class="form-control" name="name" placeholder="Your Name" required>
        </div>

        <!-- Company -->
        <div class="col-12 col-md-4">
          <input class="form-control" name="company" placeholder="Company Name" required>
        </div>

        <!-- NTN -->
        <div class="col-12 col-md-4">
          <input class="form-control" name="ntn" placeholder="NTN" required>
        </div>

        <!-- Phone -->
        <div class="col-12 col-md-6">
          <input class="form-control"
                name="phone"
                type="tel"
                placeholder="Phone +92... or 03..."
                pattern="^\+?\d{10,15}$"
                title="Enter a valid phone number with only digits, optionally starting with +"
                required>
        </div>

        <!-- Email -->
        <div class="col-12 col-md-6">
          <input class="form-control"
                name="email"
                type="email"
                placeholder="Email"
                title="Enter a valid email address (e.g. user@example.com)"
                pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                required>
        </div>
      </div>

      <h5 class="mt-4 mb-3"><i class="fas fa-boxes"></i> Quotation Items</h5>
      <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle table-striped">
          <thead class="table-dark">
            <tr>
            <th><i class="fas fa-box"></i> Item/description with specifications</th>
            <th><i class="fas fa-sort-numeric-up-alt"></i> Quantity/unit of measure</th>
            <th><i class="fas fa-dollar-sign"></i> Unit Price</th>
            <th><i class="fas fa-calculator"></i> Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $index => $item): ?>
            <tr>
              <td style="min-width: 120px;">
              <?= htmlspecialchars($item['item_name']) ?>
              <input type="hidden" name="request_item_id[]" value="<?= $item['id'] ?>">
              </td>
              <td style="min-width: 120px;"><?= htmlspecialchars($item['quantity']) ?></td>
              <td style="min-width: 120px;">
              <input type="number"
                  name="unit_price[]"
                  class="form-control form-control-sm"
                  step="0.01"
                  min="0.01"
                  placeholder="Unit Price"
                  required
                  oninput="calculateRowTotal(this)">
              </td>
              <td class="total-cell">0.00</td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
            <th colspan="3" class="text-end">
              <i class="fas fa-coins"></i> Total&nbsp;
            </th>
            <th id="grand-total">0.00</th>
            </tr>
          </tfoot>
        </table>
      </div>

      <h5 class="mt-2 mb-3"><i class="fas fa-comment-alt"></i> Message / Notes</h5>
      <textarea class="form-control mb-3" name="message" rows="4" placeholder="Optional message..."></textarea>

      <h5 class="mb-2"><i class="fas fa-paperclip"></i> Attachments (optional): </h5><h6><i class="fas fa-exclamation-circle"></i> Only pdf, jpg, jpeg, png, docx and doc files format allowed having max size of 1 MB and 5 MB in total for multiple files.</h6>
      <input type="file" name="attachments[]" class="form-control mb-3" multiple>

      <!-- CAPTCHA Section -->
      <div id="captchaWrapper" class="my-3"></div>

      <!-- Submit Button -->
      <button type="submit" class="btn btn-primary theme_bg_color theme_border_color">
        <i class="fas fa-paper-plane"></i> Submit Quotation
      </button>
    </form>
  </div>

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
              <div class='alert alert-warning'><i class='fas fa-exclamation-circle'></i> CAPTCHA verification failed.</div>
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