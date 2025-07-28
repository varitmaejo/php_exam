<?php
//ระบบส่วนลดสินค้า
$total_amount = 1500;

if ($total_amount >= 2000) {
    $discount = 20;
    echo "ได้รับส่วนลด 20%";
} else if ($total_amount >= 1000) {
    $discount = 10;
    echo "ได้รับส่วนลด 10%";
} else if ($total_amount >= 500) {
    $discount = 5;
    echo "ได้รับส่วนลด 5%";
} else {
    $discount = 0;
    echo "ไม่ได้รับส่วนลด";
}

$final_amount = $total_amount - ($total_amount * $discount / 100);
echo "<br>ยอดที่ต้องชำระ: $final_amount บาท";