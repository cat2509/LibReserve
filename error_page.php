<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Maintenance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f5f5f5;
        }
        .error-container {
            max-width: 600px;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 {
            color: #b91a1a;
            margin-bottom: 20px;
        }
        p {
            color: #666;
            margin-bottom: 20px;
        }
        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #b91a1a;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #921515;
        }
        .maintenance-icon {
            font-size: 64px;
            margin-bottom: 20px;
            color: #b91a1a;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="maintenance-icon">🔧</div>
        <h1>System Maintenance</h1>
        <p>We're currently performing some maintenance on our system. Please try again in a few minutes.</p>
        <p>If the problem persists, please contact the library staff.</p>
        <a href="index.php" class="back-button">Return to Homepage</a>
    </div>
</body>
</html> 