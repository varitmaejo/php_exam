<?php
// ไฟล์: edit_curriculum.php
$page_title = "แก้ไขหลักสูตร";
require_once 'config.php';

$message = '';
$curriculum = null;
$curriculum_id = isset($_GET['id']) ? $_GET['id'] : '';

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
} catch(PDOException $e) {
    $message = '<div class="alert alert-danger">ไม่พบข้อมูล</div>';
}

// ประมวลผลการแก้ไข
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $curriculum) {
    $curriculum_name = trim($_POST['curriculum_name']);
    
    if (empty($curriculum_name)) {
        $message = '<div class="alert alert-warning">กรุณากรอกชื่อหลักสูตร</div>';
    } else {
        try {
            $update = $pdo->prepare("UPDATE curriculum SET curriculum_name = ? WHERE curriculum_id = ?");
            if ($update->execute([$curriculum_name, $curriculum_id])) {
                $message = '<div class="alert alert-success">แก้ไขข้อมูลสำเร็จ</div>';
                // ดึงข้อมูลใหม่
                $stmt->execute([$curriculum_id]);
                $curriculum = $stmt->fetch();
            } else {
                $message = '<div class="alert alert-danger">เกิดข้อผิดพลาด</div>';
            }
        } catch(PDOException $e) {
            $message = '<div class="alert alert-danger">ข้อผิดพลาด: ' . $e->getMessage() . '</div>';
        }
    }
}

require_once 'includes/header.php';
?>

<h2>แก้ไขหลักสูตร</h2>

<?php if ($curriculum): ?>
    <div class="alert alert-info">
        <strong>รหัสหลักสูตร:</strong> <?= htmlspecialchars($curriculum['curriculum_id']) ?>
    </div>
    
    <?= $message ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">รหัสหลักสูตร</label>
                    <input type="text" class="form-control" 
                           value="<?= htmlspecialchars($curriculum['curriculum_id']) ?>" 
                           disabled>
                    <div class="form-text">รหัสหลักสูตรไม่สามารถแก้ไขได้</div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">ชื่อหลักสูตร *</label>
                    <input type="text" class="form-control" name="curriculum_name" 
                           value="<?= htmlspecialchars($curriculum['curriculum_name']) ?>" required>
                </div>
                
                <button type="submit" class="btn btn-warning">บันทึกการแก้ไข</button>
                <a href="view_curriculum.php?id=<?= urlencode($curriculum_id) ?>" class="btn btn-info">ดูข้อมูล</a>
                <a href="list_curriculum.php" class="btn btn-secondary">ยกเลิก</a>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>