<?php
//ค่าคงที่ (Constants)
// การกำหนดค่าคงที่ด้วย define()
define("SITE_NAME", "เว็บไซต์ของฉัน");
define("VERSION", "1.0.0");
define("DEBUG_MODE", true);
define("MAX_USERS", 1000);
// การใช้ค่าคงที่
echo "ชื่อไซต์: " . SITE_NAME . "<br>";
echo "เวอร์ชัน: " . VERSION . "<br>";
echo "จำนวนผู้ใช้สูงสุด: " . MAX_USERS . "<br>";
// การตรวจสอบค่าคงที่ด้วย defined()
if (defined("DEBUG_MODE")) {
    echo "โหมดดีบัก: " . (DEBUG_MODE ? "เปิด" : "ปิด") . "<br>";
} else {
    echo "ไม่ได้กำหนดโหมดดีบัก<br>";
}
// การใช้ const (PHP 5.3+)
const PI = 3.14159;
const GREETING = "สวัสดี";
echo "ค่า PI: " . PI . "<br>";
echo GREETING . " ทุกคน!<br>";