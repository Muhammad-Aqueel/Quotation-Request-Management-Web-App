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

<link rel="stylesheet" href="./assets/css/quote.css">

<!-- Page Header -->
<div class="page-header">
  <div class="row align-items-center position-relative">
    <div class="col-md-8">
      <h1 class="h2 fw-bold mb-2">
        <i class="fas fa-file-invoice-dollar me-3"></i>Submit Quotation
      </h1>
      <p class="mb-0 opacity-90">Provide your quotation for the requested items</p>
    </div>
    <div class="col-md-4 text-md-end">
      <div class="badge bg-body fs-6 px-3 py-2" style="color: var(--primary-color);">
        Request ID # <?= $request_id ?>
      </div>
    </div>
  </div>
</div>

<!-- Request Information -->
<div class="row g-4 mb-4">
  <!-- Request Details -->
  <div class="col-lg-6">
    <div class="info-card">
      <h5><i class="fas fa-clipboard-list"></i> Request Details</h5>
      
      <div class="info-item">
        <div class="info-icon">
          <i class="fas fa-tag"></i>
        </div>
        <div class="info-content">
          <div class="info-label">Title</div>
          <p class="info-value fw-semibold"><?= htmlspecialchars($request['title']) ?></p>
        </div>
      </div>

      <div class="info-item">
        <div class="info-icon">
          <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="info-content">
          <div class="info-label">Event Date</div>
          <p class="info-value"><?= htmlspecialchars($request['event_date']) ?></p>
        </div>
      </div>

      <div class="info-item">
        <div class="info-icon">
          <i class="fas fa-layer-group"></i>
        </div>
        <div class="info-content">
          <div class="info-label">Category</div>
          <p class="info-value"><?= htmlspecialchars($request['category_name']) ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Event Description & Attachments -->
  <div class="col-lg-6">
    <div class="info-card">
      <h5><i class="fas fa-info-circle"></i> Event Information</h5>
      
      <div class="info-item mb-3">
        <div class="info-icon">
          <i class="fas fa-align-left"></i>
        </div>
        <div class="info-content">
          <div class="info-label">Description</div>
          <p class="info-value"><?= htmlspecialchars($request['description']) ?></p>
        </div>
      </div>

      <div class="info-item">
        <div class="info-icon">
          <i class="fas fa-folder-open"></i>
        </div>
        <div class="info-content">
          <div class="info-label">Reference Attachments</div>
          <?php if ($attachments): ?>
            <ul class="attachment-list">
              <?php foreach ($attachments as $attachment): ?>
                <li class="attachment-item">
                  <div class="attachment-icon">
                    <i class="fas fa-paperclip"></i>
                  </div>
                  <a href="<?= htmlspecialchars($attachment['filepath']) ?>" target="_blank" class="text-decoration-none">
                    <?= htmlspecialchars($attachment['filename']) ?>
                  </a>
                  <a href="<?= htmlspecialchars($attachment['filepath']) ?>" target="_blank" class="text-decoration-none ms-auto">
                    <i class="fas fa-external-link-alt text-muted" style="font-size: 0.8rem;"></i>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="info-value text-muted"><i class="fas fa-exclamation-circle"></i> No attachments available</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Terms and Conditions -->
<div class="terms-card">
  <div class="terms-header">
    <h5 class="mb-0 fw-bold ">
      <i class="fas fa-edit me-2"></i> Terms and Conditions
    </h5>
  </div>
  <div class="terms-body">
    <textarea name="tandc" class="terms-textarea form-control" rows="1" readonly><?= $tandc[0]['content'] ?></textarea>
  </div>
</div>

