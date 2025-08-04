<?php
// ตัวอย่างการใช้คำสั่งรวมกัน
$students = [
    ["name" => "สมชาย", "score" => 85],
    ["name" => "สมหญิง", "score" => 72],
    ["name" => "สมศรี", "score" => 91],
    ["name" => "สมหมาย", "score" => 45],
    ["name" => "สมใจ", "score" => 68]
];

echo "<h3>รายงานผลการเรียน</h3>";

for ($i = 0; $i < count($students); $i++) {
    $name = $students[$i]["name"];
    $score = $students[$i]["score"];

    // ใช้ switch สำหรับกำหนดเกรด
    $grade = "";
    switch (true) {
        case ($score >= 80):
            $grade = "A";
            break;
        case ($score >= 70):
            $grade = "B";
            break;
        case ($score >= 60):
            $grade = "C";
            break;
        case ($score >= 50):
            $grade = "D";
            break;
        default:
            $grade = "F";
    }

    // ข้ามการแสดงผลถ้าเกรด F
    if ($grade == "F") {
        echo "$name: คะแนน $score (เกรด $grade) - ต้องสอบแก้<br>";
        continue;
    }

    // แสดงผลการเรียน
    echo "$name: คะแนน $score (เกรด $grade)";

    // เพิ่มข้อความแสดงความยินดี
    if ($score >= 90) {
        echo " - ยอดเยี่ยม!";
    } else if ($score >= 80) {
        echo " - ดีมาก!";
    }

    echo "<br>";
}

// คำนวณคะแนนเฉลี่ย
$total_score = 0;
$student_count = count($students);

for ($i = 0; $i < $student_count; $i++) {
    $total_score += $students[$i]["score"];
}

$average = $total_score / $student_count;
echo "<br>คะแนนเฉลี่ยของห้อง: " . round($average, 2);

// ประเมินผลรวม
if ($average >= 80) {
    echo " - นักเรียนมีผลการเรียนดีมาก";
} else if ($average >= 70) {
    echo " - นักเรียนมีผลการเรียนดี";
} else if ($average >= 60) {
    echo " - นักเรียนมีผลการเรียนปานกลาง";
} else {
    echo " - ควรปรับปรุงการเรียนการสอน";
}