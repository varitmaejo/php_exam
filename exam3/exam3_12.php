<?php
//empty() - ตรวจสอบค่าว่าง
$var1 = ""; // สตริงว่าง 
$var2 = 0; // ศูนย์ 
$var3 = "0"; // สตริงศูนย์ 
$var4 = null; // null 
$var5 = false; // false 
$var6 = array(); // อาร์เรย์ว่าง 
$var7 = "Hello"; // มีค่า 
$vars = [$var1, $var2, $var3, $var4, $var5, $var6, $var7];
foreach ($vars as $i => $var) {
    $num = $i + 1;
    echo "var$num: " . (empty($var) ? "ว่าง" : "มีค่า") . "<br>";
}