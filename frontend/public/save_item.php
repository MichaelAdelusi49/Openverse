<?php
session_start();
require_once '../config/config.php';
require_once '../controllers/error_handler.php';

header('Content-Type: application/json');

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method', 405);
    }

    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION[CSRF_TOKEN_NAME]) || 
        !hash_equals($_SESSION[CSRF_TOKEN_NAME], $_POST['csrf_token'])) {
        throw new Exception('CSRF token validation failed', 403);
    }

    // Validate user session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Authentication required', 401);
    }

    // Retrieve POST data
    $media_id = trim($_POST['media_id'] ?? '');
    $media_type = trim($_POST['media_type'] ?? '');
    $media_reference = trim($_POST['media_reference'] ?? '');
    $user_id = $_SESSION['user_id'];

    // Check if required fields are present
    if (empty($media_id) || empty($media_type) || empty($media_reference) || 
        !in_array($media_type, ['images', 'audio'])) {
        throw new Exception('Invalid media parameters', 400);
    }

    // Database connection
    $mysqli = new mysqli("db", "root", "root", "openverse");
    if ($mysqli->connect_error) {
        throw new Exception("Database connection failed: " . $mysqli->connect_error, 500);
    }

    // Check if the item already exists for this user
    $stmt = $mysqli->prepare("SELECT saved_id FROM saved_items WHERE user_id = ? AND media_id = ?");
    $stmt->bind_param("is", $user_id, $media_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        throw new Exception('Media already saved', 409);
    }
    $stmt->close();

    // Insert new saved item
    $stmt = $mysqli->prepare("INSERT INTO saved_items (user_id, media_id, media_type, media_reference) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $media_id, $media_type, $media_reference);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to save media item', 500);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Media saved successfully',
        'saved_id' => $stmt->insert_id
    ]);

    $stmt->close();
    $mysqli->close();

} catch (Exception $e) {
    error_log("Save Item Error [{$e->getCode()}]: {$e->getMessage()} - User: " . ($_SESSION['user_id'] ?? 'guest'));
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
}
?>
