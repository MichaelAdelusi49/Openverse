<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    exit();
}

$mysqli = new mysqli("db", "root", "root", "openverse");
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

$stmt = $mysqli->prepare("SELECT history_id, search_query FROM search_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$response = "";
while ($row = $result->fetch_assoc()) {
    $response .= '<li class="list-group-item" data-query="' . htmlspecialchars($row['search_query']) . '">';
    $response .= '<span>' . htmlspecialchars($row['search_query']) . '</span>';
    $response .= '<button class="delete-history btn btn-sm" data-history-id="' . $row['history_id'] . '"><i class="fas fa-times"></i></button>';
    $response .= '</li>';
}

$stmt->close();
$mysqli->close();
echo $response;
?>
