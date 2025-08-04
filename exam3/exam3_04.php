<?php
//ตัวดำเนินการเปรียบเทียบ (Comparison Operators)
$x = 5;
$y = "5";
$z = 10; 
// การเปรียบเทียบค่า
echo "x = $x, y = '$y', z = $z<br>";
echo "x == y: " . ($x == $y ? 'true' : 'false') . "<br>";
echo "x === y: " . ($x === $y ? 'true' : 'false') . "<br>";
echo "x != y: " . ($x != $y ? 'true' : 'false') . "<br>";
echo "x !== y: " . ($x !== $y ? 'true' : 'false') . "<br>";
echo "x < z: " . ($x < $z ? 'true' : 'false') . "<br>";
echo "x > z: " . ($x > $z ? 'true' : 'false') . "<br>"; 
// Spaceship operator (PHP 7+) 
echo "x <=> z: " . ($x <=> $z) . "<br>";