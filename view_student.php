<?php
// ไฟล์: view_student.php - ดูข้อมูลนักศึกษา
$student = null;
$student_id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($student_id)) {
    header("Location: index.php");
    exit();
}

require_once 'config.php';

// ดึงข้อมูลนักศึกษาพร้อมหลักสูตร
try {
    $stmt = $pdo->prepare("
        SELECT s.*, c.curriculum_name 
        FROM students s 
        LEFT JOIN curriculum c ON s.curriculum_id = c.curriculum_id 
        WHERE s.student_id = ?
    ");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        header("Location: index.php?error=" . urlencode("ไม่พบข้อมูลนักศึกษา"));
        exit();
    }
    
    $page_title = "ข้อมูลนักศึกษา - " . $student['fullname'];
} catch(PDOException $e) {
    die("ข้อผิดพลาด: " . $e->getMessage());
}

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h2><i class="bi bi-person-circle"></i> ข้อมูลนักศึกษา</h2>

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                    <li class="breadcrumb-item active">ข้อมูลนักศึกษา</li>
                </ol>
            </nav>

            <!-- ข้อมูลนักศึกษา -->
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge"></i>
                        <?= htmlspecialchars($student['fullname']) ?>
                    </h5>
                    <small>รหัสนักศึกษา: <?= htmlspecialchars($student['student_id']) ?></small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- รูปภาพ -->
                        <div class="col-md-4 text-center mb-3">
                            <?php if (!empty($student['student_photo']) && file_exists('uploads/' . $student['student_photo'])): ?>
                                <img src="uploads/<?= htmlspecialchars($student['student_photo']) ?>" 
                                     alt="ภาพนักศึกษา" class="img-thumbnail shadow-sm" 
                                     style="max-width: 250px; max-height: 300px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light border rounded p-5 d-flex align-items-center justify-content-center" 
                                     style="min-height: 250px;">
                                    <div class="text-center">
                                        <i class="bi bi-person-circle text-muted" style="font-size: 4rem;"></i>
                                        <p class="text-muted mt-2 mb-0">ไม่มีรูปภาพ</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- ข้อมูล -->
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <td width="40%"><strong>รหัสนักศึกษา:</strong></td>
                                                <td><?= htmlspecialchars($student['student_id']) ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>ชื่อ-นามสกุล:</strong></td>
                                                <td><?= htmlspecialchars($student['fullname']) ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>เพศ:</strong></td>
                                                <td>
                                                    <span class="badge <?= $student['gender'] == 'ชาย' ? 'bg-primary' : 'bg-pink' ?>">
                                                        <?= htmlspecialchars($student['gender']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>หลักสูตร:</strong></td>
                                                <td>
                                                    <?php if (!empty($student['curriculum_name'])): ?>
                                                        <a href="view_curriculum.php?id=<?= urlencode($student['curriculum_id']) ?>" 
                                                           class="text-decoration-none">
                                                            <i class="bi bi-mortarboard"></i>
                                                            <?= htmlspecialchars($student['curriculum_name']) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">ไม่ระบุ</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <td width="40%"><strong>ห้อง:</strong></td>
                                                <td>
                                                    <?php if (!empty($student['class_room'])): ?>
                                                        <span class="badge bg-secondary">
                                                            <?= htmlspecialchars($student['class_room']) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">ไม่ระบุ</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>อีเมล:</strong></td>
                                                <td>
                                                    <?php if (!empty($student['email'])): ?>
                                                        <a href="mailto:<?= htmlspecialchars($student['email']) ?>" 
                                                           class="text-decoration-none">
                                                            <i class="bi bi-envelope"></i>
                                                            <?= htmlspecialchars($student['email']) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">ไม่ระบุ</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>เบอร์โทร:</strong></td>
                                                <td>
                                                    <?php if (!empty($student['phone'])): ?>
                                                        <a href="tel:<?= htmlspecialchars($student['phone']) ?>" 
                                                           class="text-decoration-none">
                                                            <i class="bi bi-telephone"></i>
                                                            <?= htmlspecialchars($student['phone']) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">ไม่ระบุ</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>สถานะ:</strong></td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle"></i>
                                                        กำลังศึกษา
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ปุ่มจัดการ -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="card-title">การจัดการข้อมูล</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <!-- ปุ่มแก้ไข -->
                        <a href="edit_student.php?id=<?= urlencode($student['student_id']) ?>" 
                           class="btn btn-warning">
                            <i class="bi bi-pencil-square"></i> แก้ไขข้อมูล
                        </a>
                        
                        <!-- ปุ่มลบ -->
                        <button type="button" class="btn btn-danger" 
                                onclick="confirmDelete()">
                            <i class="bi bi-trash"></i> ลบข้อมูล
                        </button>
                        
                        <!-- ปุ่มอื่นๆ -->
                        <a href="search.php" class="btn btn-secondary">
                            <i class="bi bi-search"></i> ค้นหานักศึกษา
                        </a>
                        
                        <a href="index.php" class="btn btn-primary">
                            <i class="bi bi-house"></i> หน้าหลัก
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete() {
        var studentName = '<?= htmlspecialchars($student['fullname'], ENT_QUOTES) ?>';
        var studentId = '<?= htmlspecialchars($student['student_id'], ENT_QUOTES) ?>';
        
        var confirmMsg = 'คุณแน่ใจหรือไม่ที่จะลบข้อมูลนักศึกษา "' + studentName + '" ?\n\nการลบจะไม่สามารถย้อนกลับได้!';
        var confirmMsg2 = 'ยืนยันการลบอีกครั้ง?\n\nข้อมูลจะถูกลบถาวร!';
        
        if (confirm(confirmMsg)) {
            if (confirm(confirmMsg2)) {
                window.location.href = 'delete_student.php?id=' + encodeURIComponent(studentId);
            }
        }
    }
</script>

<!-- เพิ่ม CSS สำหรับ badge สีชมพู -->
<style>
.bg-pink {
    background-color: #e83e8c !important;
}
</style>

<?php require_once 'includes/footer.php'; ?>