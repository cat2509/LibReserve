<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_email'])) {
    header("Location: admin-login.php");
    exit();
}

// Check if reservation ID is provided
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header("Location: admin-scanner.php?error=invalid_id");
    exit();
}

try {
    // Prepare and execute the update query
    $stmt = $conn->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement");
    }
    
    $stmt->bind_param("i", $_POST['id']);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to cancel reservation");
    }
    
    if ($stmt->affected_rows > 0) {
        // Successfully cancelled
        header("Location: admin-scanner.php?success=cancelled");
    } else {
        // No reservation found with that ID
        header("Location: admin-scanner.php?error=no_reservation");
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Reservation cancellation error: " . $e->getMessage());
    header("Location: admin-scanner.php?error=cancellation_failed");
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
exit();
?> 