<?php
session_start();
require_once '../config/config.php';
require_once '../controllers/error_handler.php';

// Database Connection
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Handle CREATE & UPDATE Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    if ($action == "create") {
        // Insert new admin
        $stmt = $mysqli->prepare("INSERT INTO admin (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashedPassword);
    } elseif ($action == "update") {
        // Update existing admin
        $admin_id = $_POST['admin_id'];
        $stmt = $mysqli->prepare("UPDATE admin SET username = ?, email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $email, $hashedPassword, $admin_id);
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = "Admin " . ($action == "create" ? "added" : "updated") . " successfully.";
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: admins.php");
    exit();
}

// Handle DELETE Admin
if (isset($_GET['delete'])) {
    $admin_id = $_GET['delete'];
    $stmt = $mysqli->prepare("DELETE FROM admin WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Admin deleted successfully.";
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: admins.php");
    exit();
}

// Fetch Admin Users
$result = $mysqli->query("SELECT id, username, email, created_at FROM admin ORDER BY created_at DESC");
$admins = $result->fetch_all(MYSQLI_ASSOC);
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>
<body>
    <?php include '../models/sidebar.php'; ?>

    <div class="container">
        <h2 class="mt-4">Admin Management</h2>

        <!-- Notifications -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Add Admin Form -->
        <div class="card p-3 mt-3">
            <h4>Add New Admin</h4>
            <form action="admins.php" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Admin</button>
            </form>
        </div>

        <!-- Admins List -->
        <div class="card p-3 mt-4">
            <h4>Existing Admins</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $admin): ?>
                        <tr>
                            <td><?= htmlspecialchars($admin['username']); ?></td>
                            <td><?= htmlspecialchars($admin['email']); ?></td>
                            <td><?= htmlspecialchars($admin['created_at']); ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm edit-btn"
                                    data-id="<?= $admin['id']; ?>"
                                    data-username="<?= $admin['username']; ?>"
                                    data-email="<?= $admin['email']; ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <a href="admins.php?delete=<?= $admin['id']; ?>" class="btn btn-danger btn-sm"
                                   onclick="return confirm('Are you sure?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Edit Admin Modal -->
        <div id="editModal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Admin</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form action="admins.php" method="POST">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="admin_id" id="edit-admin-id">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" id="edit-username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" id="edit-email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="password" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-success">Update Admin</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('edit-admin-id').value = button.dataset.id;
                document.getElementById('edit-username').value = button.dataset.username;
                document.getElementById('edit-email').value = button.dataset.email;
                $('#editModal').modal('show');
            });
        });
    </script>

</body>
</html>
