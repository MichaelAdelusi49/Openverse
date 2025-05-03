<?php
session_start();
require_once '../config/config.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// Fetch saved items with username from the users table
$query = "
    SELECT si.saved_id, si.media_id, si.media_type, si.media_reference, si.created_at, 
           u.username 
    FROM saved_items si
    JOIN users u ON si.user_id = u.user_id
    ORDER BY si.created_at DESC
";
$result = $mysqli->query($query);
$savedItems = $result->fetch_all(MYSQLI_ASSOC);
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Items</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .media-thumbnail {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <?php include '../models/sidebar.php'; ?>

    <div class="container mt-4">
        <h2>Saved Items</h2>

        <!-- Saved Items Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Media Type</th>
                    <th>Media</th>
                    <th>Media ID</th>
                    <th>Saved On</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($savedItems as $index => $item): ?>
                    <tr>
                        <td><?= $index + 1; ?></td>
                        <td><?= htmlspecialchars($item['username']); ?></td>
                        <td><?= htmlspecialchars($item['media_type']); ?></td>
                        <td>
                            <?php if ($item['media_type'] === 'images'): ?>
                                <img src="<?= htmlspecialchars($item['media_reference']); ?>" class="media-thumbnail" alt="Saved Image">
                            <?php else: ?>
                                <audio controls>
                                    <source src="<?= htmlspecialchars($item['media_reference']); ?>" type="audio/mpeg">
                                    Your browser does not support the audio element.
                                </audio>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($item['media_id']); ?></td>
                        <td><?= $item['created_at']; ?></td>
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
