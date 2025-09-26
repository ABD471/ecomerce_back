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

$stmt = $pdo->query("SELECT v.vendor_id, v.user_vendor AS user_id, u.username, u.email, v.store_name
                      FROM vendors v
                      JOIN users u ON v.user_vendor = u.id
                      WHERE v.is_active = false");

$pending_vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($pending_vendors);
?>