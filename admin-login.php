<?php
session_start(); // Start session at the top
require_once 'config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        // Prepare and execute the query
        $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            // Check if password hash exists and verify password
            if (isset($admin['password_hash']) && !empty($admin['password_hash'])) {
                if (password_verify($password, $admin['password_hash'])) {
                    $_SESSION['admin_email'] = $admin['email'];
                    header("Location: admin.php");
                    exit();
                } else {
                    $error = "Invalid password";
                }
            } else {
                $error = "Password hash not found";
            }
        } else {
            $error = "Admin not found";
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo $APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ffffff;
            margin: 0;
        }
        .container1 {
            display: flex;
            justify-content: right;
            align-items: center;
            height: 88vh;
            width: 87%;
            background-color: #ffffff;
            border-radius: 8px;
            padding-top: 50px;
            gap: 163px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #444444;
        }
        .input {
            margin-bottom: 15px;
        }
        .input label {
            display: block;
            margin-bottom: 5px;
        }
        .input input {
            width: 100%;
            padding: 8px;
            border: 1px solid #444444;
            border-radius: 4px;
            color: #444444;
            box-sizing: border-box;
        }
        .password-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-container input {
            width: 100%;
            padding-right: 30px;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            cursor: pointer;
            font-size: 18px;
            user-select: none;
            color: #444444;
        }
        .toggle-password:hover {
            color: #b91a1a;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #b91a1a;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        button:hover {
            background-color: #444444;
        }
        p {
            text-align: center;
            margin-top: 20px;
        }
        a {
            color: #444444;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .banner {
            position: absolute;
            z-index: 1;
            background-color: #b91a1a;
            height: 7vh;
            width: 100%;
        }
        .logo {
            text-align: center;
        }
        .nav {
            position: absolute;
            z-index: 2;
            width: 20%;
            background-color: #ed1c24;
            height: 7vh;
        }
        .container {
            width: 29%;
        }
        @media (max-width: 693px) {
            .container {
                width: 100%;
            }
        }
        .imgcontainer {
            height: 112vh;
            overflow: hidden;
        }
        .bg-image {
            position: relative;
            top: -78px;
            width: 906px;
        }
        .admin-notice {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="nav"></div>
    <div class="banner"></div>
    <div class="container1">
        <div class="imgcontainer">
            <img class="bg-image" src="img/BG-login.jpg" alt="">
        </div>
        <div class="container">
            <div class="logo">
                <img src="img/Kjsit_logo.png" alt="">
            </div>
            <h2>Admin Login</h2>
            <?php if ($error): ?>
                <div class="admin-notice">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="input">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="input">
                    <label for="password">Password</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" required>
                        <span class="toggle-password">👁️</span>
                    </div>
                    <button type="submit">Login</button>
                </div>
            </form>
            
            <p><a href="home.php">Return to Home Page</a></p>
        </div>
    </div>

    <script>
        document.querySelector(".toggle-password").addEventListener("click", function() {
            const passwordField = document.getElementById("password");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                this.textContent = "🙈";
            } else {
                passwordField.type = "password";
                this.textContent = "👁️";
            }
        });
    </script>
</body>
</html> 