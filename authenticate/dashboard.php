<?php
  include 'includes/header.php';
?>
<style>
  .card-stats .col-icon {
    width: 65px;
    padding-left: 0;
    padding-right: 0;
    margin-left: 15px;
  }
  .icon-big i.fas {
      font-size: .8em;
    }
  .icon-big {
      min-height: 65px;
  }
</style>
<h2 class="mb-4"><i class="fas fa-user-shield"></i> Welcome, <?= htmlspecialchars($_SESSION['user_username']) ?></h2>

<div class="row g-3">
  <div class="col-md-4">
    <a href="requests.php" class="btn btn-outline-primary w-100">
      <i class="fas fa-clipboard-list"></i> Manage Requests
    </a>
  </div>
  <div class="col-md-4">
    <a href="quotations.php" class="btn btn-outline-success w-100">
      <i class="fas fa-file-invoice-dollar"></i> View Quotations
    </a>
  </div>
  <div class="col-md-4">
    <a href="profile.php" class="btn btn-outline-info w-100">
      <i class="fas fa-user-cog"></i> My Profile
    </a>
  </div>
</div>

<br>

<div class="row g-3">
  <div class="col-sm-6 col-md-4">
    <div class="shadow card card-stats">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon">
            <div class="bg-danger icon-big rounded d-flex justify-content-center align-items-center w-100 h-100 display-6">
              <i class="fas fa-clock text-white"></i>
            </div>
          </div>
          <div class="col col-stats ms-3 ms-sm-0">
            <div class="numbers">
              <h6 class="card-category">Pending Quotations</h6>
              <h4 id="pending_quotes" class="card-title"></h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-4">
    <div class="shadow card card-stats">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon">
            <div class="bg-success icon-big rounded d-flex justify-content-center align-items-center w-100 h-100 display-6">
              <i class="fas fa-calendar-day text-white"></i>
            </div>
          </div>
          <div class="col col-stats ms-3 ms-sm-0">
            <div class="numbers">
              <h6 class="card-category">Quotations Today</h6>
              <h4 id="submitted_today" class="card-title"></h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-4">
    <div class="shadow card card-stats">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon">
            <div class="bg-info icon-big rounded d-flex justify-content-center align-items-center w-100 h-100 display-6">
              <i class="fas fa-calendar-alt text-white"></i>
            </div>
          </div>
          <div class="col col-stats ms-3 ms-sm-0">
            <div class="numbers">
              <h6 class="card-category">Quotations This Month</h6>
              <h4 id="month_total" class="card-title"></h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-4">
    <div class="shadow card card-stats">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon">
            <div class="bg-primary icon-big rounded d-flex justify-content-center align-items-center w-100 h-100 display-6">
              <i class="fas fa-list text-white"></i>
            </div>
          </div>
          <div class="col col-stats ms-3 ms-sm-0">
            <div class="numbers">
              <h6 class="card-category">Total Requests</h6>
              <h4 id="total_requests" class="card-title"></h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-4">
    <div class="shadow card card-stats">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon">
            <div class="bg-warning icon-big rounded d-flex justify-content-center align-items-center w-100 h-100 display-6">
              <i class="fas fa-users text-white"></i>
            </div>
          </div>
          <div class="col col-stats ms-3 ms-sm-0">
            <div class="numbers">
              <h6 class="card-category">Active Vendors</h6>
              <h4 id="vendor_count" class="card-title"></h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-4">
    <div class="shadow card card-stats">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon">
            <div class="bg-secondary icon-big rounded d-flex justify-content-center align-items-center w-100 h-100 display-6">
              <i class="fas fa-users-slash text-white"></i>
            </div>
          </div>
          <div class="col col-stats ms-3 ms-sm-0">
            <div class="numbers">
              <h6 class="card-category">Inactive Vendors</h6>
              <h4 id="inactive_vendor_count" class="card-title"></h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
