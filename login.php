<?php
session_start(); // Start session at the top

$is_invalid = false;

// Ensure db_connect.php is included properly
require __DIR__ . "/db_connect.php";

// Ensure $mysqli is properly assigned
if (!isset($mysqli) || !$mysqli instanceof mysqli) {
    die("Database connection error.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get email and password safely
    $email = trim($_POST["email"] ?? '');
    $password = trim($_POST["password"] ?? '');

    if (empty($email) || empty($password)) {
        $is_invalid = true;
    } else {
        // Use a prepared statement to prevent SQL injection
        $stmt = $mysqli->prepare("SELECT * FROM user WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            // Verify the password
            if ($user && password_verify($password, $user["password_hash"])) {
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["email"] = $user["email"]; // ✅ Store email
                $_SESSION["student_id"] = $user["student_id"]; // ✅ Assuming this field exists
                header("Location: layout.php");
                exit();
            }
             else {
                $is_invalid = true;
            }
        } else {
            die("Query preparation failed: " . $mysqli->error);
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/jwt-decode@3.1.2/build/jwt-decode.min.js"></script>
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
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .forgot-password {
            text-align: right;
            margin-top: 10px;
        }
        .forgot-password a {
            color: #444444;
        }
        .forgot-password a:hover {
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
            <h2>Welcome to Somaiya</h2>
            <form method="post">
                <?php if ($is_invalid): ?>
                    <p style="color: red; text-align: center;">Invalid Login</p>
                <?php endif; ?>
                
                <div class="input">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($_POST["email"] ?? "") ?>" required>
                </div>

                <div class="input">
                    <label for="password">Password</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" required>
                        <span class="toggle-password">👁️</span>
                    </div>
                    <p class="forgotpassword"><a href="forgotpassword.php">Forgot Password?</a></p>
                    <button type="submit">Login</button>
                </div>
            </form>
            
            <p>Don't have an account? <a href="register.php">Register Here</a></p>

<p style="text-align: center; margin-top: 10px;">
    <a href="home.php" style="color: #444444;">Return to Home Page</a>
</p>

            
            <div id="google-signin"></div>
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
