<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

// Include database connection
require_once 'db_connect.php';
$db = $mysqli ?? $conn;

// Validate input
if (!isset($_POST['table_number'], $_POST['chairs'], $_POST['date'], $_POST['time'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit();
}

$table_number = intval($_POST['table_number']);
$chairs = intval($_POST['chairs']);
$date = $_POST['date'];
$time = $_POST['time'];
$student_id = $_SESSION['student_id'];

// Validate values
if ($table_number <= 0 || $chairs <= 0 || $chairs > 4) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid table number or chairs']);
    exit();
}

// Combine date and time
$reservation_time = date('Y-m-d H:i:s', strtotime("$date $time"));

// Check if the reservation time is valid (not in the past)
if (strtotime($reservation_time) < time()) {
    echo json_encode(['status' => 'error', 'message' => 'Cannot make reservations for past times']);
    exit();
}

// Check if table is already fully reserved for this time slot
$checkQuery = "SELECT SUM(chairs_used) as total_chairs_used 
              FROM reservations 
              WHERE table_number = ? 
              AND status = 'reserved'
              AND ? BETWEEN reservation_time AND DATE_ADD(reservation_time, INTERVAL 60 MINUTE)";

$stmt = $db->prepare($checkQuery);
$stmt->bind_param("is", $table_number, $reservation_time);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_chairs_used = $row['total_chairs_used'] ?? 0;

// Calculate available chairs
$available_chairs = 4 - $total_chairs_used;

if ($available_chairs < $chairs) {
    echo json_encode(['status' => 'error', 'message' => 'Not enough chairs available. Only ' . $available_chairs . ' chairs left at this table.']);
    exit();
}

// Check if student already has a reservation at this time
$checkStudentQuery = "SELECT * FROM reservations 
                     WHERE student_id = ? 
                     AND status = 'reserved'
                     AND ? BETWEEN reservation_time AND DATE_ADD(reservation_time, INTERVAL 60 MINUTE)";

$stmt = $db->prepare($checkStudentQuery);
$stmt->bind_param("ss", $student_id, $reservation_time);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'You already have a reservation at this time']);
    exit();
}

try {
    // Insert the reservation
    $stmt = $db->prepare("INSERT INTO reservations (student_id, table_number, chairs_used, reservation_time, status) VALUES (?, ?, ?, ?, 'reserved')");
    $stmt->bind_param("siis", $student_id, $table_number, $chairs, $reservation_time);

    if ($stmt->execute()) {
        // Store reservation in session for confirmation
        $_SESSION['latest_reservation'] = [
            'student_id' => $student_id,
            'table_number' => $table_number,
            'chairs_used' => $chairs,
            'reservation_time' => $reservation_time,
            'status' => 'reserved'
        ];
        echo json_encode(['status' => 'success']);
    } else {
        throw new Exception('Failed to insert reservation');
    }
} catch (Exception $e) {
    error_log("Reservation Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to create reservation. Please try again.']);
}

$db->close();
?>
