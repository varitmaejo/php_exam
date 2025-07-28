<?php
//print - แสดงผลแบบคืนค่า
// print คืนค่า 1 เสมอ 
$result = print "Hello World!";
echo "<br>ค่าที่คืน: $result<br>"; // แสดงผลก่อน 
print("ข้อความนี้จะแสดงและคืนค่า 1");
echo "<br>"; // จากนั้นคำนวณแยกต่างหาก 
$output = 1 + 5; // เพราะ print คืนค่า 1 เสมอ 
echo "ผลลัพธ์: $output<br>";