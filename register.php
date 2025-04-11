<?php
session_start();
$error = ""; // Initialize error variable

// Include PHPMailer
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to generate OTP
function generateOTP($length = 6) {
    $otp = "";
    for ($i = 0; $i < $length; $i++) {
        $otp .= random_int(0, 9);
    }
    return $otp;
}

// If OTP verification is submitted
if (isset($_POST['verify_otp'])) {
    // Check if OTP matches
    if (isset($_SESSION['otp']) && $_POST['otp'] == $_SESSION['otp']) {
        require __DIR__ . "/db_connect.php";
        $mysqli = getConnection();
        
        // Get the registration data from session
        $email = $_SESSION['temp_email'];
        $hashed_password = $_SESSION['temp_password'];
        
        // Generate a new student_id like STUD101, STUD102...
        $result = $mysqli->query("SELECT MAX(CAST(SUBSTRING(student_id, 5) AS UNSIGNED)) AS max_id FROM user");
        $next_id = 101;
        if ($result && $row = $result->fetch_assoc()) {
            $next_id = max(101, (int)$row["max_id"] + 1);
        }
        $student_id = "STUD" . $next_id;

        // Insert into user table
        $stmt = $mysqli->prepare("INSERT INTO user (email, password_hash, student_id) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $hashed_password, $student_id);

        if ($stmt->execute()) {
            // Clear temp session data
            unset($_SESSION['otp'], $_SESSION['temp_email'], $_SESSION['temp_password']);
            
            // Set user session
            $_SESSION["user_id"] = $mysqli->insert_id;
            $_SESSION["email"] = $email;
            $_SESSION["student_id"] = $student_id;

            header("Location: login.php");
            exit();
        } else {
            $error = "Error: Could not register.";
        }
    } else {
        $error = "Invalid OTP. Please try again.";
    }
}

// If initial registration form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['verify_otp'])) {
    require __DIR__ . "/db_connect.php";
    $mysqli = getConnection();

    $email = $_POST["email"];
    $password = $_POST["password"];

    if (!preg_match("/^[a-zA-Z0-9._%+-]+@somaiya\.edu$/", $email)) {
        $error = "Only emails with @somaiya.edu domain are allowed.";
    } else {
        $stmt = $mysqli->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already registered. Please login.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Generate OTP
            $otp = generateOTP();
            
            // Store data in session
            $_SESSION['otp'] = $otp;
            $_SESSION['temp_email'] = $email;
            $_SESSION['temp_password'] = $hashed_password;
            
            // Send OTP via email
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'sanaaakadam@gmail.com'; // Use the same email as in confirmation.php
                $mail->Password = 'lpqt keke dptb ikpb'; // App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                
                // Recipients
                $mail->setFrom('sanaaakadam@gmail.com', 'Library Booking System');
                $mail->addAddress($email);
                
                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Your OTP for Library Booking System Registration';
                $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                    <h2 style='color: #b91a1a;'>Email Verification</h2>
                    <p>Thank you for registering with the Library Booking System.</p>
                    <p>Your One-Time Password (OTP) is: <strong style='font-size: 24px; color: #b91a1a;'>{$otp}</strong></p>
                    <p>Please enter this OTP on the registration page to verify your email address.</p>
                    <p style='color: #666; font-size: 14px;'>If you did not request this OTP, please ignore this email.</p>
                </div>";
                
                $mail->send();
                $showOtpForm = true;
            } catch (Exception $e) {
                $error = "Could not send verification email. Error: " . $mail->ErrorInfo;
                $showOtpForm = false;
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
        .error-box {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success-box {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .otp-container {
            text-align: center;
        }
        .otp-input {
            letter-spacing: 10px;
            font-size: 20px;
            padding: 10px;
            text-align: center;
            width: 200px;
            margin: 0 auto;
        }
        .resend-link {
            display: inline-block;
            margin-top: 10px;
            color: #b91a1a;
            cursor: pointer;
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
            <h2>Register</h2>

            <?php if (!empty($error)): ?>
                <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($showOtpForm) && $showOtpForm): ?>
                <div class="success-box">
                    OTP has been sent to <?php echo htmlspecialchars($_SESSION['temp_email']); ?>. 
                    Please check your email.
                </div>
                
                <form method="post">
                    <div class="otp-container">
                        <label for="otp">Enter OTP:</label>
                        <input type="text" id="otp" name="otp" class="otp-input" maxlength="6" required>
                        <button type="submit" name="verify_otp">Verify & Complete Registration</button>
                        <a href="register.php" class="resend-link">Resend OTP</a>
                    </div>
                </form>
            <?php else: ?>
                <form method="post">
                    <div class="input">
                        <label for="email">Email:</label>
                        <input type="email" name="email" required 
                               pattern="[a-zA-Z0-9._%+-]+@somaiya\.edu"
                               title="Email must be from the @somaiya.edu domain">
                    </div>
                    
                    <div class="input">
                        <label for="password">Password:</label>
                        <div class="password-container">
                            <input type="password" id="password" name="password" required>
                            <span class="toggle-password">👁️</span>
                        </div>
                    </div>
                    
                    <button type="submit">Register</button>
                </form>
            <?php endif; ?>
            
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>

    <script>
        // Toggle Password Visibility
        document.querySelector(".toggle-password").addEventListener("click", function () {
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
