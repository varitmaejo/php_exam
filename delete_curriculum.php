<?php
// ไฟล์: delete_curriculum.php
$page_title = "ลบหลักสูตร";
require_once 'config.php';

$curriculum = null;
$curriculum_id = isset($_GET['id']) ? $_GET['id'] : '';
$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : '';
$message = '';

if (empty($curriculum_id)) {
    header("Location: list_curriculum.php");
    exit();
}

// ดึงข้อมูลหลักสูตร
try {
    $stmt = $pdo->prepare("SELECT * FROM curriculum WHERE curriculum_id = ?");
    $stmt->execute([$curriculum_id]);
    $curriculum = $stmt->fetch();
    
    if (!$curriculum) {
        header("Location: list_curriculum.php");
        exit();
    }
    
    // ตรวจสอบว่ามีนักศึกษาในหลักสูตรนี้หรือไม่
    $check_students = $pdo->prepare("SELECT COUNT(*) FROM students WHERE curriculum_id = ?");
    $check_students->execute([$curriculum_id]);
    $student_count = $check_students->fetchColumn();
    
} catch(PDOException $e) {
    header("Location: list_curriculum.php");
    exit();
}

// ดำเนินการลบ
if ($confirm == 'yes' && $curriculum) {
    if ($student_count > 0) {
        $message = '<div class="alert alert-danger">ไม่สามารถลบหลักสูตรนี้ได้ เนื่องจากมีนักศึกษา ' . $student_count . ' คน ในหลักสูตรนี้</div>';
    } else {
        try {
            $delete = $pdo->prepare("DELETE FROM curriculum WHERE curriculum_id = ?");
            if ($delete->execute([$curriculum_id])) {
                header("Location: list_curriculum.php?deleted=1");
                exit();
            }
        } catch(PDOException $e) {
            $message = '<div class="alert alert-danger">เกิดข้อผิดพลาด: ' . $e->getMessage() . '</div>';
        }
    }
}

require_once 'includes/header.php';
?>

<h2>ลบหลักสูตร</h2>

<?= $message ?>

<?php if ($curriculum): ?>
    <div class="alert alert-warning">
        <h5>คำเตือน</h5>
        <p>คุณกำลังจะลบหลักสูตร การดำเนินการนี้ไม่สามารถยกเลิกได้!</p>
    </div>
    
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5>ข้อมูลที่จะถูกลบ</h5>
        </div>
        <div class="card-body">
            <p><strong>รหัสหลักสูตร:</strong> <?= htmlspecialchars($curriculum['curriculum_id']) ?></p>
            <p><strong>ชื่อหลักสูตร:</strong> <?= htmlspecialchars($curriculum['curriculum_name']) ?></p>
            
            <?php if ($student_count > 0): ?>
                <div class="alert alert-warning">
                    <strong>ไม่สามารถลบได้!</strong><br>
                    หลักสูตรนี้มีนักศึกษา <?= $student_count ?> คน กรุณาย้ายนักศึกษาไปหลักสูตรอื่นก่อน
                </div>
                <a href="search.php?search_curriculum=<?= urlencode($curriculum_id) ?>" class="btn btn-info">
                    ดูรายการนักศึกษาในหลักสูตรนี้
                </a>
            <?php else: ?>
                <div class="alert alert-success">
                    หลักสูตรนี้ไม่มีนักศึกษา สามารถลบได้
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="text-center mt-4">
        <?php if ($student_count == 0): ?>
            <a href="?id=<?= urlencode($curriculum_id) ?>&confirm=yes" 
               class="btn btn-danger" 
               onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบหลักสูตรนี้?')">
               ยืนยันการลบ
            </a>
        <?php endif; ?>
        <a href="view_curriculum.php?id=<?= urlencode($curriculum_id) ?>" class="btn btn-info">
           ดูข้อมูล
        </a>
        <a href="list_curriculum.php" class="btn btn-secondary">
           ยกเลิก
        </a>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>