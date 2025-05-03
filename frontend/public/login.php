<?php
session_start();

// If already logged in, redirect to homepage
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Database connection
$mysqli = new mysqli("db", "root", "root", "openverse");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$errors = [];
$username = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize input
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        // Look up the user by username
        $stmt = $mysqli->prepare("SELECT user_id, password_hash FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($user_id, $password_hash);
            $stmt->fetch();
            // Verify the password
            if (password_verify($password, $password_hash)) {
                $_SESSION['user_id'] = $user_id;
                header("Location: index.php");
                exit();
            } else {
                $errors[] = "Invalid password.";
            }
        } else {
            $errors[] = "Invalid username.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - Media Search</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body {
      background: url('images/media-bg.png') repeat;
    }
    .form-container {
      max-width: 500px;
      margin: 50px auto;
      background-color: rgba(255,255,255,0.9);
      padding: 30px;
      border-radius: 5px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="form-container">
      <h2 class="text-center mb-4">Login</h2>
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
              <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      
      <form action="login.php" method="POST">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Login</button>
      </form>
      <p class="mt-3 text-center">
        Don't have an account? <a href="register.php">Register here</a>
      </p>
    </div>
  </div>
  
  <!-- Bootstrap JS and dependencies -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
