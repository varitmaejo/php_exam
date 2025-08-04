<?php
//ค้นหาสินค้าในอาร์เรย์
$products = ["โทรศัพท์", "แท็บเล็ต", "โน้ตบุ๊ก", "หูฟัง", "เมาส์"];
$search_item = "โน้ตบุ๊ก";
$found = false;

for ($i = 0; $i < count($products); $i++) {
    echo "กำลังค้นหา... ตรวจสอบ: " . $products[$i] . "<br>";
    
    if ($products[$i] == $search_item) {
        echo "พบสินค้า '$search_item' ในตำแหน่งที่ " . ($i + 1);
        $found = true;
        break;
    }
}

if (!$found) {
    echo "ไม่พบสินค้าที่ค้นหา";
}
?>