<?php
//สร้างตารางสูตรคูณ
$number = 7;
echo "ตารางสูตรคูณ $number:<br>";

for ($i = 1; $i <= 12; $i++) {
    $result = $number * $i;
    echo "$number × $i = $result<br>";
}
?>