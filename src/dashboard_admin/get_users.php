<?php
require '../index.php';
require '../funcation.php';

// استخراج التوكن من الهيدر
$headers = getallheaders();
$token = $headers['Authorization'] ?? '';

if (!$token) {
    http_response_code(401);
    echo json_encode(["error" => "Missing token"]);
    exit;
}

// التحقق من التوكن
$decoded = verifyToken(str_replace("Bearer ", "", $token));

// التحقق من الدور
if ($decoded->role !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// جلب المستخدمين
$stmt = $pdo->query("SELECT id, username, email, role, active, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($users);
?>