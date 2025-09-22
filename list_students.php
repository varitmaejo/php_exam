<?php
// ไฟล์: list_students.php
$page_title = "รายการนักศึกษา";
require_once 'config.php';
require_once 'includes/header.php';
?>

<h2>รายการนักศึกษา</h2>
<a href="add_student.php" class="btn btn-success mb-3">เพิ่มนักศึกษาใหม่</a>

<?php
try {
    $query = "SELECT s.*, c.curriculum_name 
             FROM students s 
             LEFT JOIN curriculum c ON s.curriculum_id = c.curriculum_id 
             ORDER BY s.student_id";
    $stmt = $pdo->query($query);
    $students = $stmt->fetchAll();
    
    if (count($students) > 0) {
        ?>
        <table class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th>รหัส</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>เพศ</th>
                    <th>หลักสูตร</th>
                    <th>ห้องเรียน</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?= htmlspecialchars($student['student_id']) ?></td>
                        <td><?= htmlspecialchars($student['fullname']) ?></td>
                        <td><?= htmlspecialchars($student['gender']) ?></td>
                        <td><?= htmlspecialchars($student['curriculum_name']) ?></td>
                        <td><?= htmlspecialchars($student['class_room']) ?></td>
                        <td>
                            <a href="view_student.php?id=<?= urlencode($student['student_id']) ?>" 
                               class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                            <a href="edit_student.php?id=<?= urlencode($student['student_id']) ?>" 
                               class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i></a>
                            <a href="delete_student.php?id=<?= urlencode($student['student_id']) ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('ยืนยันการลบ?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="alert alert-info">
            รวม: <?= count($students) ?> คน
        </div>
        <?php
    } else {
        ?>
        <div class="alert alert-warning">
            ไม่พบข้อมูลนักศึกษา
        </div>
        <?php
    }
} catch(PDOException $e) {
    ?>
    <div class="alert alert-danger">
        ข้อผิดพลาด: <?= htmlspecialchars($e->getMessage()) ?>
    </div>
    <?php
}
?>

<?php require_once 'includes/footer.php'; ?>