<?php
// กำหนดชื่อหน้าเว็บ
$page_title = "ค้นหานักศึกษา";

// เชื่อมต่อกับไฟล์การตั้งค่าฐานข้อมูล
require_once 'config.php';

/* ===== ส่วนที่ 1: ดึงข้อมูลหลักสูตรสำหรับ dropdown ===== */
try {
    // SQL Query สำหรับดึงข้อมูลหลักสูตรทั้งหมด เรียงตามชื่อ
    $stmt = $pdo->query("SELECT * FROM curriculum ORDER BY curriculum_name");
    // เก็บผลลัพธ์ในตัวแปร array
    $curriculums = $stmt->fetchAll();
} catch(PDOException $e) {
    // แสดงข้อผิดพลาดหากเกิดปัญหาในการเชื่อมต่อฐานข้อมูล
    die("ข้อผิดพลาด: " . $e->getMessage());
}

/* ===== ส่วนที่ 2: รับค่าจากฟอร์มค้นหา ===== */
// รับค่าคำค้นหาจาก URL parameter หรือใช้ค่าว่างถ้าไม่มี
$search_text = $_GET['search_text'] ?? '';
// รับค่าหลักสูตรที่เลือก
$search_curriculum = $_GET['search_curriculum'] ?? '';
// รับค่าเพศที่เลือก
$search_gender = $_GET['search_gender'] ?? '';
// รับค่าห้องเรียนที่ค้นหา
$search_room = $_GET['search_room'] ?? '';

// ตัวแปรสำหรับเก็บผลการค้นหา
$students = [];
// ตัวแปรตรวจสอบว่ามีการค้นหาหรือไม่
$search_performed = false;

/* ===== ส่วนที่ 3: การประมวลผลการค้นหา ===== */
// ตรวจสอบว่ามีการกรอกเงื่อนไขการค้นหาอย่างน้อย 1 ข้อหรือไม่
if (!empty($search_text) || !empty($search_curriculum) || !empty($search_gender) || !empty($search_room)) {
    // กำหนดสถานะว่ามีการค้นหาแล้ว
    $search_performed = true;
    
    try {
        /* สร้าง Dynamic SQL Query สำหรับค้นหา */
        // SQL พื้นฐาน - JOIN ตาราง students กับ curriculum
        $sql = "SELECT s.*, c.curriculum_name 
                FROM students s 
                LEFT JOIN curriculum c ON s.curriculum_id = c.curriculum_id 
                WHERE 1=1"; // WHERE 1=1 เพื่อให้สามารถต่อ AND ได้เสมอ
        
        // Array สำหรับเก็บ parameters ที่จะส่งให้ prepared statement
        $params = [];

        /* ===== เงื่อนไขการค้นหาตามข้อความ ===== */
        if (!empty($search_text)) {
            // ค้นหาในหลายคอลัมน์: รหัสนักศึกษา, ชื่อ, อีเมล, เบอร์โทร
            $sql .= " AND (s.student_id LIKE ? OR s.fullname LIKE ? OR s.email LIKE ? OR s.phone LIKE ?)";
            // เพิ่ม % ข้างหน้าและข้างหลังเพื่อค้นหาแบบ partial match
            $search_param = "%$search_text%";
            // เพิ่ม parameter เดียวกัน 4 ครั้งสำหรับ 4 คอลัมน์
            $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
        }

        /* ===== เงื่อนไขการค้นหาตามหลักสูตร ===== */
        if (!empty($search_curriculum)) {
            $sql .= " AND s.curriculum_id = ?";
            $params[] = $search_curriculum;
        }

        /* ===== เงื่อนไขการค้นหาตามเพศ ===== */
        if (!empty($search_gender)) {
            $sql .= " AND s.gender = ?";
            $params[] = $search_gender;
        }

        /* ===== เงื่อนไขการค้นหาตามห้องเรียน ===== */
        if (!empty($search_room)) {
            // ใช้ LIKE เพื่อค้นหาแบบ partial match
            $sql .= " AND s.class_room LIKE ?";
            $params[] = "%$search_room%";
        }

        // เรียงลำดับผลลัพธ์ตามรหัสนักศึกษา
        $sql .= " ORDER BY s.student_id";

        // เตรียม SQL statement และ execute พร้อม parameters
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        // เก็บผลลัพธ์ทั้งหมดใน array
        $students = $stmt->fetchAll();
        
    } catch(PDOException $e) {
        // เก็บข้อความผิดพลาดเพื่อแสดงให้ผู้ใช้
        $error_message = "เกิดข้อผิดพลาดในการค้นหา: " . $e->getMessage();
    }
}

