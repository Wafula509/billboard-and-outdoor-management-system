<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // Change to your database username
define('DB_PASS', '');          // Change to your database password
define('DB_NAME', 'ad_space_manager');

// Error reporting (only for development)
error_reporting(E_ALL);
ini_set('display_errors', 0);    // Don't show errors to users in production
ini_set('log_errors', 1);        // Log errors to a file

try {
    // Create database connection using mysqli with error handling
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4 for full Unicode support
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    // Log the error securely (in production, don't expose details to users)
    error_log($e->getMessage());
    
    // Display a generic error message to the user
    die("We're experiencing technical difficulties. Please try again later.");
}
?>