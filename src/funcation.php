<?php


require 'vendor/autoload.php'; // تحميل مكتبة JWT و phpdotenv


require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;


function generateOTP($length = 5) {
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= rand(0, 9);
    }
    return $otp;
}

function resendOTP($email){
    try{
    include 'index.php';
    $hash_otp = generateOTP();
    $otp_created_at = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("UPDATE users SET hash_otp = ?, otp_created_at = ? WHERE email = ?");
    $stmt->execute([$hash_otp, $otp_created_at, $email]);
 return  $hash_otp;
}catch(PDOException $e){
 http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
}




function verifyToken($token) {
    $config = require __DIR__ . '/config.php';
$secret_key= $config['jwt_key'];
    // تحميل ملف البيئة .env
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    // جلب المفتاح السري من البيئة
   // $secret_key = getenv('JWT_SECRET'); 
 

    if (!$secret_key || !is_string($secret_key)) {
        http_response_code(500);
        echo json_encode(["error" => "JWT secret key not found or invalid"]);
        exit;
    }

    try {
        // فك التوكن والتحقق منه
        $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));

        // إرجاع البيانات فقط (user_id, email, role)
        return $decoded->data;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode($e->getMessage());
        echo json_encode(["error" => "Invalid or expired token"]);
        exit;
    }
}
?>