// รวมไฟล์ header ของเว็บไซต์
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <!-- ===== ส่วนหัวหน้า ===== -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><i class="bi bi-search"></i> ค้นหานักศึกษา</h2>
                <!-- ปุ่มกลับหน้าหลัก -->
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-house"></i> กลับหน้าหลัก
                </a>
            </div>

            <!-- ===== Breadcrumb Navigation ===== -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                    <li class="breadcrumb-item active">ค้นหานักศึกษา</li>
                </ol>
            </nav>

            <!-- ===== แสดงข้อผิดพลาด (ถ้ามี) ===== -->
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle"></i>
                    <?= htmlspecialchars($error_message) ?> <!-- ป้องกัน XSS ด้วย htmlspecialchars -->
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- ===== ฟอร์มค้นหา ===== -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-funnel"></i> เงื่อนไขการค้นหา
                    </h5>
                </div>
                <div class="card-body">
                    <!-- ฟอร์มใช้ method GET เพื่อให้ URL สามารถ bookmark ได้ -->
                    <form method="GET" class="row g-3">
                        <!-- ===== ช่องค้นหาข้อความทั่วไป ===== -->
                        <div class="col-md-6">
                            <label for="search_text" class="form-label">
                                <i class="bi bi-search"></i> คำค้นหา
                            </label>
                            <input type="text" class="form-control" id="search_text" name="search_text" 
                                   value="<?= htmlspecialchars($search_text) ?>"
                                   placeholder="รหัสนักศึกษา, ชื่อ-นามสกุล, อีเมล หรือเบอร์โทร">
                            <div class="form-text">สามารถค้นหาด้วยข้อมูลใดก็ได้</div>
                        </div>
                        
                        <!-- ===== Dropdown หลักสูตร ===== -->
                        <div class="col-md-3">
                            <label for="search_curriculum" class="form-label">
                                <i class="bi bi-book"></i> หลักสูตร
                            </label>
                            <select class="form-select" id="search_curriculum" name="search_curriculum">
                                <option value="">ทุกหลักสูตร</option>
                                <?php foreach ($curriculums as $curriculum): ?>
                                    <!-- ตรวจสอบว่าหลักสูตรนี้ถูกเลือกอยู่หรือไม่ -->
                                    <option value="<?= $curriculum['curriculum_id'] ?>"
                                            <?= $search_curriculum == $curriculum['curriculum_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($curriculum['curriculum_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- ===== Dropdown เพศ ===== -->
                        <div class="col-md-3">
                            <label for="search_gender" class="form-label">
                                <i class="bi bi-people"></i> เพศ
                            </label>
                            <select class="form-select" id="search_gender" name="search_gender">
                                <option value="">ทุกเพศ</option>
                                <option value="ชาย" <?= $search_gender == 'ชาย' ? 'selected' : '' ?>>ชาย</option>
                                <option value="หญิง" <?= $search_gender == 'หญิง' ? 'selected' : '' ?>>หญิง</option>
                            </select>
                        </div>
                        
                        <!-- ===== ช่องค้นหาห้องเรียน ===== -->
                        <div class="col-md-6">
                            <label for="search_room" class="form-label">
                                <i class="bi bi-building"></i> ห้องเรียน
                            </label>
                            <input type="text" class="form-control" id="search_room" name="search_room" 
                                   value="<?= htmlspecialchars($search_room) ?>"
                                   placeholder="เช่น CS-01, วท.201">
                            <div class="form-text">ค้นหาตามชื่อห้องเรียน</div>
                        </div>
                        
                        <!-- ===== ปุ่มควบคุม ===== -->
                        <div class="col-md-6 d-flex align-items-end gap-2">
                            <!-- ปุ่มค้นหา -->
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-search"></i> ค้นหา
                            </button>
                            <!-- ปุ่มล้างการค้นหา (แสดงเฉพาะเมื่อมีการค้นหาแล้ว) -->
                            <?php if ($search_performed): ?>
                                <a href="search.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> ล้าง
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ===== ส่วนแสดงผลการค้นหา ===== -->
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> 
                        ผลการค้นหา
                        <!-- แสดงจำนวนผลลัพธ์ถ้ามีการค้นหา -->
                        <?php if ($search_performed): ?>
                            <span class="badge bg-primary"><?= count($students) ?> รายการ</span>
                        <?php endif; ?>
                    </h5>
                    
                    <!-- ปุ่ม Export CSV (แสดงเฉพาะเมื่อมีผลลัพธ์) -->
                    <?php if ($search_performed && count($students) > 0): ?>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="exportToCSV()">
                                <i class="bi bi-download"></i> Export CSV
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!$search_performed): ?>
                        <!-- ===== หน้าเริ่มต้น: ยังไม่มีการค้นหา ===== -->
                        <div class="text-center py-5">
                            <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">ค้นหาข้อมูลนักศึกษา</h5>
                            <p class="text-muted">กรอกเงื่อนไขการค้นหาข้างต้นเพื่อแสดงผลลัพธ์</p>
                        </div>
                    <?php elseif (empty($students)): ?>
                        <!-- ===== กรณีไม่พบผลลัพธ์ ===== -->
                        <div class="text-center py-4">
                            <i class="bi bi-exclamation-circle text-warning" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">ไม่พบข้อมูล</h5>
                            <p class="text-muted">ไม่พบนักศึกษาที่ตรงกับเงื่อนไขการค้นหา</p>
                            <p class="text-muted">ลองเปลี่ยนเงื่อนไขการค้นหาหรือ <a href="search.php">ล้างการค้นหา</a></p>
                        </div>
                    <?php else: ?>
                        <!-- ===== แสดงผลลัพธ์ในรูปแบบตาราง ===== -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="studentsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>รหัสนักศึกษา</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>เพศ</th>
                                        <th>หลักสูตร</th>
                                        <th>ห้อง</th>
                                        <th>อีเมล</th>
                                        <th>เบอร์โทร</th>
                                        <th>การจัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $index => $student): ?>
                                        <tr>
                                            <!-- ลำดับที่ (เริ่มจาก 1) -->
                                            <td><?= $index + 1 ?></td>
                                            <!-- รหัสนักศึกษา -->
                                            <td>
                                                <strong><?= htmlspecialchars($student['student_id']) ?></strong>
                                            </td>
                                            <!-- ชื่อ-นามสกุล (คลิกได้เพื่อดูรายละเอียด) -->
                                            <td>
                                                <a href="view_student.php?id=<?= urlencode($student['student_id']) ?>" 
                                                   class="text-decoration-none">
                                                    <?= htmlspecialchars($student['fullname']) ?>
                                                </a>
                                            </td>
                                            <!-- เพศ (แสดงเป็น badge) -->
                                            <td>
                                                <span class="badge <?= $student['gender'] == 'ชาย' ? 'bg-primary' : 'bg-pink' ?>">
                                                    <?= htmlspecialchars($student['gender']) ?>
                                                </span>
                                            </td>
                                            <!-- หลักสูตร -->
                                            <td>
                                                <?php if (!empty($student['curriculum_name'])): ?>
                                                    <span class="text-decoration-none">
                                                        <i class="bi bi-mortarboard"></i>
                                                        <small><?= htmlspecialchars($student['curriculum_name']) ?></small>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">ไม่ระบุ</span>
                                                <?php endif; ?>
                                            </td>
                                            <!-- ห้องเรียน -->
                                            <td>
                                                <?php if (!empty($student['class_room'])): ?>
                                                    <span class="badge bg-secondary"><?= htmlspecialchars($student['class_room']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">ไม่ระบุ</span>
                                                <?php endif; ?>
                                            </td>
                                            <!-- อีเมล (คลิกเพื่อส่งอีเมล) -->
                                            <td>
                                                <?php if (!empty($student['email'])): ?>
                                                    <a href="mailto:<?= htmlspecialchars($student['email']) ?>" 
                                                       class="text-decoration-none">
                                                        <i class="bi bi-envelope"></i>
                                                        <small><?= htmlspecialchars($student['email']) ?></small>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">ไม่ระบุ</span>
                                                <?php endif; ?>
                                            </td>
                                            <!-- เบอร์โทร (คลิกเพื่อโทร) -->
                                            <td>
                                                <?php if (!empty($student['phone'])): ?>
                                                    <a href="tel:<?= htmlspecialchars($student['phone']) ?>" 
                                                       class="text-decoration-none">
                                                        <i class="bi bi-telephone"></i>
                                                        <small><?= htmlspecialchars($student['phone']) ?></small>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">ไม่ระบุ</span>
                                                <?php endif; ?>
                                            </td>
                                            <!-- ปุ่มการจัดการ -->
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <!-- ปุ่มดูข้อมูล -->
                                                    <a href="view_student.php?id=<?= urlencode($student['student_id']) ?>" 
                                                       class="btn btn-info btn-sm" title="ดูข้อมูล">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <!-- ปุ่มแก้ไข -->
                                                    <a href="edit_student.php?id=<?= urlencode($student['student_id']) ?>" 
                                                       class="btn btn-warning btn-sm" title="แก้ไข">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- ===== สรุปผลการค้นหา ===== -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>สรุปผลการค้นหา:</strong> 
                                    พบนักศึกษา <?= count($students) ?> คน
                                    <?php if (!empty($search_text)): ?>
                                        จากคำค้นหา "<?= htmlspecialchars($search_text) ?>"
                                    <?php endif; ?>
                                    <?php if (!empty($search_curriculum)): ?>
                                        <?php
                                        // หาชื่อหลักสูตรจาก ID
                                        $curriculum_name = '';
                                        foreach ($curriculums as $curr) {
                                            if ($curr['curriculum_id'] == $search_curriculum) {
                                                $curriculum_name = $curr['curriculum_name'];
                                                break;
                                            }
                                        }
                                        ?>
                                        ในหลักสูตร "<?= htmlspecialchars($curriculum_name) ?>"
                                    <?php endif; ?>
                                    <?php if (!empty($search_gender)): ?>
                                        เพศ <?= htmlspecialchars($search_gender) ?>
                                    <?php endif; ?>
                                    <?php if (!empty($search_room)): ?>
                                        ห้อง "<?= htmlspecialchars($search_room) ?>"
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ===== ปุ่มการทำงานเพิ่มเติม ===== -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="card-title">การจัดการข้อมูล</h6>
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <a href="list_students.php" class="btn btn-primary">
                                    <i class="bi bi-list"></i> รายการนักศึกษาทั้งหมด
                                </a>
                                <a href="add_student.php" class="btn btn-success">
                                    <i class="bi bi-plus-circle"></i> เพิ่มนักศึกษาใหม่
                                </a>
                                <a href="statistics.php" class="btn btn-info">
                                    <i class="bi bi-bar-chart"></i> ดูสถิติ
                                </a>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="bi bi-house"></i> หน้าหลัก
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    /**
     * ฟังก์ชัน Export ข้อมูลเป็น CSV
     * สร้างไฟล์ CSV จากข้อมูลในตารางและดาวน์โหลด
     */
    function exportToCSV() {
        // ดึงตารางที่มี ID studentsTable
        const table = document.getElementById('studentsTable');
        const rows = table.querySelectorAll('tr');
        let csv = [];
        
        // ===== สร้าง Header ===== 
        const headerCells = rows[0].querySelectorAll('th');
        let headerRow = [];
        headerCells.forEach((cell, index) => {
            // ไม่เอาคอลัมน์การจัดการ (คอลัมน์สุดท้าย)
            if (index < headerCells.length - 1) {
                headerRow.push('"' + cell.textContent.trim() + '"');
            }
        });
        csv.push(headerRow.join(','));
        
        // ===== สร้าง Data rows =====
        for (let i = 1; i < rows.length; i++) {
            const cells = rows[i].querySelectorAll('td');
            let row = [];
            cells.forEach((cell, index) => {
                // ไม่เอาคอลัมน์การจัดการ (คอลัมน์สุดท้าย)
                if (index < cells.length - 1) {
                    row.push('"' + cell.textContent.trim() + '"');
                }
            });
            csv.push(row.join(','));
        }
        
        // ===== Download ไฟล์ =====
        const csvContent = csv.join('\n');
        // เพิ่ม BOM เพื่อรองรับภาษาไทยใน Excel
        const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        // ตั้งชื่อไฟล์พร้อมวันที่
        link.download = 'search_results_' + new Date().toISOString().slice(0, 10) + '.csv';
        link.click();
    }
    
    /**
     * Event listener เมื่อโหลดหน้าเสร็จ
     * Focus ที่ช่องค้นหาเพื่อให้ผู้ใช้พิมพ์ได้ทันที
     */
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('search_text').focus();
    });
</script>

<!-- ===== CSS เพิ่มเติม ===== -->
<style>
/* กำหนดสี badge สำหรับเพศหญิง */
.bg-pink {
    background-color: #e83e8c !important;
}
</style>

<?php 
// รวมไฟล์ footer ของเว็บไซต์
require_once 'includes/footer.php'; 
?>