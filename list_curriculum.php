<?php
// ไฟล์: list_curriculum.php
$page_title = "รายการหลักสูตร";
require_once 'config.php';
require_once 'includes/header.php';

// ตรวจสอบการลบสำเร็จ
$deleted = isset($_GET['deleted']) ? $_GET['deleted'] : '';
?>

<h2>รายการหลักสูตร</h2>
<a href="add_curriculum.php" class="btn btn-success mb-3">เพิ่มหลักสูตรใหม่</a>

<?php if ($deleted): ?>
    <div class="alert alert-success">
        ลบหลักสูตรเรียบร้อยแล้ว
    </div>
<?php endif; ?>

<?php
try {
    $query = "SELECT * FROM curriculum ORDER BY curriculum_id";
    $stmt = $pdo->query($query);
    $curricula = $stmt->fetchAll();
    
    if (count($curricula) > 0) {
        ?>
        <table class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th>รหัสหลักสูตร</th>
                    <th>ชื่อหลักสูตร</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($curricula as $curriculum): ?>
                    <tr>
                        <td><?= htmlspecialchars($curriculum['curriculum_id']) ?></td>
                        <td><?= htmlspecialchars($curriculum['curriculum_name']) ?></td>
                        <td>
                            <a href="view_curriculum.php?id=<?= urlencode($curriculum['curriculum_id']) ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                            <a href="edit_curriculum.php?id=<?= urlencode($curriculum['curriculum_id']) ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i></a>
                            <a href="delete_curriculum.php?id=<?= urlencode($curriculum['curriculum_id']) ?>" class="btn btn-sm btn-danger" 
                               onclick="return confirm('ยืนยันการลบ?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="alert alert-info">
            รวม: <?= count($curricula) ?> หลักสูตร
        </div>
        <?php
    } else {
        ?>
        <div class="alert alert-warning">
            ไม่พบข้อมูลหลักสูตร
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