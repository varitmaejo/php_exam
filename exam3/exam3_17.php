<?php
//print_r() - แสดงข้อมูลอ่านง่าย
$person = ["name" => "สมชาย ใจดี", "age" => 30, "hobbies" => ["อ่านหนังสือ", "ฟังเพลง", "เดินทาง"], "address" => ["street" => "123 ถนนสุขุมวิท", "city" => "กรุงเทพฯ", "zipcode" => "10110"]];
echo "ข้อมูลบุคคล:<br>";
print_r($person); 
// การใช้ print_r แบบคืนค่าเป็นสตริง 
$output = print_r($person, true);
echo "ความยาวข้อมูล: " . strlen($output) . " ตัวอักษร<br>";