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

// استقبال معايير البحث والتصفية
$page   = isset($_GET['page'])   ? (int)$_GET['page']   : 1;
$limit  = isset($_GET['limit'])  ? (int)$_GET['limit']  : 50;
$search = isset($_GET['search']) ? $_GET['search']      : '';
$status = isset($_GET['status']) ? $_GET['status']      : '';
$role   = isset($_GET['role'])   ? $_GET['role']        : '';

$offset = ($page - 1) * $limit;

// بناء شروط WHERE ديناميكية
$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(username LIKE :search OR email LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($status !== '') {
    $where[] = "status = :status";
    $params[':status'] = $status;
}
if ($role !== '') {
    $where[] = "role = :role";
    $params[':role'] = $role;
}

$whereSQL = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// استعلام SQL مع التصفية والتقسيم
$sql = "SELECT id, username, email, role, status, created_at 
        FROM users 
        $whereSQL 
        ORDER BY created_at DESC 
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);

// ربط القيم
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// إرجاع البيانات
header('Content-Type: application/json');
echo json_encode([
    'page' => $page,
    'limit' => $limit,
    'count' => count($users),
    'users' => $users
]);
?>