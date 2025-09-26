<?php






require __DIR__ . '/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include "index.php"; // تأكد أن هذا الملف يحتوي على تعريف $pdo

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
// إعداد رؤوس الاستجابة
header('Content-Type: application/json');

// التحقق من نوع المحتوى
//if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
  //  http_response_code(415); // Unsupported Media Type
   // echo json_encode(['error' => 'Content-Type must be application/json']);
   // exit;
//}

// السماح فقط بطلبات POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Only POST requests are allowed']);
    exit;
}

// استقبال البيانات من الطلب
$data = json_decode(file_get_contents('php://input'), true);

// التحقق من وجود البيانات المطلوبة
if (!isset($data['email']) || !isset($data['password'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// تنظيف البيانات
$email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
$password = trim($data['password']);

if (!$email) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

// إعداد بيانات JWT
$config = require __DIR__ . '/config.php';
$secret_key= $config['jwt_key']; // تأكد من ضبط هذا المتغير في البيئة
$issuedAt   = time();
$expiration = $issuedAt + 3600; // صالح لمدة ساعة

try {
    // التحقق من وجود المستخدم وكلمة المرور
    $stmt = $pdo->prepare('SELECT id, password, role FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(401); // Unauthorized
        echo json_encode(['error' => 'Invalid credentials']);
        exit;
    }

    $dbpassword = $user['password'];

    if (password_verify($password, $dbpassword)) {
        // إعداد الـ payload مع بيانات المستخدم
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expiration,
            'data' => [
                'user_id' => $user['id'],
                'email' => $email,
                'role' => $user['role']
            ]
        ];

        $jwt = JWT::encode($payload, "$secret_key", 'HS256');

        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'email' => $email,
                'role' => $user['role'],
                'token' => $jwt
            ]
        ]);
    } else {
        http_response_code(401); // Unauthorized
        echo json_encode(['error' => 'Invalid credentials']);
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}