<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login page if not authenticated
    header("Location: ../auth/admin_login.php");
    exit();
}

// Optional: Fetch the logged-in admin's details for display
require_once '../config/config.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

$admin_id = $_SESSION['admin_id'];
$stmt = $mysqli->prepare("SELECT username, email FROM admin WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($admin_username, $admin_email);
$stmt->fetch();
$stmt->close();
$mysqli->close();
?>
