<?php
//ตัวดำเนินการสตริง (String Operators)
$firstName = "สมชาย";
$lastName  = "ใจดี";
// การต่อสตริงด้วย . (dot)
$fullName = $firstName . " " . $lastName;
echo "ชื่อเต็ม: $fullName<br>";
// การต่อสตริงด้วย .= (dot equal)
$message = "สวัสดี";
$message .= " คุณ";
$message .= " " . $fullName;
echo "ข้อความ: $message<br>";
// การต่อสตริงแบบซับซ้อน
$product  = "โทรศัพท์";
$price    = 15000;
$currency = "บาท";
$info     = "สินค้า: " . $product . " | ราคา: " . $price . " " . $currency;
echo $info . "<br>";