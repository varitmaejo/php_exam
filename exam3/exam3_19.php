<?php
//การจัดการข้อมูลผู้ใช้
// ฟังก์ชันตรวจสอบข้อมูล
function validateUser($userData)
{
    $errors = [];
    // ตรวจสอบชื่อ
    if (! isset($userData["name"]) || empty(trim($userData["name"]))) {
        $errors[] = "กรุณากรอกชื่อ";
    }
    // ตรวจสอบอีเมล
    if (! isset($userData["email"]) || ! filter_var($userData["email"], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "อีเมลไม่ถูกต้อง";
    }
    // ตรวจสอบอายุ
    if (! isset($userData["age"]) || ! is_numeric($userData["age"]) || $userData["age"] < 1) {
        $errors[] = "อายุต้องเป็นตัวเลขที่มากกว่า 0";
    }
    return $errors;
}
// ข้อมูลทดสอบ
$users = [
    ["name" => "สมชาย ใจดี", "email" => "somchai@email.com", "age" => 25],
    ["name" => "", "email" => "invalid-email", "age" => -5],
    ["name" => "สมหญิง", "email" => "somying@email.com", "age" => 30],
];
foreach ($users as $index => $user) {
    echo "=== ตรวจสอบผู้ใช้ที่ " . ($index + 1) . " ===<br>";
    print_r($user);
    $errors = validateUser($user);
    if (empty($errors)) {
        echo "ข้อมูลถูกต้อง<br><br>";
    } else {
        echo "พบข้อผิดพลาด:<br>";
        foreach ($errors as $error) {
            echo "- $error<br>";
        }
        echo "<br>";
    }
}