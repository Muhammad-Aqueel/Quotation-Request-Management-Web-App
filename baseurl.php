<?php
    // Dynamically determine BASE_URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $appBasePath = dirname($_SERVER['SCRIPT_NAME']);
    // $appBasePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
    $baseUrl = $protocol . '://' . $host . $appBasePath;