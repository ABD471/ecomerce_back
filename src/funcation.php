<?php
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
?>