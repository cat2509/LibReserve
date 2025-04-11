<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_email'])) {
    header("Location: admin-login.php");
    exit();
}

// Handle POST request for updating reservation status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'])) {
    $reservation_id = $_POST['reservation_id'];
    
    // Prepare and execute query to update status
    $stmt = $conn->prepare("UPDATE reservations SET status = 'scanned' WHERE id = ?");
    $stmt->bind_param("i", $reservation_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Reservation status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update reservation status']);
    }
    
    $stmt->close();
    exit();
}

// Initialize variables
$scanResult = null;
$scanError = null;
$reservationDetails = null;

// Process the scanned QR code data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if it's from the QR scanner or manual form
        if (isset($_POST['qr_data'])) {
            // Processing QR code data
            $qrData = json_decode($_POST['qr_data'], true);
            
            if (!$qrData || !isset($qrData['student_id']) || !isset($qrData['table_number']) || !isset($qrData['reservation_time'])) {
                throw new Exception("Invalid QR code format");
            }
            
            $studentId = $qrData['student_id'];
            $tableNumber = $qrData['table_number'];
            $reservationTime = $qrData['reservation_time'];
        } 
        elseif (isset($_POST['form_submitted'])) {
            // Processing manual form submission
            if (!isset($_POST['student_id']) || !isset($_POST['table_number']) || 
                !isset($_POST['reservation_date']) || !isset($_POST['reservation_time'])) {
                throw new Exception("Please fill all required fields");
            }
            
            $studentId = $_POST['student_id'];
            $tableNumber = $_POST['table_number'];
            
            // Combine date and time
            $reservationDate = $_POST['reservation_date'];
            $reservationTimeInput = $_POST['reservation_time'];
            $reservationTime = $reservationDate . ' ' . $reservationTimeInput . ':00';
        }
        else {
            throw new Exception("No valid input provided");
        }
        
        // Try to get reservation details - modified to work even if students table is not available
        try {
            // First attempt to join with students table
            $stmt = $conn->prepare("SELECT r.*, s.name as student_name, s.email 
                                FROM reservations r 
                                LEFT JOIN students s ON r.student_id = s.id 
                                WHERE r.student_id = ? 
                                AND r.table_number = ? 
                                AND r.reservation_time = ? 
                                AND r.status = 'reserved'");
            
            $stmt->bind_param("sis", $studentId, $tableNumber, $reservationTime);
            $stmt->execute();
            $result = $stmt->get_result();
        } catch (Exception $e) {
            // If that fails, try a simpler query without the join
            $stmt = $conn->prepare("SELECT * FROM reservations 
                                  WHERE student_id = ? 
                                  AND table_number = ? 
                                  AND reservation_time = ? 
                                  AND status = 'reserved'");
            
            $stmt->bind_param("sis", $studentId, $tableNumber, $reservationTime);
            $stmt->execute();
            $result = $stmt->get_result();
        }
        
        if ($result->num_rows === 0) {
            throw new Exception("Reservation not found or not valid");
        }
        
        $reservationDetails = $result->fetch_assoc();
        
        // Calculate if the reservation is still valid (not expired)
        $reservationTime = strtotime($reservationDetails['reservation_time']);
        $duration = intval($reservationDetails['duration']);
        $endTime = $reservationTime + ($duration * 60);
        $currentTime = time();
        
        if ($currentTime > $endTime) {
            // Update the reservation status to expired
            $updateStmt = $conn->prepare("UPDATE reservations SET status = 'expired' WHERE id = ?");
            $updateStmt->bind_param("i", $reservationDetails['id']);
            $updateStmt->execute();
            $updateStmt->close();
            
            throw new Exception("Reservation has expired");
        }
        
        // If we get here, the reservation is valid
        $scanResult = "Reservation is valid";
        
    } catch (Exception $e) {
        $scanError = $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin QR Scanner - Library Reservations</title>
    <!-- Favicon using Font Awesome -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📚</text></svg>">
    <!-- Include the jsQR library for QR code scanning -->
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #b91a1a;
            --secondary-color: #333;
            --light-gray: #f4f4f4;
            --medium-gray: #e9ecef;
            --border-color: #ddd;
            --success-color: #0f5132;
            --success-bg: #d1e7dd;
            --danger-color: #842029;
            --danger-bg: #f8d7da;
            --font-family: 'Arial', sans-serif;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 8px 16px rgba(0, 0, 0, 0.15);
            --border-radius: 10px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: var(--font-family);
            background-color: var(--light-gray);
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
            color: #444;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
        }

        header {
            background-color: var(--primary-color);
            color: white;
            padding: 0.8rem;
            text-align: center;
            box-shadow: var(--shadow-md);
            position: relative;
        }
        
        header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        header p {
            margin-top: 0.2rem;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .container {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            padding: 1rem;
            max-height: calc(100vh - 80px);
            overflow: hidden;
        }
        
        .left-panel, .right-panel {
            background: white;
            padding: 1rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            display: flex;
            flex-direction: column;
            max-height: 100%;
            overflow-y: auto;
        }

        .left-panel {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .right-panel {
            background: var(--medium-gray);
        }
        
        h2 {
            font-size: 1.4rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--light-gray);
        }
        
        .scanner-container {
            background-color: var(--medium-gray);
            padding: 1rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1rem;
        }
        
        #video-container {
            position: relative;
            width: 100%;
            max-width: 100%;
            aspect-ratio: 4/3;
            overflow: hidden;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
        }
        
        #qr-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border: 2px solid var(--border-color);
            border-radius: 8px;
        }
        
        .controls {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        button {
            padding: 0.6rem 1rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
            flex: 1;
        }
        
        button:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .manual-input {
            background-color: white;
            padding: 1rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
        }
        
        .form-group {
            margin-bottom: 0.8rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.8rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .form-control:hover {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(185, 26, 26, 0.1);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(185, 26, 26, 0.2);
        }
        
        .alert-success, .alert-danger {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-top: 1rem;
            transition: all 0.3s ease;
        }
        
        .alert-success:hover, .alert-danger:hover {
            transform: translateX(5px);
        }
        
        .reservation-details {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 1rem;
            margin-top: 1rem;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem;
            background-color: white;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .detail-row:hover {
            transform: translateX(5px);
            background-color: var(--light-gray);
            box-shadow: var(--shadow-sm);
        }
        
        .nav-links {
            margin-top: auto;
            padding-top: 1rem;
            text-align: center;
        }
        
        .nav-links a {
            display: inline-block;
            padding: 0.6rem 1.2rem;
            background-color: var(--medium-gray);
            color: var(--secondary-color);
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .nav-links a:hover {
            background-color: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light-gray);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }

        /* Responsive layout */
        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
                max-height: none;
                overflow: auto;
            }
            
            .left-panel, .right-panel {
                max-height: none;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Admin QR Scanner</h1>
        <p>Scan library reservation QR codes to verify bookings</p>
    </header>
    
    <div class="container">
        <div class="left-panel">
            <div class="scanner-container">
                <h2>QR Code Scanner</h2>
                <div id="video-container">
                    <video id="qr-video" playsinline></video>
                    <canvas id="qr-canvas"></canvas>
                    <div class="scanner-overlay">
                        <div class="scanner-line"></div>
                    </div>
                </div>
                
                <div class="controls">
                    <button id="start-button"><i class="fas fa-camera"></i> Start Scanner</button>
                    <button id="stop-button" disabled><i class="fas fa-stop"></i> Stop Scanner</button>
                </div>
            </div>

            <?php if ($scanResult): ?>
            <div class="alert-success">
                <h3><i class="fas fa-check-circle"></i> Valid Reservation</h3>
                <p><?php echo htmlspecialchars($scanResult); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if ($scanError): ?>
            <div class="alert-danger">
                <h3><i class="fas fa-exclamation-circle"></i> Invalid Reservation</h3>
                <p><?php echo htmlspecialchars($scanError); ?></p>
            </div>
            <?php endif; ?>

            <div class="nav-links">
                <a href="admin.php"><i class="fas fa-tachometer-alt"></i> Back to Dashboard</a>
            </div>
        </div>

        <div class="right-panel">
            <div class="manual-input">
                <h2>Enter Reservation Details</h2>
                <form method="post" action="" id="manual-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="student_id"><i class="fas fa-id-card"></i> Student ID</label>
                            <input type="text" id="student_id" name="student_id" class="form-control" placeholder="e.g., S12345" required>
                        </div>
                        <div class="form-group">
                            <label for="table_number"><i class="fas fa-table"></i> Table Number</label>
                            <input type="number" id="table_number" name="table_number" class="form-control" placeholder="e.g., 5" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="reservation_date"><i class="fas fa-calendar-alt"></i> Date</label>
                            <input type="date" id="reservation_date" name="reservation_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="reservation_time"><i class="fas fa-clock"></i> Time</label>
                            <input type="time" id="reservation_time" name="reservation_time" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="chairs"><i class="fas fa-chair"></i> Chairs</label>
                            <input type="number" id="chairs" name="chairs" class="form-control" placeholder="e.g., 2" required min="1" max="8">
                        </div>
                        <div class="form-group">
                            <label for="duration"><i class="fas fa-hourglass-half"></i> Duration</label>
                            <select id="duration" name="duration" class="form-control" required>
                                <option value="30">30 minutes</option>
                                <option value="60" selected>1 hour</option>
                                <option value="90">1.5 hours</option>
                                <option value="120">2 hours</option>
                            </select>
                        </div>
                    </div>
                    
                    <input type="hidden" name="form_submitted" value="1">
                    <button type="submit"><i class="fas fa-search"></i> Verify Reservation</button>
                </form>
            </div>

            <?php if ($reservationDetails): ?>
            <div class="reservation-details">
                <h2><i class="fas fa-clipboard-check"></i> Reservation Details</h2>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-id-card"></i> Student ID:</span>
                    <span><?php echo htmlspecialchars($reservationDetails['student_id']); ?></span>
                </div>
                
                <?php if (isset($reservationDetails['student_name']) && !empty($reservationDetails['student_name'])): ?>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-user"></i> Student Name:</span>
                    <span><?php echo htmlspecialchars($reservationDetails['student_name']); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-table"></i> Table Number:</span>
                    <span><?php echo htmlspecialchars($reservationDetails['table_number']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-calendar-alt"></i> Date & Time:</span>
                    <span><?php echo htmlspecialchars(date('Y-m-d h:i A', strtotime($reservationDetails['reservation_time']))); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-clock"></i> Duration:</span>
                    <span><?php echo htmlspecialchars($reservationDetails['duration']); ?> minutes</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-chair"></i> Chairs:</span>
                    <span><?php echo isset($reservationDetails['chairs_used']) ? htmlspecialchars($reservationDetails['chairs_used']) : '0'; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-info-circle"></i> Status:</span>
                    <span class="status-tag status-valid"><?php echo ucfirst(htmlspecialchars($reservationDetails['status'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-times-circle"></i> Cancel Reservation:</span>
                    <span>
                        <form action="admin-cancel.php" method="POST" style="display: inline;" 
                              onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($reservationDetails['id']); ?>">
                            <button type="submit" class="cancel-button" style="background-color: #dc3545; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">
                                <i class="fas fa-times"></i> Cancel Reservation
                            </button>
                        </form>
                    </span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        const video = document.getElementById('qr-video');
        const canvas = document.getElementById('qr-canvas');
        const startButton = document.getElementById('start-button');
        const stopButton = document.getElementById('stop-button');
        const videoContainer = document.getElementById('video-container');
        const ctx = canvas.getContext('2d');
        
        // Set default date to today for the reservation date field
        document.addEventListener('DOMContentLoaded', function() {
            // Set today's date as default for the date input
            if (document.getElementById('reservation_date')) {
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const day = String(today.getDate()).padStart(2, '0');
                document.getElementById('reservation_date').value = `${year}-${month}-${day}`;
            }
        });
        
        let scanning = false;
        let videoStream = null;
        
        // Start the QR scanner
        startButton.addEventListener('click', () => {
            startScanner();
        });
        
        // Stop the QR scanner
        stopButton.addEventListener('click', () => {
            stopScanner();
        });
        
        // Function to start the QR scanner
        function startScanner() {
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                // Add loading visual feedback
                startButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Starting Camera...';
                
                navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: "environment" } 
                })
                .then(function(stream) {
                    videoStream = stream;
                    video.srcObject = stream;
                    video.setAttribute('playsinline', true);
                    video.play();
                    scanning = true;
                    startButton.disabled = true;
                    stopButton.disabled = false;
                    
                    // Add active scanner effect
                    videoContainer.classList.add('scanner-active');
                    
                    // Update button text
                    startButton.innerHTML = '<i class="fas fa-camera"></i> Start Scanner';
                    
                    scanQRCode();
                })
                .catch(function(error) {
                    console.error("Camera error: ", error);
                    alert("Could not access the camera: " + error.message);
                    
                    // Reset button text
                    startButton.innerHTML = '<i class="fas fa-camera"></i> Start Scanner';
                });
            } else {
                alert("Sorry, your browser doesn't support camera access");
            }
        }
        
        // Function to stop the QR scanner
        function stopScanner() {
            if (videoStream) {
                videoStream.getTracks().forEach(track => {
                    track.stop();
                });
                videoStream = null;
                video.srcObject = null;
                scanning = false;
                startButton.disabled = false;
                stopButton.disabled = true;
                
                // Remove active scanner effect
                videoContainer.classList.remove('scanner-active');
            }
        }
        
        // Function to continuously scan for QR codes
        function scanQRCode() {
            if (!scanning) return;
            
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                canvas.height = video.videoHeight;
                canvas.width = video.videoWidth;
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                
                // Use jsQR to find QR codes in the image
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "dontInvert",
                });
                
                if (code) {
                    // QR code found - provide visual feedback
                    videoContainer.style.border = "3px solid #0f5132";
                    setTimeout(() => {
                        videoContainer.style.border = "";
                    }, 500);
                    
                    console.log("QR Code detected: ", code.data);
                    handleQRData(code.data);
                }
            }
            
            // Continue scanning
            requestAnimationFrame(scanQRCode);
        }
        
        // Handle QR code data
        function handleQRData(data) {
            // Stop scanning
            stopScanner();
            
            // Provide visual feedback
            videoContainer.classList.add('scan-success');
            
            try {
                // Try to parse the JSON data
                JSON.parse(data);
                
                // Submit the form with the QR data
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'qr_data';
                input.value = data;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            } catch (e) {
                alert("Invalid QR code data: " + e.message);
                // Restart scanning
                startScanner();
            }
        }
        
        // Add keyboard shortcut for starting scanner (press 's')
        document.addEventListener('keydown', (e) => {
            if (e.key.toLowerCase() === 's' && !startButton.disabled) {
                startScanner();
            }
        });
        
        // Auto-focus the textarea when manual input section is clicked
        document.querySelector('.manual-input').addEventListener('click', () => {
            document.querySelector('textarea[name="qr_data"]').focus();
        });
    </script>
</body>
</html> 