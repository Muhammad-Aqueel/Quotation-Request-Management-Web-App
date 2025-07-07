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
    #export_btn{
      color: #0dcaf0;
    }
    #export_btn:hover{
      color: white;
    }
  </style>
</head>
<body>
    <?php
        $zip = new ZipArchive();
        $name = "quotation-app_" . date("Ymd_His") . ".zip";

        if ($zip->open($name, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            exit("Cannot create ZIP file.");
        }

        // Exclude files/folders
        $exclude = [
            'uploads',           // runtime data
            'libs',              // external library folder
            'config.php',        // local credentials
            'zip_me_first.php',  // build script
            '.git',              // version control
            '__MACOSX'           // system folders
        ];

        // Add files recursively
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(__DIR__, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relative = str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $filePath);
            // Skip excluded
            foreach ($exclude as $skip) {
                if (stripos($relative, $skip) === 0) {
                    if((stripos($relative, '.htaccess') !== 8) && (stripos($relative, 'README.md') !== 5)){
                        continue 2;
                    }
                }
            }

            $zip->addFile($filePath, $relative);
        }

        $zip->close();
        echo '<div class="row m-auto"><div class="col-sm-4 mx-auto mt-5 p-4 shadow-sm rounded alert alert-info text-center" style="padding:20px;font-family:sans-serif;">
                <i class="fas fa-check-circle"></i> Package created: <strong>'.$name.'</strong><br><br>
                <a href="'.$name.'"><button id="export_btn" class="btn btn-dark py-2" type="button"><i class="fas fa-file-archive"></i> Download ZIP</button></a>
                <br><a href="index.php" class="btn btn-secondary btn-sm mt-3 position-relative" style="left: -41%;top: 7%;"><i class="fas fa-arrow-left"></i> Back</a>
                </div></div>';
    ?>
    <!-- Bootstrap JS (optional for interactivity) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>