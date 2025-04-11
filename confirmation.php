<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Include PHPMailer
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// For QR code, we'll use a simple approach without the Endroid library
// since it's causing issues
function generateQRCode($data) {
    $size = 200;
    $url = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($data);
    return $url;
}

// Get reservation details from session
$reservation = $_SESSION['latest_reservation'] ?? null;
if (!$reservation) {
    header("Location: layout.php");
    exit();
}

// Get server IP address
$serverIP = getServerIP();

// For hotspot connection, hardcode the IP if needed
// You can comment this out after your presentation if needed
$serverIP = "192.168.160.206"; // Use your current mobile hotspot IP

// Create the data for QR code
$bookingData = [
    'student_id' => $_SESSION['student_id'],
    'table_number' => $reservation['table_number'],
    'reservation_time' => $reservation['reservation_time'],
    'chairs_used' => $reservation['chairs_used']
];

// Create the URL that will be encoded in the QR code
$qrUrl = "http://{$serverIP}{$APP_PATH}/qr-result.php?id=" . urlencode($bookingData['student_id']) . 
         "&table=" . urlencode($bookingData['table_number']) . 
         "&time=" . urlencode($bookingData['reservation_time']) . 
         "&chairs=" . urlencode($bookingData['chairs_used']);

// Generate QR code using QR Server API - make it larger and with better error correction
$qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($qrUrl) . "&size=300x300&margin=10&ecc=H";

