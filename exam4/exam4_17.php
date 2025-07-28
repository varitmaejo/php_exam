<?php
//เกมทายตัวเลข
$secret_number = 7;
$guess = 0;
$tries = 0;

do {
    $guess = rand(1, 10);
    $tries++;
    
    if ($guess < $secret_number) {
        echo "ครั้งที่ $tries: ทาย $guess - น้อยเกินไป<br>";
    } else if ($guess > $secret_number) {
        echo "ครั้งที่ $tries: ทาย $guess - มากเกินไป<br>";
    } else {
        echo "ครั้งที่ $tries: ทาย $guess - ถูกต้อง!<br>";
    }
    
} while ($guess != $secret_number);

echo "ทายถูกใน $tries ครั้ง!";
?>