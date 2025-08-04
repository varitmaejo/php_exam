<?php
//ตัวดำเนินการกำหนดค่า (Assignment Operators)
$score = 80; // กำหนดค่าเริ่มต้น
echo "คะแนนเริ่มต้น: $score<br>";
$score += 10; // เท่ากับ $score = $score + 10
echo "หลังบวก 10: $score<br>";
$score -= 5; // เท่ากับ $score = $score - 5
echo "หลังลบ 5: $score<br>";
$score *= 2; // เท่ากับ $score = $score * 2
echo "หลังคูณ 2: $score<br>";
$score /= 3; // เท่ากับ $score = $score / 3
echo "หลังหาร 3: $score<br>";
$score %= 10; // เท่ากับ $score = $score % 10
echo "เศษจากการหาร 10: $score<br>";