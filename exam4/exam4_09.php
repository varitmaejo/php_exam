<?php
//แสดงชื่อวัน
$day = "Monday";

switch ($day) {
    case "Monday":
        echo "วันนี้คือวันจันทร์";
        break;
    case "Tuesday":
        echo "วันนี้คือวันอังคาร";
        break;
    case "Wednesday":
        echo "วันนี้คือวันพุธ";
        break;
    case "Thursday":
        echo "วันนี้คือวันพฤหัสบดี";
        break;
    case "Friday":
        echo "วันนี้คือวันศุกร์";
        break;
    case "Saturday":
    case "Sunday":
        echo "วันนี้คือวันหยุดสุดสัปดาห์";
        break;
    default:
        echo "ไม่ทราบวัน";
}
?>