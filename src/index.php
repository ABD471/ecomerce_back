<?php
require __DIR__ . '/vendor/autoload.php';
$host = "postgres";
$dbname = "ecommerce";
$user = "abdalmjed";
$password = "08TEFGnMhn6u7ug0tQKT8OoutR6qxzPP";

try{
    $dsn ="pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn,$user,$password,[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); 
}catch(PDOException $e){
    echo "failed".$e->getMessage();

}