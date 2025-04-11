<?php
// Get server IP
$serverIP = "192.168.0.103"; 

// Test booking data
$testData = [
    'id' => 'STUD9',
    'table' => '3',
    'time' => '2025-04-10 16:00:00',
    'chairs' => '2'
];

// Create the URL for QR code
$qrUrl = "http://{$serverIP}/final/qr-result.php?" . http_build_query($testData);

// Generate QR code URL
$qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($qrUrl) . "&size=300x300&margin=10&ecc=H";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test QR Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }
        .qr-container {
            margin: 30px auto;
            padding: 20px;
            border: 2px dashed #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        h1 {
            color: #b91a1a;
        }
        img {
            max-width: 300px;
            border: 1px solid #eee;
            margin: 20px 0;
        }
        .url {
            word-break: break-all;
            background: #eee;
            padding: 10px;
            border-radius: 5px;
            margin: 20px 0;
            font-family: monospace;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>Test QR Code</h1>
    <p>Scan this QR code with your phone camera or Google Lens</p>
    
    <div class="qr-container">
        <img src="<?php echo $qrImageUrl; ?>" alt="Test QR Code">
    </div>
    
    <p><strong>The QR code contains this URL:</strong></p>
    <div class="url"><?php echo htmlspecialchars($qrUrl); ?></div>
    
    <p>Try also directly opening this URL on your mobile device:</p>
    <a href="<?php echo htmlspecialchars($qrUrl); ?>" target="_blank">Open URL directly</a>
</body>
</html> 