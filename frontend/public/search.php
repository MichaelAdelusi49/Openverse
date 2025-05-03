<?php
session_start(); // Start session for user and CSRF token

require_once '../config/config.php';
require_once '../controllers/error_handler.php';

// Optionally generate CSRF token if not set
if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
}

// Define get_openverse_token() if not already defined
if (!function_exists('get_openverse_token')) {
    function get_openverse_token() {
        static $token = null;
        if ($token === null) {
            $data = [
                'client_id'     => OPENVERSE_CLIENT_ID,
                'client_secret' => OPENVERSE_CLIENT_SECRET,
                'grant_type'    => 'client_credentials'
            ];
            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data)
                ]
            ];
            $context = stream_context_create($options);
            $response = file_get_contents('https://api.openverse.org/v1/auth_tokens/token/', false, $context);
            if ($response === false) {
                throw new Exception('Failed to get Openverse access token');
            }
            $token_data = json_decode($response, true);
            
            $token = $token_data['access_token'] ?? '';
        }
        return $token;
    }
}

// Get raw input parameters and validate
$query      = filter_input(INPUT_GET, 'query', FILTER_DEFAULT);
$mediaType  = strtolower(filter_input(INPUT_GET, 'mediaType', FILTER_DEFAULT) ?? 'images');
$mediaType  = in_array($mediaType, ['images', 'audio']) ? $mediaType : 'images';
$page       = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$creator    = filter_input(INPUT_GET, 'creator', FILTER_DEFAULT);
$start_date = filter_input(INPUT_GET, 'start_date', FILTER_DEFAULT);
$end_date   = filter_input(INPUT_GET, 'end_date', FILTER_DEFAULT);

$results = [];
$result_count = 0;
$error = null;

