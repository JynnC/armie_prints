<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       
define('DB_PASS', '');           
define('DB_NAME', 'ap_db');

function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die('<div style="font-family:sans-serif;padding:20px;color:#e84040;">
                <h3>Database Connection Failed</h3>
                <p>' . htmlspecialchars($conn->connect_error) . '</p>
                <p>Make sure XAMPP MySQL is running and <code>ap_db</code> exists.</p>
            </div>');
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

// Session helper
function requireLogin() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
}

function isLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['user_id']);
}