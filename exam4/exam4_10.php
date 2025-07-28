<?php
//เมนูร้านอาหาร
$menu_choice = 2;

switch ($menu_choice) {
    case 1:
        echo "คุณเลือก: ข้าวผัดกุ้ง - ราคา 60 บาท";
        break;
    case 2:
        echo "คุณเลือก: ต้มยำกุ้ง - ราคา 80 บาท";
        break;
    case 3:
        echo "คุณเลือก: ส้มตำไทย - ราคา 50 บาท";
        break;
    case 4:
        echo "คุณเลือก: แกงเขียวหวานไก่ - ราคา 70 บาท";
        break;
    default:
        echo "ไม่มีเมนูนี้ในรายการ";
}
?>