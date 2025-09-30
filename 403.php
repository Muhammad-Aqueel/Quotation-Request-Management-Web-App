<?php
    // Set the HTTP response code to 403 
    http_response_code(403);
    include_once 'baseurl.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #000; }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const theme = localStorage.getItem('preferred-theme');
            if (theme === 'light') {
                document.body.style.backgroundColor = '#f1f5f9';
            } else {
                document.body.style.backgroundColor = '#000'; // dark or undefined
            }
        });
    </script>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="container mx-auto px-4 py-16 text-center min-h-screen flex flex-col justify-center items-center">
        <h1 class="text-6xl font-extrabold text-red-600 mb-4">403</h1>
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Access Denied</h2>
        <p class="text-gray-600 mb-8 max-w-lg">
            You do not have the necessary permissions to view this page.
            Please check your account role or contact the administrator if you believe this is an error.
        </p>
        <a href="<?= $baseUrl; ?>" class="bg-sky-600 text-white px-6 py-3 rounded shadow-md hover:bg-sky-700 transition duration-300">
            Go to Homepage
        </a>
    </div>
</body>
</html>