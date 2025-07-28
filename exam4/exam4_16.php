<?php
$choice = 0;

do {
    echo "<br>=== เมนูหลัก ===<br>";
    echo "1. ดูข้อมูลส่วนตัว<br>";
    echo "2. แก้ไขข้อมูล<br>";
    echo "3. ออกจากระบบ<br>";
    echo "กรุณาเลือก (1-3): ";
    
    $choice = 2; // จำลองการเลือก
    
    switch ($choice) {
        case 1:
            echo "แสดงข้อมูลส่วนตัว<br>";
            break;
        case 2:
            echo "แก้ไขข้อมูลเรียบร้อย<br>";
            break;	
        case 3:
            echo "ออกจากระบบแล้ว<br>";
            break;
        default:
            echo "กรุณาเลือก 1-3 เท่านั้น<br>";
    }
} while ($choice != 3);
?>