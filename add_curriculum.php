<?php
// ไฟล์: add_curriculum.php
$page_title = "เพิ่มหลักสูตร";
require_once 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $curriculum_id = trim($_POST['curriculum_id']);
    $curriculum_name = trim($_POST['curriculum_name']);
    
    if (empty($curriculum_id) || empty($curriculum_name)) {
        $message = '<div class="alert alert-warning">กรุณากรอกข้อมูลให้ครบ</div>';
    } else {
        try {
            // ตรวจสอบรหัสซ้ำ
            $check = $pdo->prepare("SELECT COUNT(*) FROM curriculum WHERE curriculum_id = ?");
            $check->execute([$curriculum_id]);
            
            if ($check->fetchColumn() > 0) {
                $message = '<div class="alert alert-warning">รหัสหลักสูตรนี้มีอยู่แล้ว</div>';
            } else {
                // เพิ่มข้อมูล
                $insert = $pdo->prepare("INSERT INTO curriculum (curriculum_id, curriculum_name) VALUES (?, ?)");
                if ($insert->execute([$curriculum_id, $curriculum_name])) {
                    $message = '<div class="alert alert-success">เพิ่มหลักสูตรสำเร็จ</div>';
                    $curriculum_id = '';
                    $curriculum_name = '';
                } else {
                    $message = '<div class="alert alert-danger">เกิดข้อผิดพลาด</div>';
                }
            }
        } catch(PDOException $e) {
            $message = '<div class="alert alert-danger">ข้อผิดพลาด: ' . $e->getMessage() . '</div>';
        }
    }
}

require_once 'includes/header.php';
?>

<h2>เพิ่มหลักสูตรใหม่</h2>

<?= $message ?>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">รหัสหลักสูตร</label>
                <input type="text" class="form-control" name="curriculum_id" 
                       value="<?= htmlspecialchars($curriculum_id ?? '') ?>" 
                       placeholder="เช่น CS002" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">ชื่อหลักสูตร</label>
                <input type="text" class="form-control" name="curriculum_name" 
                       value="<?= htmlspecialchars($curriculum_name ?? '') ?>" 
                       placeholder="เช่น วิศวกรรมซอฟต์แวร์" required>
            </div>
            
            <button type="submit" class="btn btn-success">เพิ่มหลักสูตร</button>
            <a href="list_curriculum.php" class="btn btn-secondary">ยกเลิก</a>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>