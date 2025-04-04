<?php
require_once '../config/config.php';
require_once '../classes/Database.php'; // Ensure you have a Database class
require_once '../controllers/error_handler.php';

class Dashboard {
    protected $db;

    public function __construct() {
        // Check if admin is logged in
        if (!isset($_SESSION['admin_id'])) {
            header("Location: ../auth/login.php");
            exit();
        }
        // Initialize database connection
        $this->db = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }

    public function getUserCount() {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM users");
        if ($result) {
            $row = $result->fetch_assoc();
            $result->free();
            return $row['total'] ?? 0;
        }
        return 0;
    }

    public function getAdminCount() {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM admin");
        if ($result) {
            $row = $result->fetch_assoc();
            $result->free();
            return $row['total'] ?? 0;
        }
        return 0;
    }

    public function getSavedItemsCount() {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM saved_items");
        if ($result) {
            $row = $result->fetch_assoc();
            $result->free();
            return $row['total'] ?? 0;
        }
        return 0;
    }

    public function __destruct() {
        $this->db->close();
    }
}
