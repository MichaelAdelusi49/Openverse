<?php
session_start();
require_once '../config/config.php';
require_once '../controllers/error_handler.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$mysqli = new mysqli("db", "root", "root", "openverse");
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}
if (isset($_SESSION['user_id'])) {
    $stmt = $mysqli->prepare("SELECT username FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($username);
    $stmt->fetch();
    $stmt->close();
}


// Get current user's saved items
$user_id = $_SESSION['user_id'];
$stmt = $mysqli->prepare("SELECT saved_id, media_id, media_type, media_reference, created_at FROM saved_items WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$savedItems = [];
while ($row = $result->fetch_assoc()) {
    $savedItems[] = $row;
}
$stmt->close();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Saved Items - Media Search</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .media-card {
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 20px;
        }
        .media-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .fixed-img {
            width: 100%;
            height: 200px; /* Fixed height */
            object-fit: cover; /* Crop images while maintaining aspect ratio */
        }
    </style>
</head>
<body>
    <?php include '../models/navbar.php'; ?>
    <div class="container mt-4">
        <h2>Saved Items</h2>
        <?php if (empty($savedItems)): ?>
            <p>You have not saved any items yet.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($savedItems as $item): ?>
                    <div class="col-md-4 mb-4" id="saved-item-<?= htmlspecialchars($item['media_id']) ?>">
                        <div class="card media-card">
                            <?php if ($item['media_type'] === 'images'): ?>
                                <img src="<?= htmlspecialchars($item['media_reference']) ?>" class="card-img-top fixed-img" alt="Saved Image">
                            <?php else: ?>
                                <div class="card-body">
                                    <audio controls class="w-100">
                                        <source src="<?= htmlspecialchars($item['media_reference']) ?>" type="audio/mpeg">
                                        Your browser does not support the audio element.
                                    </audio>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <p class="card-text"><strong>Media ID:</strong> <?= htmlspecialchars($item['media_id']) ?></p>
                                <p class="card-text"><small class="text-muted">Saved on <?= htmlspecialchars($item['created_at']) ?></small></p>
                                <a href="<?= htmlspecialchars($item['media_reference']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                                <button class="btn btn-sm btn-outline-danger unsave-media" data-id="<?= htmlspecialchars($item['media_id']) ?>">
                                    <i class="fas fa-trash"></i> Unsave
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        $(".unsave-media").click(function() {
            const button = $(this);
            const itemId = button.data("id");

            $.ajax({
                url: "../controllers/delete_saved_item.php",
                type: "POST",
                data: { id: itemId },
                dataType: "json", // Expect JSON response
                success: function(response) {
                    if (response.success) {
                        $("#saved-item-" + itemId).fadeOut(300, function() { $(this).remove(); });
                    } else {
                        alert("Error: " + response.error);
                    }
                },
                error: function(xhr) {
                    alert("Error unsaving item.");
                }
            });
        });
    });
    </script>
</body>
</html>
