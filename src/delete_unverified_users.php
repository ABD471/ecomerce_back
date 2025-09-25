<?php
include 'index.php';
try{
 $stmt = $pdo->prepare("DELETE FROM users WHERE active = false AND created_at < NOW() - INTERVAL '5 minutes'");
 $stmt->execute();
 $count = $stmt->rowCount();
    $message = "[" . date('Y-m-d H:i:s') . "] Deleted $count unverified users.\n";

    // عرض النتيجة في الطرفية
    echo $message;

    // تسجيل النتيجة في ملف log
    file_put_contents("/var/log/cron.log", $message, FILE_APPEND);
} catch (PDOException $e) {
    $error = "[" . date('Y-m-d H:i:s') . "] Error: " . $e->getMessage() . "\n";
    echo $error;
    file_put_contents("/var/log/cron.log", $error, FILE_APPEND);
}


    