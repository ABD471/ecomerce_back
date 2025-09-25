<?php

require __DIR__ . '/vendor/autoload.php';
include 'funcation.php';
include 'index.php';

// Set response header to JSON
header('Content-Type: application/json');

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Only POST requests are allowed']);
    exit;
}

// Receive and decode JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['hash_otp'], $data['email'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Sanitize input
$enteredOtp = htmlspecialchars(trim($data['hash_otp']));
$email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);

// Validate email format
if (!$email) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

try {
    // Fetch user data from database
    $stmt = $pdo->prepare('SELECT hash_otp, otp_created_at, otp_attempts, otp_blocked_at,id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Check if user is blocked due to too many attempts
        if ($user['otp_attempts'] >= 5) {
            if ($user['otp_blocked_at']) {
                $blockedUntil = strtotime($user['otp_blocked_at']) + (24 * 60 * 60); // 24 hours
                if (time() < $blockedUntil) {
                     http_response_code(600);
                    echo json_encode(['success' => false, 'message' => '🚫 You are temporarily blocked. Try again after 24 hours']);
                    exit;
                } else {
                    // Unblock user after 24 hours
                    $pdo->prepare("UPDATE users SET otp_attempts = 0, otp_blocked_at = NULL WHERE email = ?")->execute([$email]);
                }
            } else {
                // First time blocking the user
                $pdo->prepare("UPDATE users SET otp_blocked_at = NOW() WHERE email = ?")->execute([$email]);
                 http_response_code(601);
                echo json_encode(['success' => false, 'message' => '🚫 You are temporarily blocked due to too many attempts']);
                exit;
            }
        }

        // Check OTP expiration (valid for 5 minutes)
        $otpValidUntil = strtotime($user['otp_created_at']) + (5 * 60);
        if (time() > $otpValidUntil) {
             http_response_code(602);
            echo json_encode(['success' => false, 'message' => '⏰ OTP has expired']);
            exit;
        }

        // Verify OTP match
        if ($user['hash_otp'] == $enteredOtp) {
            // Activate account and reset attempts and reset otp and reset otp_created_at
            $pdo->prepare("UPDATE users SET active = TRUE, otp_attempts = 0, otp_blocked_at = NULL,hash_otp = NULL , otp_created_at = NULL WHERE email = ?")->execute([$email]);
             http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => '✅ Account activated successfully',
                'user' => [
                    'id' => $user['id'],
                    'email' => $email
                ]
            ]);
        } else {
            // Increment failed attempts
            $pdo->prepare("UPDATE users SET otp_attempts = otp_attempts + 1 WHERE email = ?")->execute([$email]);
                 http_response_code(603);
                 
            echo json_encode([
                'success' => false,
                'message' => '❌ Incorrect OTP',
                'user' => [
                    'email' => $email
                ]
            ]);
        }
    } else {
         http_response_code(604);
        echo json_encode(['success' => false, 'message' => '⚠️ User not found']);
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}