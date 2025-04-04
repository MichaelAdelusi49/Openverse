<?php
session_start();

class AdminLogin {
    private $username;
    private $password;
    private $errors = [];
    private $db;

    public function __construct($dbConfig) {
        $this->db = new Database($dbConfig);
    }

    // Handle form submission and authentication
    public function handleFormSubmission() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $this->username = trim($_POST['username'] ?? '');
            $this->password = $_POST['password'] ?? '';

            if (empty($this->username)) {
                $this->errors[] = "Username is required.";
            }

            if (empty($this->password)) {
                $this->errors[] = "Password is required.";
            }

            if (empty($this->errors)) {
                $this->authenticateUser();
            }
        }
    }

    // Authenticate the user by checking credentials
    private function authenticateUser() {
        $query = "SELECT id, password FROM admin WHERE username = ?";
        $params = [$this->username];
        $stmt = $this->db->query($query, $params, "s");
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($admin_id, $password_hash);
            $stmt->fetch();
            if (password_verify($this->password, $password_hash)) {
                $_SESSION['admin_id'] = $admin_id;
                $_SESSION['admin_username'] = $this->username;
                header("Location: ../public/index.php");
                exit();
            } else {
                $this->errors[] = "Invalid password.";
            }
        } else {
            $this->errors[] = "Invalid username.";
        }

        $stmt->close();
    }

    // Get errors if any
    public function getErrors() {
        return $this->errors;
    }

    // Get previously entered username (for form repopulation)
    public function getUsername() {
        return htmlspecialchars($this->username);
    }
}

class Database {
    private $connection;

    public function __construct($dbConfig) {
        $this->connection = new mysqli(
            $dbConfig['host'], 
            $dbConfig['user'], 
            $dbConfig['password'], 
            $dbConfig['database']
        );

        if ($this->connection->connect_error) {
            die("Database connection failed: " . $this->connection->connect_error);
        }
    }

    // Prepare and execute the query
    public function query($sql, $params = [], $types = "") {
        $stmt = $this->connection->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt;
    }

    // Close the connection
    public function close() {
        $this->connection->close();
    }
}

// Config for database connection (assuming you have this set up in your config file)
$dbConfig = [
    'host' => DB_HOST,
    'user' => DB_USER,
    'password' => DB_PASS,
    'database' => DB_NAME,
];

// Create an instance of AdminLogin
$adminLogin = new AdminLogin($dbConfig);
$adminLogin->handleFormSubmission();
$errors = $adminLogin->getErrors();
$username = $adminLogin->getUsername();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Admin Login</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" class="form-control" value="<?= $username ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
    <p class="mt-3">Don't have an admin account? <a href="register.php">Register here</a></p>
</div>
</body>
</html>
