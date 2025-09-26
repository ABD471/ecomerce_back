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
$new_role = $data['new_role'] ?? null;

$valid_roles = ['admin', 'vendor', 'customer'];
if (!$user_id || !in_array($new_role, $valid_roles)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid input"]);
    exit;
}

$stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
$stmt->execute([$new_role, $user_id]);

echo json_encode(["success" => true, "message" => "تم تغيير الدور"]);
?>