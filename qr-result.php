<?php
session_start();

// Get data from URL parameters
$student_id = isset($_GET['id']) ? $_GET['id'] : '';
$table_number = isset($_GET['table']) ? $_GET['table'] : '';
$reservation_time = isset($_GET['time']) ? $_GET['time'] : '';
$chairs_used = isset($_GET['chairs']) ? $_GET['chairs'] : '';

// Debug to file
file_put_contents('qr_debug.log', date('Y-m-d H:i:s') . " - Accessed with parameters: " . print_r($_GET, true) . "\n", FILE_APPEND);

// Validate required fields
if (empty($student_id) || empty($table_number) || empty($reservation_time) || empty($chairs_used)) {
    $error = true;
    file_put_contents('qr_debug.log', date('Y-m-d H:i:s') . " - Error: Missing parameters\n", FILE_APPEND);
} else {
    // Create booking data array for easier access
    $bookingData = [
        'student_id' => $student_id,
        'table_number' => $table_number,
        'reservation_time' => $reservation_time,
        'chairs_used' => $chairs_used
    ];
}

// Format the date and time if data is valid
if (!isset($error)) {
    try {
        $dateTime = new DateTime($reservation_time);
        $formattedDateTime = $dateTime->format('F j, Y g:i A');
        
        // Calculate if the booking is upcoming, active, or expired
        $now = new DateTime();
        $bookingStart = new DateTime($reservation_time);
        $bookingEnd = clone $bookingStart;
        $bookingEnd->modify('+60 minutes');
        
        if ($now < $bookingStart) {
            $status = 'upcoming';
            $statusText = 'Upcoming Booking';
        } elseif ($now >= $bookingStart && $now <= $bookingEnd) {
            $status = 'active';
            $statusText = 'Booking Active';
        } else {
            $status = 'expired';
            $statusText = 'Booking Expired';
        }
    } catch (Exception $e) {
        $error = true;
        file_put_contents('qr_debug.log', date('Y-m-d H:i:s') . " - Error processing date: " . $e->getMessage() . "\n", FILE_APPEND);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Table Booking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            max-width: 500px;
            margin: 0 auto;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1 {
            color: #b91a1a;
            text-align: center;
            margin-top: 0;
        }
        h2 {
            color: #666;
            text-align: center;
            font-size: 18px;
            margin-top: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        tr {
            border-bottom: 1px solid #eee;
        }
        tr:last-child {
            border-bottom: none;
        }
        td {
            padding: 12px 5px;
        }
        .label {
            font-weight: bold;
            width: 40%;
        }
        .value {
            text-align: right;
        }
        .status {
            text-align: center;
            padding: 15px;
            margin-top: 20px;
            border-radius: 8px;
            font-weight: bold;
        }
        .upcoming {
            background-color: #fff3cd;
            color: #856404;
        }
        .active {
            background-color: #d4edda;
            color: #155724;
        }
        .expired {
            background-color: #f8d7da;
            color: #721c24;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Library Table Booking</h1>
        <h2>Booking Details</h2>
        
        <?php if (!isset($error)): ?>
            <table>
                <tr>
                    <td class="label">Student ID</td>
                    <td class="value"><?php echo htmlspecialchars($bookingData['student_id']); ?></td>
                </tr>
                <tr>
                    <td class="label">Table Number</td>
                    <td class="value"><?php echo htmlspecialchars($bookingData['table_number']); ?></td>
                </tr>
                <tr>
                    <td class="label">Date & Time</td>
                    <td class="value"><?php echo htmlspecialchars($formattedDateTime); ?></td>
                </tr>
                <tr>
                    <td class="label">Duration</td>
                    <td class="value">60 minutes</td>
                </tr>
                <tr>
                    <td class="label">Chairs</td>
                    <td class="value"><?php echo htmlspecialchars($bookingData['chairs_used']); ?></td>
                </tr>
            </table>
            
            <div class="status <?php echo $status; ?>">
                <?php echo $statusText; ?>
            </div>
        <?php else: ?>
            <div class="error">
                Invalid QR code or booking data
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 