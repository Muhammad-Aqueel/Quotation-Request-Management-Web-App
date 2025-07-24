<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Menu</title>
  <!-- Font-awesome CSS CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Bootstrap CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f8f9fa;
    }
    .card-link {
      text-decoration: none;
    }
    .card {
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    }
    #export_btn:hover{
      color: #0dcaf0;
    }
  </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
  <div class="container text-center">
    <h1 class="mb-4">Choose a Role</h1>
    <div class="row g-4 justify-content-center">
      <div class="col-12 col-md-4">
        <a href="./authenticate/" class="card-link">
          <div class="card text-white bg-primary h-100 text-center">
            <div class="card-body d-flex align-items-center justify-content-center">
              <h2 class="card-title">Admin</h2>
            </div>
          </div>
        </a>
      </div>
      <div class="col-12 col-md-4">
        <a href="./vendor/" class="card-link">
          <div class="card text-white bg-success h-100 text-center">
            <div class="card-body d-flex align-items-center justify-content-center">
              <h2 class="card-title">Vendor</h2>
            </div>
          </div>
        </a>
      </div>
    </div>
  </div>
  <a href="zip_me_first.php"><button id="export_btn" class="position-fixed bottom-0 end-0 mb-3 me-3 btn btn-outline-dark py-2" type="button"><i class="fas fa-file-archive"></i> Export ZIP</button></a>
  <!-- Bootstrap JS (optional for interactivity) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
