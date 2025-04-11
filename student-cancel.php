<?php
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Check if all required parameters are present
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?error=invalid_reservation");
    exit();
}

require 'db_connect.php';

$reservation_id = $_GET['id'];
$student_id = $_SESSION['student_id'];

// Prepare and execute the update query
$stmt = $conn->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ? AND student_id = ? AND status = 'reserved'");
$stmt->bind_param("is", $reservation_id, $student_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        header("Location: index.php?success=reservation_cancelled");
    } else {
        header("Location: index.php?error=no_reservation_found");
    }
} else {
    header("Location: index.php?error=cancellation_failed");
}

$stmt->close();
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
        <a href="layout.php" class="back-link">Back to Layout</a>
    </div>
</body>
</html> 