// Send email with PHPMailer
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->Username = "sanaaakadam@gmail.com";
    $mail->Password = "lpqt keke dptb ikpb"; // App Password

    // Recipients
    $mail->setFrom('sanaaakadam@gmail.com', 'Library Booking System');
    $mail->addAddress($_SESSION['email'], $_SESSION['student_id']);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Library Table Booking Confirmation';
    
    // Email body with QR code
    $mail->Body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #dc3545;'>Booking Confirmation</h2>
        <p>Dear {$_SESSION['student_id']},</p>
        <p>Your library table booking has been confirmed. Here are your booking details:</p>
        
        <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
            <p><strong>Table Number:</strong> {$reservation['table_number']}</p>
            <p><strong>Date & Time:</strong> " . date('F j, Y h:i A', strtotime($reservation['reservation_time'])) . "</p>
            <p><strong>Number of Chairs:</strong> {$reservation['chairs_used']}</p>
            <p><strong>Duration:</strong> 60 minutes</p>
        </div>
        
        <p>Please show this QR code at the library entrance:</p>
        <img src='{$qrImageUrl}' alt='Booking QR Code' style='max-width: 300px; margin: 20px 0;'>
        
        <p style='color: #666; font-size: 14px;'>Note: Your reservation is valid for 1 hour from the selected time.</p>
        <p style='color: #666; font-size: 14px;'>To view your booking details online, visit: {$qrUrl}</p>
        
        <p>Thank you for using our library booking system!</p>
    </div>";

    $mail->send();
    $emailSent = true;
} catch (Exception $e) {
    $emailSent = false;
    $emailError = $mail->ErrorInfo;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Confirmation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .confirmation-container {
            width: 100%;
            max-width: 600px;
            margin: 20px;
            padding: 30px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            color: #2e7d32;
        }
        .header i {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        .header p {
            color: #666;
            margin: 0;
        }
        .confirmation-details {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
        }
        .detail-item {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .detail-item:last-child {
            margin-bottom: 0;
        }
        .detail-item i {
            width: 24px;
            color: #b91a1a;
            margin-right: 15px;
            font-size: 18px;
        }
        .detail-item .label {
            font-weight: bold;
            margin-right: 15px;
            min-width: 120px;
            color: #444;
        }
        .detail-item .value {
            color: #333;
        }
        .qr-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background-color: #fff;
            border-radius: 12px;
            border: 2px dashed #ddd;
        }
        .qr-section img {
            display: block;
            width: 200px;
            height: 200px;
            margin: 15px auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .qr-section .qr-title {
            color: #333;
            font-size: 16px;
            margin-bottom: 15px;
            font-weight: 500;
        }
        .qr-section .qr-subtitle {
            color: #666;
            font-size: 14px;
            margin-top: 15px;
        }
        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        .buttons form {
            flex: 1;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn i {
            margin-right: 8px;
            font-size: 16px;
        }
        .btn-primary {
            background-color: #b91a1a;
            color: white;
        }
        .btn-danger {
            background-color: #e12727;
            color: white;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .btn-primary:hover {
            background-color: #a31717;
        }
        .btn-danger:hover {
            background-color: #c71f1f;
        }
        @media (max-width: 768px) {
            .buttons {
                flex-direction: column;
            }
            .buttons form {
                width: 100%;
            }
            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
        .chair-info {
            background-color: #fff3e0;
            padding: 20px;
            border-radius: 12px;
            margin-top: 25px;
            border-left: 4px solid #ff9800;
        }
        .chair-info i {
            color: #ff9800;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="header">
            <i class="fas fa-check-circle"></i>
            <h1>Reservation Confirmed!</h1>
            <p>Your table has been successfully reserved.</p>
        </div>

        <div class="confirmation-details">
            <div class="detail-item">
                <i class="fas fa-user"></i>
                <span class="label">Student ID:</span>
                <span class="value"><?php echo htmlspecialchars($_SESSION['student_id']); ?></span>
            </div>

            <div class="detail-item">
                <i class="fas fa-table"></i>
                <span class="label">Table Number:</span>
                <span class="value"><?php echo htmlspecialchars($reservation['table_number']); ?></span>
            </div>

            <div class="detail-item">
                <i class="fas fa-chair"></i>
                <span class="label">Chairs:</span>
                <span class="value"><?php echo htmlspecialchars($reservation['chairs_used']); ?></span>
            </div>

            <div class="detail-item">
                <i class="fas fa-clock"></i>
                <span class="label">Time:</span>
                <span class="value"><?php echo date('h:i A', strtotime($reservation['reservation_time'])); ?></span>
            </div>

            <div class="detail-item">
                <i class="fas fa-calendar"></i>
                <span class="label">Date:</span>
                <span class="value"><?php echo date('F j, Y', strtotime($reservation['reservation_time'])); ?></span>
            </div>

            <?php if ($reservation['chairs_used'] < 4): ?>
            <div class="chair-info">
                <i class="fas fa-info-circle"></i>
                <strong>Note:</strong> You have reserved <?php echo $reservation['chairs_used']; ?> chair(s). 
                The remaining chairs may be reserved by other students.
            </div>
            <?php endif; ?>
        </div>

        <div class="qr-section">
            <div class="qr-title">Scan this QR code at the library entrance</div>
            <img src="<?php echo $qrImageUrl; ?>" alt="QR Code for your booking" id="qrcode">
            <div class="qr-subtitle">📱 When scanned, this code will display your booking details</div>
            <div style="margin-top: 15px; font-size: 14px; text-align: center;">
                <p>If scanning doesn't work, use this link:</p>
                <a href="<?php echo htmlspecialchars($qrUrl); ?>" target="_blank" style="word-break: break-all; color: #b91a1a; text-decoration: underline;">Open Booking Details</a>
            </div>
        </div>

        <div class="buttons">
            <a href="layout.php" class="btn btn-primary">
                <i class="fas fa-plus"></i>Make Another Reservation
            </a>
            <form action="cancel_reservation.php" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                <input type="hidden" name="reservation_id" value="<?php echo isset($reservation['id']) ? htmlspecialchars((string)$reservation['id'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-times-circle"></i>Cancel Reservation
                </button>
            </form>
        </div>
    </div>

    <!-- Add QR code error handling -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const qrImage = document.querySelector('.qr-section img');
        
        // Check if QR code loaded successfully
        qrImage.addEventListener('load', function() {
            this.style.display = 'block';
            console.log('QR code loaded successfully');
        });
        
        // Handle QR code loading error
        qrImage.addEventListener('error', function() {
            console.error('QR code failed to load');
            // Try Google Charts as backup QR service
            this.src = 'https://chart.googleapis.com/chart?cht=qr&chs=200x200&chld=H|1&chl=' + 
                      encodeURIComponent('<?php echo addslashes($qrUrl); ?>');
            
            // Add a backup error message
            const qrSection = document.querySelector('.qr-section');
            const errorMsg = document.createElement('div');
            errorMsg.style.color = '#721c24';
            errorMsg.style.backgroundColor = '#f8d7da';
            errorMsg.style.padding = '10px';
            errorMsg.style.borderRadius = '5px';
            errorMsg.style.marginTop = '10px';
            errorMsg.innerHTML = 'If the QR code is not visible, please use the direct link below.';
            qrSection.insertBefore(errorMsg, qrImage.nextSibling);
        });
        
        // Add a way to manually refresh the page if needed
        window.refreshPage = function() {
            location.reload();
        };
    });
    </script>
</body>
</html>
