<?php

// Fetch email from POST request
$email = $_POST["email"];

// Validate Email Format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("<script>alert('Invalid email format.'); window.location.href='login.php';</script>");
}

// Generate Secure Token
$token = bin2hex(random_bytes(16));
$token_hash = hash("sha256", $token);
$expiry = date("Y-m-d H:i:s", time() + 60 * 30);

// Connect to Database
require __DIR__ . "/db_connect.php";
$mysqli = getConnection();

// Check if Email Exists
$check_email = $mysqli->prepare("SELECT email FROM user WHERE email = ?");
$check_email->bind_param("s", $email);
$check_email->execute();
$check_email->store_result();

if ($check_email->num_rows === 0) {
    die("<script>alert('Email not found.'); window.location.href='login.php';</script>");
}

// Update Reset Token in Database
$sql = "UPDATE user
        SET reset_token_hash = ?,
            t_token_expires_at = ?
        WHERE email = ?";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    die("<script>alert('SQL error: " . addslashes($mysqli->error) . "'); window.location.href='login.php';</script>");
}

$stmt->bind_param("sss", $token_hash, $expiry, $email);
$stmt->execute();

if ($stmt->affected_rows > 0) {

    // Load PHPMailer
    $mail = require __DIR__ . "/mailer.php";
    
    if (!$mail) {
        die("<script>alert('Mail configuration failed.'); window.location.href='login.php';</script>");
    }

    // Configure Email
    $mail->setFrom("noreply@example.com", "LibReserve Website");
    $mail->addAddress($email);
    $mail->Subject = "Password Reset";

    $token_safe = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
    $mail->Body = <<<END
    Click <a href="http://localhost/final/reset-password.php?token=$token_safe">here</a> 
    to reset your password.
    END;

    try {
        $mail->send();
        echo "<script>alert('Message sent, please check your inbox.'); window.location.href='login.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Message could not be sent. Mailer error: " . addslashes($mail->ErrorInfo) . "'); window.location.href='login.php';</script>";
    }

} else {
    echo "<script>alert('No changes made. Token update failed.'); window.location.href='login.php';</script>";
}

?>
