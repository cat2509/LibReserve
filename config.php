<?php

// Server Configuration
function getServerIP() {
    // Get IP address automatically
    // $ip = $_SERVER['SERVER_ADDR'];
    
    // If the above doesn't work, you can manually set it here
    // Uncomment and update the line below with your current IP
    $ip = "192.168.0.103";
    
    return $ip;
}

// Database Configuration
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "library_booking";

// Application Settings
$APP_NAME = "Library Table Booking";
$APP_PATH = "/final"; // The folder name where your project is installed

// QR Code Settings
$QR_SIZE = "300x300";
$QR_ERROR_CORRECTION = "H";
$QR_MARGIN = "10";

// Create Connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set Character Encoding
$conn->set_charset("utf8mb4");

// Base URL
define("BASE_URL", "http://yourwebsite.com/");

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>