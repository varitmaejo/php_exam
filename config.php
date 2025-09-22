<?php
// ไฟล์: config.php
$host = 'localhost';
$username = 'itshun_222222222222';
$password = 'syo9ik123';
$database = 'itshun_222222222222';

try {
    // สร้างการเชื่อมต่อด้วย PDO
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", 
                   $username, $password);
    // ตั้งค่า error mode เป็น exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("การเชื่อมต่อล้มเหลว: " . $e->getMessage());
}
?>