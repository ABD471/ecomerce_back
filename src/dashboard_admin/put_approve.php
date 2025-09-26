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
$vendor_id = $data['vendor_id'] ?? null;

if (!$vendor_id) {
    http_response_code(400);
    echo json_encode(["error" => "Missing vendor_id"]);
    exit;
}

$stmt = $pdo->prepare("UPDATE vendors SET is_active = true WHERE vendor_id = ?");
$stmt->execute([$vendor_id]);

echo json_encode(["success" => true, "message" => "تمت الموافقة على البائع"]);
?>