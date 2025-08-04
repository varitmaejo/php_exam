<?php
// ตรวจสอบรหัสผ่าน
$password = "123456";
$correct_password = "abc123";

if ($password == $correct_password) {
    echo "เข้าสู่ระบบสำเร็จ";
} else {
    echo "รหัสผ่านไม่ถูกต้อง";
}