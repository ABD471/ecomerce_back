<?php

require __DIR__ . '/vendor/autoload.php';
include "index.php"; 

use Dotenv\Dotenv;

// تحميل متغيرات البيئة
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// إعداد رؤوس الاستجابة
header('Content-Type: application/json');



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

if (!$email || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email or password']);
    exit;
}

// تشفير كلمة المرور
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    // تحديث كلمة المرور
    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
    $stmt->execute([$hashedPassword, $email]);

    // التحقق من نجاح التحديث
    if ($stmt->rowCount() === 0) {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'No user found with this email']);
        exit;
    }

    // جلب معلومات المستخدم
    $stmt1 = $pdo->prepare('SELECT id, username, email, active FROM users WHERE email = ?');
    $stmt1->execute([$email]);
    $user = $stmt1->fetch(PDO::FETCH_ASSOC);

    // التحقق من حالة الحساب
    if (!$user['active']) {
        http_response_code(403); // Forbidden
        echo json_encode(['error' => 'User account is not active']);
        exit;
    }

    // إرسال الاستجابة
    echo json_encode([
        'success' => true,
        'message' => 'Password reset successfully',
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email']
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Database error']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Unexpected error']);
}