<?php
//ตรวจสอบสิทธิ์การเข้าใช้งาน
$age = 25;
$is_member = true;
$membership_type = "premium";

if ($age >= 18) {
    echo "อายุเพียงพอ<br>";

    if ($is_member) {
        echo "เป็นสมาชิก<br>";

        if ($membership_type == "premium") {
            echo "สามารถเข้าใช้งานพิเศษทั้งหมดได้";
        } else {
            echo "สามารถเข้าใช้งานขั้นพื้นฐานได้";
        }
    } else {
        echo "กรุณาสมัครสมาชิกก่อน";
    }
} else {
    echo "อายุไม่เพียงพอ ต้องมีอายุ 18 ปีขึ้นไป";
}