<?php
//ระบบคำนวณคะแนนสอบ
// กำหนดค่าคงที่ 
define("FULL_SCORE", 100);
define("PASS_SCORE", 60);
// ข้อมูลนักเรียน 
$students = [["name" => "สมชาย", "score" => 85], ["name" => "สมหญิง", "score" => 72], ["name" => "สมศักดิ์", "score" => 45], ["name" => "สมใจ", "score" => 90]];
$totalScore = 0;
$passCount = 0;
$failCount = 0;
echo "=== ผลการสอบ ===<br>";
echo "คะแนนเต็ม: " . FULL_SCORE . " คะแนนผ่าน: " . PASS_SCORE . "<br><br>";
foreach ($students as $student) {
    $score = $student["score"];
    $percentage = ($score / FULL_SCORE) * 100;
    $status = ($score >= PASS_SCORE) ? "ผ่าน" : "ไม่ผ่าน";
    $grade = "";
    // คำนวณเกรด 
    if ($score >= 80) {
        $grade = "A";
    } elseif ($score >= 70) {
        $grade = "B";
    } elseif ($score >= 60) {
        $grade = "C";
    } elseif ($score >= 50) {
        $grade = "D";
    } else {
        $grade = "F";
    }
    echo "ชื่อ: " . $student["name"] . " | คะแนน: " . $score . " | เปอร์เซ็นต์: " . number_format($percentage, 2) . "%" . " | เกรด: " . $grade . " | สถานะ: " . $status . "<br>";
    $totalScore += $score;
    if ($score >= PASS_SCORE) {
        $passCount++;
    } else {
        $failCount++;
    }
}
$average = $totalScore / count($students);
$passRate = ($passCount / count($students)) * 100;
echo "<br>=== สรุปผล ===<br>";
echo "จำนวนนักเรียนทั้งหมด: " . count($students) . " คน<br>";
echo "ผ่าน: " . $passCount . " คน | ไม่ผ่าน: " . $failCount . " คน<br>";
echo "คะแนนเฉลี่ย: " . number_format($average, 2) . " คะแนน<br>";
echo "อัตราผ่าน: " . number_format($passRate, 2) . "%<br>";