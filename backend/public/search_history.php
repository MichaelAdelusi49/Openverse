<?php
session_start();
require_once '../config/config.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// Fetch search history with username from the users table
$query = "
    SELECT sh.history_id, sh.search_query, sh.media_type, sh.created_at, 
           u.username 
    FROM search_history sh
    JOIN users u ON sh.user_id = u.user_id
    ORDER BY sh.created_at DESC
";
$result = $mysqli->query($query);
$searchHistory = $result->fetch_all(MYSQLI_ASSOC);
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search History</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../models/sidebar.php'; ?>

    <div class="container mt-4">
        <h2>Search History</h2>

        <!-- Search History Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Search Query</th>
                    <th>Media Type</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($searchHistory as $index => $entry): ?>
                    <tr>
                        <td><?= $index + 1; ?></td>
                        <td><?= htmlspecialchars($entry['username']); ?></td>
                        <td><?= htmlspecialchars($entry['search_query']); ?></td>
                        <td><?= htmlspecialchars($entry['media_type']); ?></td>
                        <td><?= $entry['created_at']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
