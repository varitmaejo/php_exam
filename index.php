<?php
/**
 * ไฟล์: index.php (อัปเดตให้รองรับระบบสิทธิ์)
 * ฟังก์ชัน: หน้าหลักที่รองรับทั้งผู้ใช้ที่ล็อกอินและไม่ล็อกอิน
 */

$page_title = "หน้าหลัก";
require_once 'config.php';
require_once 'auth_config.php';

// ตรวจสอบ session timeout (ถ้าล็อกอินอยู่)
if (isLoggedIn()) {
    checkSessionTimeout();
}

/**
 * ดึงข้อมูลสถิติจากฐานข้อมูล
 * เพื่อแสดงภาพรวมของระบบ
 */
try {
    // นับจำนวนนักศึกษาทั้งหมดในระบบ
    $total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    
    // นับจำนวนหลักสูตรทั้งหมดในระบบ
    $total_curriculum = $pdo->query("SELECT COUNT(*) FROM curriculum")->fetchColumn();

    // นับจำนวนนักศึกษาแยกตามเพศ
    $male_students = $pdo->query("SELECT COUNT(*) FROM students WHERE gender = 'ชาย'")->fetchColumn();
    $female_students = $pdo->query("SELECT COUNT(*) FROM students WHERE gender = 'หญิง'")->fetchColumn();
    
    // ข้อมูลสถิติเพิ่มเติมสำหรับผู้ที่ล็อกอิน
    if (hasPermission('faculty_staff')) {
        $recent_students = $pdo->query("
            SELECT s.*, c.curriculum_name 
            FROM students s 
            LEFT JOIN curriculum c ON s.curriculum_id = c.curriculum_id 
            ORDER BY s.student_id DESC 
            LIMIT 5
        ")->fetchAll();
    }
    
} catch (PDOException $e) {
    // กรณีเกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล ให้ตั้งค่าเป็น 0
    $total_students = 0;
    $total_curriculum = 0;
    $male_students = 0;
    $female_students = 0;
    $recent_students = [];
}

// ตรวจสอบว่ามีการส่งข้อมูลการลบมาหรือไม่ (จากหน้าอื่น)
$deleted = isset($_GET['deleted']) ? $_GET['deleted'] : '';

// โหลด header template
require_once 'includes/protected_header.php';
?>

<div class="container mt-4">
    <!-- ส่วนหัวหลัก (Hero Section) -->
    <div class="row mb-5">
        <div class="col-12">
            <!-- การ์ดหลักแสดงชื่อระบบและปุ่มหลัก -->
            <div class="bg-primary text-white rounded p-5 shadow">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <!-- ชื่อระบบและคำอธิบาย -->
                        <h1 class="display-4 fw-bold mb-3">
                            <i class="bi bi-mortarboard-fill me-3"></i>
                            ระบบจัดการข้อมูลนักศึกษา
                        </h1>
                        <p class="lead mb-4">
                            คณะวิทยาศาสตร์และเทคโนโลยี
                            <?php if (isLoggedIn()): ?>
                                <br><small>ยินดีต้อนรับ <?= htmlspecialchars($_SESSION['full_name']) ?> 
                                (<?= getRoleDisplayName($_SESSION['user_role']) ?>)</small>
                            <?php endif; ?>
                        </p>
                        <!-- ปุ่มการดำเนินการหลัก -->
                        <div class="d-flex flex-wrap gap-3">
                            <?php if (hasPermission('faculty_staff')): ?>
                                <a href="add_student.php" class="btn btn-warning btn-lg px-4 py-3">
                                    <i class="bi bi-plus-circle me-2"></i>เพิ่มนักศึกษาใหม่
                                </a>
                                <a href="statistics.php" class="btn btn-light btn-lg px-4 py-3">
                                    <i class="bi bi-bar-chart me-2"></i>ดูสถิติ
                                </a>
                            <?php else: ?>
                                <a href="list_students.php" class="btn btn-light btn-lg px-4 py-3">
                                    <i class="bi bi-list me-2"></i>ดูรายการนักศึกษา
                                </a>
                                <a href="search.php" class="btn btn-warning btn-lg px-4 py-3">
                                    <i class="bi bi-search me-2"></i>ค้นหาข้อมูล
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-4 text-center">
                        <!-- ไอคอนประกอบส่วนหัว -->
                        <i class="bi bi-people-fill display-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- แสดงข้อความเมื่อมีการลบข้อมูล -->
    <?php if ($deleted): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            ลบข้อมูลเรียบร้อยแล้ว
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ส่วนสถิติพื้นฐาน -->
    <div class="row mb-5">
        <div class="col-12 mb-4">
            <h2 class="text-center">
                <i class="bi bi-bar-chart text-primary"></i>
                สถิติทั่วไป
            </h2>
        </div>
        
        <!-- การ์ดสถิติจำนวนนักศึกษาทั้งหมด -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-center border-0 shadow">
                <div class="card-body">
                    <div class="text-primary mb-3">
                        <i class="bi bi-people-fill display-4"></i>
                    </div>
                    <h3 class="text-primary fw-bold"><?= number_format($total_students) ?></h3>
                    <p class="text-muted mb-0">นักศึกษาทั้งหมด</p>
                </div>
            </div>
        </div>

        <!-- การ์ดสถิติจำนวนหลักสูตร -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-center border-0 shadow">
                <div class="card-body">
                    <div class="text-success mb-3">
                        <i class="bi bi-book-fill display-4"></i>
                    </div>
                    <h3 class="text-success fw-bold"><?= number_format($total_curriculum) ?></h3>
                    <p class="text-muted mb-0">หลักสูตร</p>
                </div>
            </div>
        </div>

        <!-- การ์ดสถิติจำนวนนักศึกษาชาย -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-center border-0 shadow">
                <div class="card-body">
                    <div class="text-info mb-3">
                        <i class="bi bi-person-fill display-4"></i>
                    </div>
                    <h3 class="text-info fw-bold"><?= number_format($male_students) ?></h3>
                    <p class="text-muted mb-0">นักศึกษาชาย</p>
                    <small class="text-muted">
                        <?= $total_students > 0 ? round(($male_students / $total_students) * 100, 1) : 0 ?>% ของทั้งหมด
                    </small>
                </div>
            </div>
        </div>

        <!-- การ์ดสถิติจำนวนนักศึกษาหญิง -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-center border-0 shadow">
                <div class="card-body">
                    <div class="text-danger mb-3">
                        <i class="bi bi-person-fill display-4"></i>
                    </div>
                    <h3 class="text-danger fw-bold"><?= number_format($female_students) ?></h3>
                    <p class="text-muted mb-0">นักศึกษาหญิง</p>
                    <small class="text-muted">
                        <?= $total_students > 0 ? round(($female_students / $total_students) * 100, 1) : 0 ?>% ของทั้งหมด
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- ส่วนเมนูระบบงานหลัก -->
    <div class="row mb-5">
        <div class="col-12 mb-4">
            <h2 class="text-center mb-4">
                <i class="bi bi-grid-3x3-gap text-primary"></i>
                ระบบงานหลัก
            </h2>
        </div>
        
        <!-- การ์ดจัดการข้อมูลนักศึกษา -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 shadow border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <i class="bi bi-people-fill display-4 mb-3"></i>
                    <h5 class="fw-bold mb-0">จัดการข้อมูลนักศึกษา</h5>
                </div>
                <div class="card-body text-center p-4">
                    <p class="text-muted mb-4">
                        <?php if (hasPermission('faculty_staff')): ?>
                            เพิ่ม แก้ไข ลบ และค้นหาข้อมูลนักศึกษา
                        <?php else: ?>
                            ดูและค้นหาข้อมูลนักศึกษา
                        <?php endif; ?>
                    </p>
                    <div class="d-grid gap-2">
                        <a href="list_students.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-list me-2"></i>รายการนักศึกษา
                        </a>
                        <?php if (hasPermission('faculty_staff')): ?>
                            <a href="add_student.php" class="btn btn-success">
                                <i class="bi bi-plus-circle me-2"></i>เพิ่มนักศึกษา
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- การ์ดจัดการหลักสูตร -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 shadow border-0">
                <div class="card-header bg-success text-white text-center py-4">
                    <i class="bi bi-book-fill display-4 mb-3"></i>
                    <h5 class="fw-bold mb-0">หลักสูตร</h5>
                </div>
                <div class="card-body text-center p-4">
                    <p class="text-muted mb-4">
                        <?php if (hasPermission('faculty_staff')): ?>
                            จัดการข้อมูลหลักสูตรการเรียน
                        <?php else: ?>
                            ดูข้อมูลหลักสูตรการเรียน
                        <?php endif; ?>
                    </p>
                    <div class="d-grid gap-2">
                        <a href="list_curriculum.php" class="btn btn-success btn-lg">
                            <i class="bi bi-book me-2"></i>รายการหลักสูตร
                        </a>
                        <?php if (hasPermission('faculty_staff')): ?>
                            <a href="add_curriculum.php" class="btn btn-outline-success">
                                <i class="bi bi-plus-circle me-2"></i>เพิ่มหลักสูตร
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- การ์ดค้นหาและรายงาน -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 shadow border-0">
                <div class="card-header bg-info text-white text-center py-4">
                    <i class="bi bi-search display-4 mb-3"></i>
                    <h5 class="fw-bold mb-0">ค้นหาและรายงาน</h5>
                </div>
                <div class="card-body text-center p-4">
                    <p class="text-muted mb-4">ค้นหาข้อมูลและดูสถิติต่างๆ</p>
                    <div class="d-grid gap-2">
                        <a href="search.php" class="btn btn-info btn-lg">
                            <i class="bi bi-search me-2"></i>ค้นหาข้อมูล
                        </a>
                        <?php if (hasPermission('faculty_staff')): ?>
                            <a href="statistics.php" class="btn btn-outline-info">
                                <i class="bi bi-bar-chart me-2"></i>สถิติและรายงาน
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (hasPermission('faculty_staff') && !empty($recent_students)): ?>
    <!-- ส่วนนักศึกษาที่เพิ่มล่าสุด (เฉพาะผู้ที่มีสิทธิ์) -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>นักศึกษาที่เพิ่มล่าสุด
                    </h5>
                    <small class="text-muted">แสดง 5 คนล่าสุด</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($recent_students as $student): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                             style="width: 40px; height: 40px;">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($student['student_name']) ?></h6>
                                            <small class="text-muted">รหัส: <?= htmlspecialchars($student['student_id']) ?></small>
                                        </div>
                                    </div>
                                    <div class="text-muted small">
                                        <div>หลักสูตร: <?= htmlspecialchars($student['curriculum_name'] ?: 'ไม่ระบุ') ?></div>
                                        <div>เพศ: <?= htmlspecialchars($student['gender']) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="list_students.php" class="btn btn-sm btn-outline-primary">
                            ดูรายการทั้งหมด
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (hasPermission('admin')): ?>
    <!-- ส่วนเครื่องมือผู้ดูแลระบบ -->
    <div class="row mb-5">
        <div class="col-12 mb-4">
            <h3 class="text-center">
                <i class="bi bi-shield-lock text-danger"></i>
                เครื่องมือผู้ดูแลระบบ
            </h3>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <i class="bi bi-people text-danger display-4 mb-2"></i>
                    <h6>จัดการผู้ใช้</h6>
                    <a href="manage_users.php" class="btn btn-danger btn-sm">เข้าจัดการ</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="bi bi-clipboard-data text-warning display-4 mb-2"></i>
                    <h6>บันทึกกิจกรรม</h6>
                    <a href="activity_logs.php" class="btn btn-warning btn-sm">ดูบันทึก</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="bi bi-archive text-info display-4 mb-2"></i>
                    <h6>สำรองข้อมูล</h6>
                    <a href="backup.php" class="btn btn-info btn-sm">สำรองข้อมูล</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-secondary">
                <div class="card-body text-center">
                    <i class="bi bi-sliders text-secondary display-4 mb-2"></i>
                    <h6>ตั้งค่าระบบ</h6>
                    <a href="system_settings.php" class="btn btn-secondary btn-sm">ตั้งค่า</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ส่วนปุ่มการทำงานและนำทางหลัก -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="card-title">การจัดการข้อมูล</h6>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <a href="list_students.php" class="btn btn-primary">
                            <i class="bi bi-list"></i> รายการนักศึกษา
                        </a>
                        <?php if (hasPermission('faculty_staff')): ?>
                            <a href="add_student.php" class="btn btn-success">
                                <i class="bi bi-plus-circle"></i> เพิ่มนักศึกษา
                            </a>
                            <a href="list_curriculum.php" class="btn btn-info">
                                <i class="bi bi-book"></i> จัดการหลักสูตร
                            </a>
                        <?php endif; ?>
                        <a href="search.php" class="btn btn-warning">
                            <i class="bi bi-search"></i> ค้นหาข้อมูล
                        </a>
                        <?php if (!isLoggedIn()): ?>
                            <a href="login.php" class="btn btn-outline-primary">
                                <i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>

<?php require_once 'includes/footer.php'; ?>