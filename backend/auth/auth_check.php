<?php
session_start();

class AdminSession {
    private $adminId;
    private $dbConnection;

    public function __construct($dbConfig) {
        $this->dbConnection = new Database($dbConfig);
    }

    // Check if the admin is logged in
    public function isAdminLoggedIn() {
        if (!isset($_SESSION['admin_id'])) {
            header("Location: ../auth/admin_login.php");
            exit();
        }

        $this->adminId = $_SESSION['admin_id'];
    }

    // Fetch the logged-in admin's details
    public function getAdminDetails() {
        $query = "SELECT username, email FROM admin WHERE id = ?";
        $params = [$this->adminId];
        $result = $this->dbConnection->query($query, $params, "i");

        return $result->fetch_assoc();
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

// Create an instance of AdminSession
$adminSession = new AdminSession($dbConfig);

// Ensure the admin is logged in
$adminSession->isAdminLoggedIn();

// Fetch admin details
$adminDetails = $adminSession->getAdminDetails();

$admin_username = $adminDetails['username'];
$admin_email = $adminDetails['email'];
?>
