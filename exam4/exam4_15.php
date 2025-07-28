<?php
//สุ่มตัวเลขจนกว่าจะได้เลข 7
$random_number = 0;
$attempts = 0;

while ($random_number != 7) {
    $random_number = rand(1, 10);
    $attempts++;
    echo "ครั้งที่ $attempts: ได้เลข $random_number<br>";
}

echo "สุ่มได้เลข 7 ในครั้งที่ $attempts!";
?>