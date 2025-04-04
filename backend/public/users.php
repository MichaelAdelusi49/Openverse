<?php
session_start();
require_once '../config/config.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// Handle User Creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_user'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    if (!empty($username) && !empty($email) && !empty($password)) {
        $stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);
        if ($stmt->execute()) {
            $_SESSION['success'] = "User created successfully!";
        } else {
            $_SESSION['error'] = "Failed to create user.";
        }
        $stmt->close();
    }
    header("Location: users.php");
    exit();
}

// Handle User Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    if (!empty($user_id) && !empty($username) && !empty($email)) {
        $stmt = $mysqli->prepare("UPDATE users SET username=?, email=? WHERE id=?");
        $stmt->bind_param("ssi", $username, $email, $user_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "User updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update user.";
        }
        $stmt->close();
    }
    header("Location: users.php");
    exit();
}

// Handle User Deletion
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];

    $stmt = $mysqli->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "User deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete user.";
    }
    $stmt->close();
    header("Location: users.php");
    exit();
}

// Fetch All Users
$result = $mysqli->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $result->fetch_all(MYSQLI_ASSOC);
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../models/sidebar.php'; ?>

    <div class="container mt-4">
        <h2>Manage Users</h2>

        <!-- Display Alerts -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Add User Form -->
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addUserModal">Add New User</button>

        <!-- Users Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['user_id']; ?></td>
                        <td><?= htmlspecialchars($user['username']); ?></td>
                        <td><?= htmlspecialchars($user['email']); ?></td>
                        <td><?= $user['created_at']; ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-btn"
                                    data-id="<?= $user['user_id']; ?>"
                                    data-username="<?= htmlspecialchars($user['username']); ?>"
                                    data-email="<?= htmlspecialchars($user['email']); ?>"
                                    data-toggle="modal" data-target="#editUserModal">Edit</button>
                            <a href="users.php?delete=<?= $user['user_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal">
        <div class="modal-dialog">
            <form method="POST" action="users.php">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add User</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
                        <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
                        <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="create_user" class="btn btn-primary">Add User</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal">
        <div class="modal-dialog">
            <form method="POST" action="users.php">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit User</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="edit-user-id">
                        <input type="text" name="username" id="edit-username" class="form-control mb-2" required>
                        <input type="email" name="email" id="edit-email" class="form-control mb-2" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="update_user" class="btn btn-success">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(".edit-btn").click(function () {
            $("#edit-user-id").val($(this).data("id"));
            $("#edit-username").val($(this).data("username"));
            $("#edit-email").val($(this).data("email"));
        });
    </script>
</body>
</html>