try {
    if (!empty($query)) {
        // Get Openverse token
        $token = get_openverse_token();

        // Build API URL
        $url = "https://api.openverse.org/v1/{$mediaType}/";
        $params = [
            'q'            => $query,
            'page'         => $page,
            'page_size'    => RESULTS_PER_PAGE,
            'license_type' => 'commercial'
        ];
        if (!empty($creator))      $params['creator']        = $creator;
        if (!empty($start_date))     $params['created_after']  = $start_date;
        if (!empty($end_date))       $params['created_before'] = $end_date;
        
        $api_url = $url . '?' . http_build_query($params);

        // Make API request with authentication header
        $options = [
            'http' => [
                'header'  => "Authorization: Bearer $token\r\n",
                'timeout' => 15
            ]
        ];
        $context = stream_context_create($options);
        $response = file_get_contents($api_url, false, $context);
        if ($response !== false) {
            $data = json_decode($response, true);
            $results = $data['results'] ?? [];
            $result_count = $data['result_count'] ?? 0;
        } else {
            throw new Exception('Failed to connect to Openverse API');
        }
        
        // Log search query if user is logged in
        if (isset($_SESSION['user_id'])) {
            $mysqli = new mysqli("db", "root", "root", "openverse");
            if ($mysqli->connect_error) {
                throw new Exception("DB Connection failed: " . $mysqli->connect_error);
            }
            $search_log = $query;
            if (!empty($creator))  $search_log .= " | Creator: $creator";
            if (!empty($start_date)) $search_log .= " | From: $start_date";
            if (!empty($end_date))   $search_log .= " | To: $end_date";
            $stmt = $mysqli->prepare("INSERT INTO search_history (user_id, search_query, media_type) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $_SESSION['user_id'], $search_log, $mediaType);
            $stmt->execute();
            $stmt->close();
            $mysqli->close();
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Get current user's username (for navbar display)
$mysqli = new mysqli("db", "root", "root", "openverse");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$username = "";
if (isset($_SESSION['user_id'])) {
    $stmt = $mysqli->prepare("SELECT username FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($username);
    $stmt->fetch();
    $stmt->close();
}
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Media Search</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
         .media-card {
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
         }
         .media-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
         }
         .license-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
         }
         .search-form {
            max-width: 800px;
            margin: 0 auto 20px;
         }
         .search-form .form-control {
             margin-right: 5px;
         }
         .card-img-top {
    width: 100%;
    height: 200px; /* Set your desired height */
    object-fit: cover;
}

    </style>
</head>
<body>
    <?php include '../controllers/loading_spinner.php'; ?>
    <?php include '../models/navbar.php'; ?>

    <div class="container mt-4">
        <!-- Advanced Search Form -->
        <form class="form-inline search-form" action="search.php" method="GET">
            <div class="input-group w-100">
                <select class="custom-select" name="mediaType">
                    <option value="images" <?= $mediaType === 'images' ? 'selected' : '' ?>>Images</option>
                    <option value="audio" <?= $mediaType === 'audio' ? 'selected' : '' ?>>Audio</option>
                </select>
                <input type="search" name="query" class="form-control" placeholder="Search media..." value="<?= htmlspecialchars($query ?? '') ?>" required>
                <input type="text" name="creator" class="form-control" placeholder="Creator (optional)" value="<?= htmlspecialchars($creator ?? '') ?>">
                <input type="date" name="start_date" class="form-control" placeholder="Start Date" value="<?= htmlspecialchars($start_date ?? '') ?>">
                <input type="date" name="end_date" class="form-control" placeholder="End Date" value="<?= htmlspecialchars($end_date ?? '') ?>">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>

        <!-- Error Display -->
        <?php if ($error): ?>
            <div class="alert alert-danger">Error: <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <h2>Search Results for "<?= htmlspecialchars($query) ?>"</h2>
        
        <div class="row mt-4">
            <?php if (empty($query)): ?>
                <p>Please enter a search query above to see results.</p>
            <?php elseif (empty($results)): ?>
                <p>No results found for "<?= htmlspecialchars($query) ?>"</p>
            <?php else: ?>
                <?php foreach ($results as $result): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card media-card">
                            <?php if ($mediaType === 'images'): ?>
                                <span class="badge license-badge"><?= htmlspecialchars($result['license'] ?? 'CC0') ?></span>
                                <img src="<?= htmlspecialchars($result['thumbnail']) ?>" class="card-img-top" alt="<?= htmlspecialchars($result['title']) ?>">
                            <?php else: ?>
                                <div class="card-body">
                                    <span class="badge license-badge"><?= htmlspecialchars($result['license'] ?? 'CC0') ?></span>
                                    <audio controls class="w-100">
                                        <source src="<?= htmlspecialchars($result['url']) ?>" type="audio/mpeg">
                                        Your browser does not support the audio element.
                                    </audio>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5><?= htmlspecialchars($result['title']) ?></h5>
                                <p class="text-muted"><?= htmlspecialchars($result['creator'] ?? 'Unknown creator') ?></p>
                                <div class="d-flex justify-content-between">
                                    <a href="<?= htmlspecialchars($result['foreign_landing_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        View Source
                                    </a>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <?php if ($mediaType === 'images'): ?>
                                            <button class="btn btn-sm btn-outline-primary save-media"
                                                data-media-id="<?= htmlspecialchars($result['id']) ?>"
                                                data-media-type="<?= htmlspecialchars($mediaType) ?>"
                                                data-media-reference="<?= htmlspecialchars($result['thumbnail']) ?>">
                                                <i class="fas fa-bookmark"></i> Save
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-primary save-media"
                                                data-media-id="<?= htmlspecialchars($result['id']) ?>"
                                                data-media-type="<?= htmlspecialchars($mediaType) ?>"
                                                data-media-reference="<?= htmlspecialchars($result['url']) ?>">
                                                <i class="fas fa-bookmark"></i> Save
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
<?php 
$total_pages = ceil($result_count / RESULTS_PER_PAGE);
if ($total_pages > 1): 
?>
    <nav aria-label="Search pagination">
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" 
                       href="?<?= http_build_query([
                           'query' => $query,
                           'mediaType' => $mediaType,
                           'page' => $page - 1,
                           'creator' => $creator,
                           'start_date' => $start_date,
                           'end_date' => $end_date
                       ]) ?>">
                        Previous
                    </a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" 
                       href="?<?= http_build_query([
                           'query' => $query,
                           'mediaType' => $mediaType,
                           'page' => $i,
                           'creator' => $creator,
                           'start_date' => $start_date,
                           'end_date' => $end_date
                       ]) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" 
                       href="?<?= http_build_query([
                           'query' => $query,
                           'mediaType' => $mediaType,
                           'page' => $page + 1,
                           'creator' => $creator,
                           'start_date' => $start_date,
                           'end_date' => $end_date
                       ]) ?>">
                        Next
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>
    </div><!-- container -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        // Save media via AJAX
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
                    csrf_token: '<?= $_SESSION[CSRF_TOKEN_NAME] ?? "" ?>'
                },
                success: function(response) {
                    if (response.success) {
                        button.toggleClass('btn-outline-primary btn-success');
                        button.html('<i class="fas fa-check"></i> Saved');
                    } else {
                        alert('Error: ' + response.error);
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.error || 'Failed to save item'));
                }
            });
        });
    });
    </script>
</body>
</html>
