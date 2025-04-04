<?php
session_start();
require_once '../config/config.php';
require_once '../classes/Database.php';
require_once '../classes/OpenverseAPIClient.php';
require_once '../classes/User.php';
require_once '../controllers/error_handler.php';

// Initialize Database
$db = new Database("db", "root", "root", "openverse");

// Get Current User Data
$username = '';
$searchHistory = [];
if (isset($_SESSION['user_id'])) {
    $username = User::getUsername($_SESSION['user_id'], $db);
    $searchHistory = User::getSearchHistory($_SESSION['user_id'], $db);
}

// Fetch Trending Content
$trending_images = OpenverseAPIClient::getTrendingContent('images');
$trending_audio = OpenverseAPIClient::getTrendingContent('audio');

// Close Database
$db->close();

// HTML REMAINS UNCHANGED FROM YOUR ORIGINAL CODE
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Openverse Media Search</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .search-form {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }
        .card-img-top {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        /* Dropdown for search history */
        #search-history-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #ccc;
            z-index: 1000;
            max-height: 250px;
            overflow-y: auto;
        }
        #search-history-dropdown ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        #search-history-dropdown li {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        #search-history-dropdown li:hover {
            background-color: #f8f9fa;
        }
        .delete-history {
            color: #dc3545;
            background: none;
            border: none;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <?php include '../models/navbar.php'; ?>

    <div class="container mt-4">
        <!-- Search Form and Dropdown Container -->
        <div class="search-form">
            <form class="form-inline" action="search.php" method="GET">
                <div class="input-group w-100">
                    <select class="custom-select" name="mediaType">
                        <option value="images">Images</option>
                        <option value="audio">Audio</option>
                    </select>
                    <input type="search" name="query" id="search-input" class="form-control" placeholder="Search media..." required>
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
            <!-- Search History Dropdown (Loaded via AJAX) -->
            <?php if (isset($_SESSION['user_id'])): ?>
            <div id="search-history-dropdown" class="shadow">
                <ul class="list-group" id="search-history-list">
                    <!-- AJAX will load search history here -->
                </ul>
            </div>
            <?php endif; ?>
        </div>

        <!-- Trending Content Sections -->
        <div class="mt-5">
            <section class="mb-5">
                <h3 class="mb-4">Trending Images</h3>
                <div class="row">
                    <?php foreach ($trending_images as $image): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card media-card">
                                <span class="badge license-badge"><?= $image['license'] ?? 'CC0' ?></span>
                                <img src="<?= htmlspecialchars($image['thumbnail']) ?>" class="card-img-top" alt="<?= htmlspecialchars($image['title']) ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($image['title']) ?></h5>
                                    <p class="card-text text-muted"><?= htmlspecialchars($image['creator'] ?? 'Unknown Creator') ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="<?= $image['url'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">View Source</a>
                                        <?php if (isset($_SESSION['user_id'])): ?>
                                            <button class="btn btn-sm btn-outline-secondary save-media"
                                                data-media-id="<?= $image['id'] ?>"
                                                data-media-type="images"
                                                data-media-reference="<?= $image['thumbnail'] ?>">
                                                <i class="fas fa-bookmark"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <section class="mb-5">
                <h3 class="mb-4">Trending Audio</h3>
                <div class="row">
                    <?php foreach ($trending_audio as $audio): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card media-card">
                                <div class="card-body">
                                    <span class="badge license-badge"><?= $audio['license'] ?? 'CC0' ?></span>
                                    <h5 class="card-title"><?= htmlspecialchars($audio['title']) ?></h5>
                                    <p class="card-text text-muted"><?= htmlspecialchars($audio['creator'] ?? 'Unknown Creator') ?></p>
                                    <audio controls class="w-100 mb-3">
                                        <source src="<?= $audio['url'] ?>" type="audio/mpeg">
                                    </audio>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="<?= $audio['url'] ?>" download class="btn btn-sm btn-success">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                        <?php if (isset($_SESSION['user_id'])): ?>
                                            <button class="btn btn-sm btn-outline-secondary save-media"
        data-media-id="<?= $audio['id'] ?>"
        data-media-type="audio"
        data-media-reference="<?= $audio['url'] ?>">
        <i class="fas fa-bookmark"></i>
    </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function(){
        // Load search history via AJAX
        function loadSearchHistory() {
            $.ajax({
                url: '../controllers/fetch_search_history.php',
                method: 'GET',
                success: function(data) {
                    $('#search-history-list').html(data);
                    if ($.trim(data) !== "") {
                        $("#search-history-dropdown").slideDown(200);
                    }
                },
                error: function(xhr) {
                    console.error("Error fetching search history:", xhr.responseText);
                }
            });
        }

        $("#search-input").focus(function(){
            loadSearchHistory();
        });

        $(document).on("click", function(event) {
            if (!$(event.target).closest("#search-input, #search-history-dropdown").length) {
                $("#search-history-dropdown").slideUp(200);
            }
        });

        $(document).on("click", "#search-history-list li", function(e){
            if ($(e.target).closest(".delete-history").length) return;
            $("#search-input").val($(this).data("query"));
            $("#search-history-dropdown").slideUp(200);
        });

        $(document).on("click", ".delete-history", function(e){
            e.stopPropagation();
            var button = $(this);
            var historyId = button.data("history-id");

            $.ajax({
                url: '../controllers/delete_search_history.php',
                method: 'POST',
                data: { history_id: historyId },
                success: function(response){
                    button.closest("li").fadeOut(300, function(){ $(this).remove(); });
                },
                error: function(xhr){
                    console.error("Error deleting entry:", xhr.responseText);
                }
            });
        });
    });

    $('.save-media').click(function() {
        const button = $(this);
        const mediaId = button.data('media-id');
        const mediaType = button.data('media-type');
        const mediaReference = button.data('media-reference');

        $.ajax({
            url: 'save_item.php',
            method: 'POST',
            data: {
                media_id: mediaId,
                media_type: mediaType,
                media_reference: mediaReference,
                csrf_token: '<?= $_SESSION[CSRF_TOKEN_NAME] ?>'
            },
            success: function(response) {
                if (response.success) {
                    button.toggleClass('btn-outline-secondary btn-primary');
                }
            },
            error: function(xhr) {
                alert('Error saving media: ' + (xhr.responseJSON?.error || 'Unknown error'));
            }
        });
    });
    </script>
</body>
</html>
