<?php
//ตัวดำเนินการเพิ่ม/ลดค่า (Increment/Decrement)
$counter = 5;
echo "ค่าเริ่มต้น: $counter<br>";    // Pre-increment
echo "++counter: " . ++$counter . "<br>";                  // เพิ่มก่อน แล้วใช้ค่า
echo "ค่าปัจจุบัน: $counter<br>";    // Post-increment
echo "counter++: " . $counter++ . "<br>";                  // ใช้ค่าก่อน แล้วเพิ่ม
echo "ค่าหลังเพิ่ม: $counter<br>"; // Pre-decrement
echo "--counter: " . --$counter . "<br>";                  // ลดก่อน แล้วใช้ค่า
// Post-decrement
echo "counter--: " . $counter-- . "<br>"; // ใช้ค่าก่อน แล้วลด
echo "ค่าสุดท้าย: $counter<br>";