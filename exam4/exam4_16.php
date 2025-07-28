<?php
//แสดงเมนูโปรแกรม
$choice    = 0;
$iteration = 0;
$choices   = [1, 2, 3];
// รายการตัวเลือกที่จะจำลอง
do {echo "<br>=== เมนูหลัก (รอบที่ " . ($iteration + 1) . ") ===<br>";
    echo "1. ดูข้อมูลส่วนตัว<br>";
    echo "2. แก้ไขข้อมูล<br>";
    echo "3. ออกจากระบบ<br>";
    echo "กรุณาเลือก (1-3): ";
// จำลองการเลือกแบบหมุนเวียน
    $choice = $choices[$iteration % count($choices)];
    echo $choice . "<br>";switch ($choice) {case 1:echo "แสดงข้อมูลส่วนตัว<br>";
            break;case 2:echo "แก้ไขข้อมูลเรียบร้อย<br>";
            break;case 3:echo "ออกจากระบบแล้ว<br>";
            break;default: echo "กรุณาเลือก 1-3 เท่านั้น<br>";}$iteration++;} while ($choice != 3);