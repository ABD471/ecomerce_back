<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

return  $arry =[
    'jwt_key' => $_ENV['JWT_SECRET'],
    'smtp' => [
        'host' => $_ENV['SMTP_HOST'],
        'port' => $_ENV['SMTP_PORT'],
        'user' => $_ENV['SMTP_USER'],
        'pass' => $_ENV['SMTP_PASS'],
        'secure' => $_ENV['SMTP_SECURE'],
        'from_email' => $_ENV['FROM_EMAIL'],
        'from_name' => $_ENV['FROM_NAME']
    ]
];