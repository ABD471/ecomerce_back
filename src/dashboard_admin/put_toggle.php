<?php
require '../index.php';
require '../funcation.php';

$headers = getallheaders();
$token = $headers['Authorization'] ?? '';
$decoded = verifyToken(str_replace("Bearer ", "", $token));

if ($decoded->role !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['id'] ?? null;

if (!$user_id) {
    http_response_code(400);
    echo json_encode(["error" => "Missing user_id"]);
    exit;
}

$stmt = $pdo->prepare("UPDATE users SET is_approve = NOT active WHERE id = ?");
$stmt->execute([$user_id]);

echo json_encode(["success" => true, "message" => "تم تغيير حالة المستخدم"]);
?>