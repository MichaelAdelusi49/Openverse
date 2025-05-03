<?php
require_once '../config/config.php';

// Custom error handler
set_error_handler(function($severity, $message, $file, $line) {
    error_log("Error: $message in $file on line $line");
    
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
        if (file_exists('errors/500.php')) {
            include 'errors/500.php';
        } else {
            echo "<h1>500 Internal Server Error</h1><p>An error occurred.</p>";
        }
        exit;
    }
});

// Custom exception handler
set_exception_handler(function($e) {
    error_log("Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());

    if (php_sapi_name() !== 'cli') {
        $error_code = $e->getCode() ?: 500;
        http_response_code($error_code);
        
        $error_file = "errors/$error_code.php";
        if (file_exists($error_file)) {
            include $error_file;
        } else {
            echo "<h1>Error $error_code</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
        }
        exit;
    }
});

// Create error pages directory if not exists
if (!is_dir('errors')) {
    mkdir('errors', 0755, true);
}

// Generate common error pages
$common_errors = [400, 401, 403, 404, 429, 500];
foreach ($common_errors as $code) {
    $file = "errors/$code.php";
    if (!file_exists($file)) {
        file_put_contents($file, <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error $code</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #d9534f; }
        a { color: #0275d8; text-decoration: none; }
    </style>
</head>
<body>
    <h1>Error $code</h1>
    <p>An error occurred while processing your request.</p>
    <a href="/">Return Home</a>
</body>
</html>
HTML
        );
    }
}
?>
