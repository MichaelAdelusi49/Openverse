<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['history_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$mysqli = new mysqli("db", "root", "root", "openverse");
if ($mysqli->connect_error) {
    die(json_encode(['success' => false, 'error' => 'DB Connection failed']));
}

$stmt = $mysqli->prepare("DELETE FROM search_history WHERE history_id = ? AND user_id = ?");
$stmt->bind_param("ii", $_POST['history_id'], $_SESSION['user_id']);
$stmt->execute();

echo json_encode(['success' => ($stmt->affected_rows > 0)]);
$stmt->close();
$mysqli->close();
?>
