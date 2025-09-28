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

// استقبال البيانات بصيغة JSON
$input = json_decode(file_get_contents("php://input"), true);
$emails = $input['emails'] ?? [];
$status = $input['status'] ?? '';

// تحقق أن البيانات مصفوفة
if (is_array($emails)&& in_array($status, ['active', 'unactive'])) {

   $sql = "UPDATE users SET status = :status WHERE email = :email";
    $stmt = $pdo->prepare($sql);


    foreach ($emails as $emails) {
        $stmt->execute([
            ':status' => $status,
            ':email' => $emails
        ]);
        echo  "تم تعديل حالة $emails إلى $status<br>";
    }
} else {
    echo "البيانات غير صالحة.";
}

?>








