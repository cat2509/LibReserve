<?php
// Set headers to prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Connection Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        h1 {
            color: #b91a1a;
        }
        .info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        code {
            background: #eee;
            padding: 3px 5px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <h1>Server Connection Test</h1>
    
    <div class="success">
        <strong>✅ Success!</strong> If you can see this page, your server is running correctly.
    </div>
    
    <div class="info">
        <h3>Server Information</h3>
        <p><strong>Server IP:</strong> <?php echo $_SERVER['SERVER_ADDR']; ?></p>
        <p><strong>Configured IP:</strong> 192.168.0.103</p>
        <p><strong>Your IP:</strong> <?php echo $_SERVER['REMOTE_ADDR']; ?></p>
        <p><strong>Server Software:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
        <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
        <p><strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        <p><strong>Directory:</strong> <?php echo __DIR__; ?></p>
    </div>
    
    <h3>Next Steps</h3>
    <p>Try these links:</p>
    <ul>
        <li><a href="test-qr.php">Test QR Code</a> - View and scan a test QR code</li>
        <li><a href="qr-result.php?id=TEST&table=1&time=2025-04-10%2016:00:00&chairs=2">Test QR Result</a> - View sample booking details</li>
    </ul>
</body>
</html> 