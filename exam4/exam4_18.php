<?php
//หยุดลูปเมื่อเจอเลข 6
echo "แสดงตัวเลขจนกว่าจะเจอเลข 6:<br>";

for ($i = 1; $i <= 10; $i++) {
    if ($i == 6) {
        echo "เจอเลข 6 แล้ว หยุดลูป!";
        break;
    }
    echo "$i ";
}
?>