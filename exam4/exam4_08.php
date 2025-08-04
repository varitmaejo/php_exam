<?php
//ระบบจองตั้วเครื่องบิน
$destination = "ญี่ปุ่น";
$seat_available = true;
$budget = 25000;

if ($destination == "ญี่ปุ่น") {
    if ($seat_available) {
        if ($budget >= 20000) {
            echo "จองตั๋วไปญี่ปุ่นสำเร็จ!";
        } else {
            echo "งบประมาณไม่เพียงพอ ต้องการอย่างน้อย 20,000 บาท";
        }
    } else {
        echo "ไม่มีที่นั่งว่างสำหรับเที่ยวบินไปญี่ปุ่น";
    }
} else {
    echo "ขออภัย ขณะนี้ไม่มีเที่ยวบินไป $destination";
}