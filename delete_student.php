<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$student_id = $_GET['id'];

try {
    // ดึงข้อมูลรูปภาพก่อนลบ
    $stmt = $pdo->prepare("SELECT student_photo FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();

    // ลบข้อมูลจากฐานข้อมูล
    $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);

    // ลบไฟล์รูปภาพ
    if ($student && $student['student_photo'] && file_exists('uploads/' . $student['student_photo'])) {
        unlink('uploads/' . $student['student_photo']);
    }

    header("Location: list_students.php");
} catch (PDOException $e) {
    header("Location: list_students");
}
