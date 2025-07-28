<?php
//isset() - ตรวจสอบว่าตัวแปรมีค่า
$name = "สมชาย";
$age  = null;
if (isset($name)) {
    echo "ตัวแปร name มีค่า: $name<br>";
}
if (isset($age)) {
    echo "ตัวแปร age มีค่า<br>";
} else {
    echo "ตัวแปร age ไม่มีค่าหรือเป็น NULL<br>";
}
if (isset($undefined)) {
    echo "ตัวแปร undefined มีค่า<br>";
} else {
    echo "ตัวแปร undefined ไม่ถูกกำหนด<br>";
}