<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';
include 'funcation.php';

$config = require __DIR__ . '/config.php';
$mailConfig = $config['smtp'];


// إعداد رؤوس الاستجابة
header('Content-Type: application/json');
include "index.php";


// السماح فقط بطلبات POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Only POST requests are allowed']);
    exit;
}

// استقبال البيانات من الطلب
$data = json_decode(file_get_contents('php://input'), true);

// التحقق من وجود البيانات المطلوبة
if ( !isset($data['email'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// تنظيف البيانات لتجنب الحقن

$email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);



if (!$email) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}


try {

    // التحقق من  وجود المستخدم مسبقًا
    $stmt = $pdo->prepare('SELECT username FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user =$stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $hash_otp = resendOTP($email);
    $recipient_email = $email;
    $recipient_name = $user["username"];
    $mail = new PHPMailer(true);
    // تهيئة STMP
    $mail->isSMTP();
    $mail->Host       = $mailConfig['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $mailConfig['user'];
    $mail->Password   = $mailConfig['pass'];
    $mail->SMTPSecure = $mailConfig['secure'];
    $mail->Port       = $mailConfig['port'];

    // إعدادات الرسالة
    $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
    $mail->addAddress($recipient_email, $recipient_name);
    $mail->isHTML(true);
    $mail->Subject = '🔐 Your OTP Code';
    $mail->Body    = "<h3>Your OTP is: <strong>$hash_otp</strong></h3><p>This code is valid for 5 minutes.</p>";
    $mail->AltBody = "Your OTP is: $hash_otp. This code is valid for 5 minutes.";
    $mail->send();

        http_response_code(200); // Conflict
        echo json_encode([

            'success' => 'Email already registered',
            'user' => [
            'username' =>$user['username'],
            'email' => $email
        ]

        ]);
        
        exit;
    }
     http_response_code(404);
    echo json_encode([ 
        "success" => false,
        "message"  => "not user found ",
        

]);
    
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Email could not be sent. Mailer Error: ' . $mail->ErrorInfo]);
}
