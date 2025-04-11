<?php

require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    $mail = new PHPMailer(true);
    
    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->Username = "sanaaakadam@gmail.com";
    $mail->Password = "lpqt keke dptb ikpb"; 

    $mail->isHTML(true);

    return $mail;
} catch (Exception $e) {
    error_log("Mailer Error: " . $mail->ErrorInfo);
    return null;
}
