<?php
//ตัวดำเนินการทางตรรกศาสตร์ (Logical Operators)
$age     = 20;
$hasLicense    = true;
$hasExperience = false;
// ตัวดำเนินการ AND
if ($age >= 18 && $hasLicense) {
    echo "สามารถขับรถได้<br>";
}
// ตัวดำเนินการ OR
if ($hasLicense || $hasExperience) {
    echo "มีคุณสมบัติขั้นพื้นฐาน<br>";
}
// ตัวดำเนินการ NOT
if (! $hasExperience) {
    echo "ยังไม่มีประสบการณ์<br>";
}
// ตัวดำเนินการ XOR
$a = true;
$b = false;
echo "XOR result: " . ($a xor $b ? 'true' : 'false') . "<br>";
// ความแตกต่างระหว่าง && กับ and
$result1 = true && false;
$result2 = true and false;
echo "&& precedence: " . ($result1 ? 'true' : 'false') . "<br>";
echo "and precedence: " . ($result2 ? 'true' : 'false') . "<br>";