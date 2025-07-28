<?php
//is_null() - ตรวจสอบค่า NULL
$var1 = null;
$var2 = "";
$var3 = 0;
echo "var1 is null: " . (is_null($var1) ? "ใช่" : "ไม่ใช่") . "<br>";
echo "var2 is null: " . (is_null($var2) ? "ใช่" : "ไม่ใช่") . "<br>";
echo "var3 is null: " . (is_null($var3) ? "ใช่" : "ไม่ใช่") . "<br>";
// การใช้ unset() เพื่อลบตัวแปร 
$temp = "ค่าชั่วคราว";
echo "ก่อน unset: " . (isset($temp) ? "มีค่า" : "ไม่มีค่า") . "<br>";
unset($temp);
echo "หลัง unset: " . (isset($temp) ? "มีค่า" : "ไม่มีค่า") . "<br>";