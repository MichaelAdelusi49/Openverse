<?php
session_start();
require_once '../config/config.php';

header('Content-Type: application/json');

try {
    // Ensure the request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method', 405);
    }
    
    // Ensure user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Authentication required', 401);
    }
    
    // Get media_id from POST (using key "id" because that’s what the unsave button sends)
    $media_id = filter_input(INPUT_POST, 'id', FILTER_DEFAULT);
    if (!$media_id) {
        throw new Exception('Invalid media ID', 400);
    }
    
    // Database connection
    $mysqli = new mysqli("db", "root", "root", "openverse");
    if ($mysqli->connect_error) {
        throw new Exception("Database connection failed: " . $mysqli->connect_error, 500);
    }
    
    // Delete media item if it belongs to the user
    $user_id = $_SESSION['user_id'];
    $stmt = $mysqli->prepare("DELETE FROM saved_items WHERE media_id = ? AND user_id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error, 500);
    }
    // Bind media_id as a string and user_id as an integer
    $stmt->bind_param("si", $media_id, $user_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Media unsaved successfully"]);
    } else {
        throw new Exception("Failed to delete media item", 500);
    }
    
    $stmt->close();
    $mysqli->close();
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
