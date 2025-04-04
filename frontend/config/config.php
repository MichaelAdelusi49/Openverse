<?php
// Database Configurationdefine('DB_HOST', 'db'); // Use the Docker service name instead of 'localhost'
define('DB_HOST', 'db'); 
define('DB_USER', 'root');
define('DB_PASS', 'root'); // Use the password set in docker-compose.yml
define('DB_NAME', 'openverse');



// Openverse API Credentials
define('OPENVERSE_CLIENT_ID', '2bW4FUJtPEx0YSOZDzVMXOsiIKRbrkCtjIuJbfMC');
define('OPENVERSE_CLIENT_SECRET', 'nBr4BJMSfHGmrSj3BoB8uyyu6EmbpH7GSGMhWVb3eyhS6zU5VA7VvdyJLNWxj2AX9BA45cVlF9O3zgTp3wiPKKg05oMpKd6OThbMUjJkmfNHylNuXx7ZMTXlF8DTpiCl');

// Application Settings
define('RESULTS_PER_PAGE', 12);
define('MAX_TRENDING_ITEMS', 6);
define('API_RATE_LIMIT', 50); // Requests per minute

// Security Settings
define('CSRF_TOKEN_NAME', 'csrf_token');

// Start session only if it is not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


// Database Connection
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    error_log("Database connection failed: " . $mysqli->connect_error);
    die("Database connection error. Please try again later.");
}

// Generate CSRF Token
if (empty($_SESSION[CSRF_TOKEN_NAME])) {
    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
}
?>