<!-- Quotation Form -->
<div class="quote-form">
  <form method="post" action="submit_quote.php" enctype="multipart/form-data">
    <input type="hidden" name="request_id" value="<?= $request_id ?>">
    
    <!-- Vendor Information Section -->
    <h5 class="section-title">
      <i class="fas fa-id-badge"></i> Vendor Information
    </h5>
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <label class="form-label fw-semibold">Full Name *</label>
        <input class="form-control" name="name" placeholder="Enter your full name" required>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Company Name *</label>
        <input class="form-control" name="company" placeholder="Enter company name" required>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">NTN Number *</label>
        <input class="form-control" name="ntn" placeholder="Enter NTN number" required>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Phone Number *</label>
        <input class="form-control" name="phone" type="tel" placeholder="e.g., +92 300 1234567" 
               pattern="^\+?\d{10,15}$" 
               title="Enter a valid phone number with only digits, optionally starting with +" 
               required>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Email Address *</label>
        <input class="form-control" name="email" type="email" placeholder="your.email@company.com"
               title="Enter a valid email address (e.g. user@example.com)"
               pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
               required>
      </div>
    </div>

    <!-- Quotation Items Section -->
    <h5 class="section-title">
      <i class="fas fa-boxes"></i> Quotation Items & Pricing
    </h5>
    <div class="modern-table-container">
      <div class="table-responsive">
        <table class="modern-table table">
          <thead>
            <tr>
              <th><i class="fas fa-box me-2"></i>Item Description & Specifications</th>
              <th><i class="fas fa-sort-numeric-up-alt me-2"></i>Quantity</th>
              <th><i class="fas fa-dollar-sign me-2"></i>Unit Price (Rs.)</th>
              <th><i class="fas fa-calculator me-2"></i>Line Total (Rs.)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $index => $item): ?>
            <tr>
              <td style="min-width: 200px;">
                <div class="d-flex align-items-start gap-3">
                  <div class="flex-shrink-0">
                    <span class="badge theme_bg_color rounded-circle" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                      <?= $index + 1 ?>
                    </span>
                  </div>
                  <div>
                    <div class="fw-medium"><?= htmlspecialchars($item['item_name']) ?></div>
                    <input type="hidden" name="request_item_id[]" value="<?= $item['id'] ?>">
                  </div>
                </div>
              </td>
              <td style="min-width: 100px;text-align: center;">
                <span class="badge bg-secondary fs-6 px-3 py-2"><?= htmlspecialchars($item['quantity']) ?></span>
              </td>
              <td style="min-width: 140px;">
                <input type="number" name="unit_price[]" class="price-input form-control-sm w-100"
                       step="0.01" min="0.01" placeholder="0.00" required
                       oninput="calculateRowTotal(this)">
              </td>
              <td class="total-cell" style="min-width: 120px;">Rs. 0.00</td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="3" class="text-end">
                <i class="fas fa-coins me-2"></i>Grand Total
              </th>
              <th id="grand-total" class="text-body fs-5">Rs. 0.00</th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Message Section -->
    <h5 class="section-title">
      <i class="fas fa-comment-alt"></i> Additional Message / Notes
    </h5>
    <div class="mb-4">
      <textarea class="form-control" name="message" rows="1" 
                placeholder="Enter any additional notes, terms, or special conditions for this quotation..."></textarea>
    </div>

    <!-- File Upload Section -->
    <h5 class="section-title">
      <i class="fas fa-paperclip"></i> Supporting Documents
    </h5>
    <div class="file-upload">
      <label class="file-upload-label">
        <i class="fas fa-cloud-upload-alt me-2"></i>Upload Supporting Files (Optional)
      </label>
      <div class="file-upload-info">
        <i class="fas fa-info-circle me-1"></i>
        Accepted formats: PDF, JPG, JPEG, PNG, DOCX, DOC | Max 1MB per file, 5MB total
      </div>
      <input type="file" name="attachments[]" class="form-control" multiple 
             accept=".pdf,.jpg,.jpeg,.png,.docx,.doc">
    </div>

    <!-- CAPTCHA Section -->
    <div id="captchaWrapper"></div>

    <!-- Submit Button -->
    <div class="text-center">
      <button type="submit" class="submit-btn">
        <i class="fas fa-paper-plane"></i> Submit Quotation
      </button>
    </div>
  </form>
</div>

<!-- Modal -->
<div id="msg-modal" class="modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title"><i class="fas fa-circle-info me-2"></i>Validation Error</h6>
        <button type="button" class="btn-close" aria-label="Close" onclick="close_modal()"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning mb-0">
          <i class="fas fa-exclamation-triangle me-2"></i>CAPTCHA verification failed. Please try again.
        </div>
      </div>
    </div>
  </div>
</div>
<div id="msg-backdrop" class="modal-backdrop fade d-none"></div>

<?php include 'includes/footer.php'; ?>

<script>
  // Price calculation function
  function calculateRowTotal(input) {
    const row = input.closest('tr');
    const quantityText = row.querySelector('td:nth-child(2) .badge').textContent;
    const quantity = parseFloat(quantityText) || 0;
    const unitPrice = parseFloat(input.value) || 0;
    const total = quantity * unitPrice;
    
    const totalCell = row.querySelector('.total-cell');
    totalCell.textContent = 'Rs. ' + total.toFixed(2);
    
    // Update grand total
    updateGrandTotal();
  }
  
  function updateGrandTotal() {
    const totalCells = document.querySelectorAll('.total-cell');
    let grandTotal = 0;
    
    totalCells.forEach(cell => {
      const value = cell.textContent.replace('Rs. ', '').replace(',', '');
      grandTotal += parseFloat(value) || 0;
    });
    
    document.getElementById('grand-total').textContent = 'Rs. ' + grandTotal.toFixed(2);
  }

  // Form validation
  document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("form");
    const msg_m = document.getElementById("msg-modal");
    const msg_b = document.getElementById("msg-backdrop");
    
    form.addEventListener("submit", function (e) {
      const input = document.getElementById("captchaInput");
      const actual = document.getElementById("captcha-value");

      if (input && actual && input.value.trim() !== actual.value.trim()) {
        e.preventDefault();
        msg_m.classList.add('d-block');
        msg_b.classList.add('show');
        msg_b.classList.remove('d-none');
        input.focus();
      }
    });

    // CAPTCHA loading
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
        <div class="captcha-container">
          <h5 class="section-title mb-3">
            <i class="fas fa-robot"></i> Security Verification
          </h5>
          <div class="row align-items-center g-3">
            <div class="col-auto">
              <div class="captcha-display">${captchaValue}</div>
            </div>
            <div class="col">
              <input id="captchaInput" type="text" name="captcha" class="form-control" 
                     placeholder="Enter CAPTCHA code as shown" maxlength="4" required>
            </div>
          </div>
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