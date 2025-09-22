<?php
// ไฟล์: view_curriculum.php
$curriculum = null;
$curriculum_id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($curriculum_id)) {
    header("Location: list_curriculum.php");
    exit();
}

require_once 'config.php';

// ดึงข้อมูลหลักสูตร
try {
    $stmt = $pdo->prepare("SELECT * FROM curriculum WHERE curriculum_id = ?");
    $stmt->execute([$curriculum_id]);
    $curriculum = $stmt->fetch();
    
    if (!$curriculum) {
        header("Location: list_curriculum.php");
        exit();
    }
    
    // นับจำนวนนักศึกษาในหลักสูตร
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE curriculum_id = ?");
    $count_stmt->execute([$curriculum_id]);
    $student_count = $count_stmt->fetchColumn();
    
    // ดึงรายการนักศึกษาในหลักสูตร
    $students_stmt = $pdo->prepare("SELECT * FROM students WHERE curriculum_id = ? ORDER BY student_id LIMIT 5");
    $students_stmt->execute([$curriculum_id]);
    $students = $students_stmt->fetchAll();
    
    $page_title = "ข้อมูลหลักสูตร - " . $curriculum['curriculum_name'];
} catch(PDOException $e) {
    die("ข้อผิดพลาด: " . $e->getMessage());
}

require_once 'includes/header.php';
?>

<h2>ข้อมูลหลักสูตร</h2>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5><?= htmlspecialchars($curriculum['curriculum_name']) ?></h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>รหัสหลักสูตร:</strong> <?= htmlspecialchars($curriculum['curriculum_id']) ?></p>
                <p><strong>ชื่อหลักสูตร:</strong> <?= htmlspecialchars($curriculum['curriculum_name']) ?></p>
                <p><strong>จำนวนนักศึกษา:</strong> <?= number_format($student_count) ?> คน</p>
            </div>
        </div>
    </div>
</div>

<?php if ($student_count > 0): ?>
    <div class="card mt-3">
        <div class="card-header">
            <h6>นักศึกษาในหลักสูตรนี้ (<?= min(5, $student_count) ?> คน)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>รหัส</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>เพศ</th>
                            <th>ห้องเรียน</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['student_id']) ?></td>
                                <td>
                                    <a href="view_student.php?id=<?= urlencode($student['student_id']) ?>">
                                        <?= htmlspecialchars($student['fullname']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($student['gender']) ?></td>
                                <td><?= htmlspecialchars($student['class_room']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($student_count > 5): ?>
                <a href="search.php?search_curriculum=<?= urlencode($curriculum_id) ?>" class="btn btn-sm btn-primary">
                    ดูนักศึกษาทั้งหมด (<?= $student_count ?> คน)
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info mt-3">
        ยังไม่มีนักศึกษาในหลักสูตรนี้
    </div>
<?php endif; ?>

<div class="mt-3">
    <a href="edit_curriculum.php?id=<?= urlencode($curriculum['curriculum_id']) ?>" class="btn btn-warning">แก้ไขข้อมูล</a>
    <a href="delete_curriculum.php?id=<?= urlencode($curriculum['curriculum_id']) ?>" class="btn btn-danger">ลบหลักสูตร</a>
    <a href="list_curriculum.php" class="btn btn-secondary">รายการหลักสูตร</a>
    <a href="index.php" class="btn btn-primary">หน้าหลัก</a>
</div>

<?php require_once 'includes/footer.php'; ?>

