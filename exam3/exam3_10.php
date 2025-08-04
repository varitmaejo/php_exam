<?php
//gettype() - ตรวจสอบชนิดข้อมูล
$var1 = 42;
$var2 = "Hello";
$var3 = 3.14;
$var4 = true;
$var5 = ["apple", "banana"];
$var6 = null;
echo gettype($var1) . "<br>"; // integer
echo gettype($var2) . "<br>"; // string
echo gettype($var3) . "<br>"; // double
echo gettype($var4) . "<br>"; // boolean
echo gettype($var5) . "<br>"; // array
echo gettype($var6) . "<br>"; // NULL