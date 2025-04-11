<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Check if reservation ID is provided
if (!isset($_POST['reservation_id'])) {
    header("Location: my_reservations.php?error=Invalid request");
    exit();
}

$student_id = $_SESSION['student_id'];
$reservation_id = $_POST['reservation_id'];

// Verify that the reservation belongs to the current user and is still valid for cancellation
$sql = "SELECT * FROM reservations 
        WHERE id = ? AND student_id = ? 
        AND status = 'reserved' 
        AND (
            reservation_time > NOW() OR 
            NOW() BETWEEN DATE_SUB(reservation_time, INTERVAL 15 MINUTE) AND DATE_ADD(reservation_time, INTERVAL 60 MINUTE)
        )";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $reservation_id, $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: my_reservations.php?error=Invalid reservation or cannot be cancelled");
    exit();
}

// Update the reservation status to cancelled
$update_sql = "UPDATE reservations SET status = 'cancelled' WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("s", $reservation_id);

if ($update_stmt->execute()) {
    header("Location: my_reservations.php?success=cancelled");
} else {
    header("Location: my_reservations.php?error=Failed to cancel reservation");
}

$stmt->close();
$update_stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Reservation</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .message-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message.warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #b91a1a;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .back-link:hover {
            background-color: #921515;
        }
    </style>
</head>
<body>
    <div class="message-card">
        <div class="message <?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
        <?php if (isset($_SESSION['admin_id'])): ?>
            <a href="admin-scanner.php" class="back-link">Back to Scanner</a>
        <?php else: ?>
            <a href="layout.php" class="back-link">Back to Layout</a>
        <?php endif; ?>
    </div>
</body>
</html>
