<?php
// Database configuration
$servername = "sql12.freesqldatabase.com";
$username = "sql12773516";
$password = "RYvYlPZWEw";
$dbname = "sql12773516";

// Create connection with error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Create the main connection as $mysqli
    $mysqli = new mysqli($servername, $username, $password, $dbname);
    
    // Create $conn as a reference to $mysqli
    $conn = $mysqli;
    
    // Set charset to utf8mb4
    $mysqli->set_charset("utf8mb4");
    
    // Check connection
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }
    
    // Both $mysqli and $conn are now available to files that include this one
    
} catch (Exception $e) {
    // Log the error (you might want to use a proper logging system in production)
    error_log("Database Connection Error: " . $e->getMessage());
    
    // If this is not an AJAX request, show a user-friendly error
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
        include 'error_page.php';
        exit();
    } else {
        // For AJAX requests, return JSON error
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed. Please try again later.']);
        exit();
    }
}

// Function to get the database connection (can be used with either name)
function getConnection() {
    global $mysqli, $conn;
    return $mysqli ?? $conn;
}
