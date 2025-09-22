<?php
/**
 * ไฟล์: statistics.php - หน้าสถิติและรายงาน
 * ฟังก์ชัน: แสดงข้อมูลสถิติและรายงานต่างๆ ของระบบจัดการนักศึกษา
 * วันที่สร้าง: 2025
 * ผู้พัฒนา: ระบบจัดการนักศึกษา
 */

$page_title = "สถิติและรายงาน";

require_once 'config.php';

/**
 * ดึงข้อมูลสถิติและรายงานต่างๆ จากฐานข้อมูล
 * รวมถึงข้อมูลการวิเคราะห์และการจัดกลุ่ม
 */
try {
    /**
     * สถิติพื้นฐานของระบบ
     */
    // นับจำนวนนักศึกษาทั้งหมดในระบบ
    $total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    
    // นับจำนวนหลักสูตรทั้งหมดในระบบ
    $total_curriculum = $pdo->query("SELECT COUNT(*) FROM curriculum")->fetchColumn();
    
    /**
     * สถิติการแบ่งตามเพศ
     * ดึงข้อมูลจำนวนนักศึกษาแยกตามเพศและจัดเรียงตามจำนวน
     */
    $gender_stats = $pdo->query("
        SELECT gender, COUNT(*) as count 
        FROM students 
        GROUP BY gender 
        ORDER BY count DESC
    ")->fetchAll();
    
    /**
     * คำนวณจำนวนนักศึกษาแต่ละเพศ
     * เพื่อใช้ในการแสดงกราฟและคำนวณเปอร์เซ็นต์
     */
    $male_count = 0;
    $female_count = 0;
    foreach ($gender_stats as $stat) {
        if ($stat['gender'] == 'ชาย') {
            $male_count = $stat['count'];
        } elseif ($stat['gender'] == 'หญิง') {
            $female_count = $stat['count'];
        }
    }
    
    /**
     * สถิติการแบ่งตามหลักสูตร
     * ใช้ LEFT JOIN เพื่อแสดงหลักสูตรที่ไม่มีนักศึกษาด้วย
     */
    $curriculum_stats = $pdo->query("
        SELECT c.curriculum_name, c.curriculum_id, COUNT(s.student_id) as student_count 
        FROM curriculum c 
        LEFT JOIN students s ON c.curriculum_id = s.curriculum_id 
        GROUP BY c.curriculum_id, c.curriculum_name 
        ORDER BY student_count DESC
    ")->fetchAll();
    
    /**
     * รายการนักศึกษาที่ลงทะเบียนล่าสุด
     * จำกัดแสดงเฉพาะ 10 คนล่าสุด
     */
    $recent_students = $pdo->query("
        SELECT s.*, c.curriculum_name 
        FROM students s 
        LEFT JOIN curriculum c ON s.curriculum_id = c.curriculum_id 
        ORDER BY s.student_id DESC 
        LIMIT 10
    ")->fetchAll();
    
    /**
     * สถิติการกระจายตัวตามห้องเรียน
     * แสดงเฉพาะห้องที่มีข้อมูลและจำกัดแสดง 10 ห้องแรก
     */
    $room_stats = $pdo->query("
        SELECT class_room, COUNT(*) as count 
        FROM students 
        WHERE class_room IS NOT NULL AND class_room != '' 
        GROUP BY class_room 
        ORDER BY count DESC 
        LIMIT 10
    ")->fetchAll();
    
    /**
     * หาหลักสูตรที่มีนักศึกษามากที่สุด
     * สำหรับแสดงในส่วนสถิติที่น่าสนใจ
     */
    $max_curriculum = $pdo->query("
        SELECT c.curriculum_name, COUNT(s.student_id) as count 
        FROM curriculum c 
        LEFT JOIN students s ON c.curriculum_id = s.curriculum_id 
        GROUP BY c.curriculum_id, c.curriculum_name 
        ORDER BY count DESC 
        LIMIT 1
    ")->fetch();
    
    /**
     * หาหลักสูตรที่มีนักศึกษาน้อยที่สุด (แต่ต้องมีนักศึกษาอย่างน้อย 1 คน)
     * ใช้ HAVING เพื่อกรองเฉพาะหลักสูตรที่มีนักศึกษา
     */
    $min_curriculum = $pdo->query("
        SELECT c.curriculum_name, COUNT(s.student_id) as count 
        FROM curriculum c 
        LEFT JOIN students s ON c.curriculum_id = s.curriculum_id 
        GROUP BY c.curriculum_id, c.curriculum_name 
        HAVING count > 0
        ORDER BY count ASC 
        LIMIT 1
    ")->fetch();
    
} catch(PDOException $e) {
    // กรณีเกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล
    die("ข้อผิดพลาด: " . $e->getMessage());
}

// โหลด header template
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <!-- หัวข้อหลักของหน้า -->
            <h2><i class="bi bi-bar-chart"></i> สถิติและรายงาน</h2>
            <p class="text-muted">ภาพรวมข้อมูลนักศึกษาและหลักสูตร</p>
            
            <!-- เส้นทางการนำทาง (Breadcrumb) -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                    <li class="breadcrumb-item active">สถิติและรายงาน</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- ส่วนสถิติหลัก - การ์ด 4 ใบแสดงข้อมูลสำคัญ -->
    <div class="row mb-4">
        <!-- การ์ดจำนวนนักศึกษาทั้งหมด -->
        <div class="col-md-3">
            <div class="card bg-primary text-white text-center">
                <div class="card-body">
                    <i class="bi bi-people-fill" style="font-size: 2rem;"></i>
                    <h3 class="mt-2"><?= number_format($total_students) ?></h3>
                    <p class="mb-0">นักศึกษาทั้งหมด</p>
                </div>
            </div>
        </div>
        
        <!-- การ์ดจำนวนหลักสูตรทั้งหมด -->
        <div class="col-md-3">
            <div class="card bg-success text-white text-center">
                <div class="card-body">
                    <i class="bi bi-book-fill" style="font-size: 2rem;"></i>
                    <h3 class="mt-2"><?= number_format($total_curriculum) ?></h3>
                    <p class="mb-0">หลักสูตรทั้งหมด</p>
                </div>
            </div>
        </div>
        
        <!-- การ์ดจำนวนนักศึกษาชาย พร้อมเปอร์เซ็นต์ -->
        <div class="col-md-3">
            <div class="card bg-info text-white text-center">
                <div class="card-body">
                    <i class="bi bi-person-check-fill" style="font-size: 2rem;"></i>
                    <h3 class="mt-2"><?= number_format($male_count) ?></h3>
                    <p class="mb-0">นักศึกษาชาย</p>
                    <!-- คำนวณและแสดงเปอร์เซ็นต์ -->
                    <small><?= $total_students > 0 ? round(($male_count/$total_students)*100, 1) : 0 ?>%</small>
                </div>
            </div>
        </div>
        
        <!-- การ์ดจำนวนนักศึกษาหญิง พร้อมเปอร์เซ็นต์ -->
        <div class="col-md-3">
            <div class="card bg-warning text-dark text-center">
                <div class="card-body">
                    <i class="bi bi-person-heart" style="font-size: 2rem;"></i>
                    <h3 class="mt-2"><?= number_format($female_count) ?></h3>
                    <p class="mb-0">นักศึกษาหญิง</p>
                    <!-- คำนวณและแสดงเปอร์เซ็นต์ -->
                    <small><?= $total_students > 0 ? round(($female_count/$total_students)*100, 1) : 0 ?>%</small>
                </div>
            </div>
        </div>
    </div>

    <!-- ส่วนกราฟและข้อมูลวิเคราะห์ -->
    <div class="row">
        <!-- กราฟแสดงสัดส่วนเพศแบบ Pie Chart -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-pie-chart"></i> สัดส่วนนักศึกษาตามเพศ</h5>
                </div>
                <div class="card-body text-center">
                    <?php if ($total_students > 0): ?>
                        <!-- แสดงกราฟเป็นวงกลมเมื่อมีข้อมูลนักศึกษา -->
                        <div class="pie-chart-container mb-3">
                            <div class="row">
                                <div class="col-6">
                                    <!-- วงกลมแสดงสัดส่วนนักศึกษาชาย -->
                                    <div class="gender-stat">
                                        <div class="stat-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                                             style="width: 80px; height: 80px; border-radius: 50%;">
                                            <strong><?= round(($male_count/$total_students)*100, 1) ?>%</strong>
                                        </div>
                                        <h6 class="mt-2">ชาย</h6>
                                        <p class="text-muted"><?= number_format($male_count) ?> คน</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <!-- วงกลมแสดงสัดส่วนนักศึกษาหญิง -->
                                    <div class="gender-stat">
                                        <div class="stat-circle bg-warning text-dark d-inline-flex align-items-center justify-content-center" 
                                             style="width: 80px; height: 80px; border-radius: 50%;">
                                            <strong><?= round(($female_count/$total_students)*100, 1) ?>%</strong>
                                        </div>
                                        <h6 class="mt-2">หญิง</h6>
                                        <p class="text-muted"><?= number_format($female_count) ?> คน</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- แสดงข้อความเมื่อไม่มีข้อมูลนักศึกษา -->
                        <p class="text-muted">ไม่มีข้อมูลนักศึกษา</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ส่วนแสดงสถิติที่น่าสนใจ -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-trophy"></i> สถิติที่น่าสนใจ</h5>
                </div>
                <div class="card-body">
                    <!-- แสดงหลักสูตรที่มีนักศึกษามากที่สุด -->
                    <?php if ($max_curriculum): ?>
                        <div class="mb-3">
                            <strong>หลักสูตรที่มีนักศึกษามากที่สุด:</strong><br>
                            <span class="text-primary"><?= htmlspecialchars($max_curriculum['curriculum_name']) ?></span>
                            <span class="badge bg-success"><?= $max_curriculum['count'] ?> คน</span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- แสดงหลักสูตรที่มีนักศึกษาน้อยที่สุด -->
                    <?php if ($min_curriculum): ?>
                        <div class="mb-3">
                            <strong>หลักสูตรที่มีนักศึกษาน้อยที่สุด:</strong><br>
                            <span class="text-info"><?= htmlspecialchars($min_curriculum['curriculum_name']) ?></span>
                            <span class="badge bg-warning text-dark"><?= $min_curriculum['count'] ?> คน</span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- แสดงอัตราส่วนเพศ -->
                    <div class="mb-3">
                        <strong>อัตราส่วนเพศ:</strong><br>
                        <?php if ($total_students > 0): ?>
                            <span class="text-primary">ชาย : หญิง = <?= $male_count ?> : <?= $female_count ?></span>
                        <?php else: ?>
                            <span class="text-muted">ไม่มีข้อมูล</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- แสดงจำนวนห้องเรียน -->
                    <div>
                        <strong>จำนวนห้องเรียน:</strong><br>
                        <span class="text-success"><?= count($room_stats) ?> ห้อง</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ตารางแสดงสถิติตามหลักสูตร -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-table"></i> จำนวนนักศึกษาแยกตามหลักสูตร</h5>
                </div>
                <div class="card-body">
                    <?php if (count($curriculum_stats) > 0): ?>
                        <!-- ตารางแสดงข้อมูลหลักสูตรและจำนวนนักศึกษา -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>ชื่อหลักสูตร</th>
                                        <th>จำนวนนักศึกษา</th>
                                        <th>เปอร์เซ็นต์</th>
                                        <th>สัดส่วน</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($curriculum_stats as $index => $stat): 
                                        // คำนวณเปอร์เซ็นต์และความกว้างของ progress bar
                                        $percentage = $total_students > 0 ? round(($stat['student_count']/$total_students)*100, 1) : 0;
                                        $bar_width = $total_students > 0 ? ($stat['student_count']/$total_students)*100 : 0;
                                    ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($stat['curriculum_name']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?= number_format($stat['student_count']) ?> คน</span>
                                            </td>
                                            <td><?= $percentage ?>%</td>
                                            <td>
                                                <!-- Progress bar แสดงสัดส่วนนักศึกษาในแต่ละหลักสูตร -->
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-info" role="progressbar" 
                                                         style="width: <?= $bar_width ?>%"
                                                         aria-valuenow="<?= $percentage ?>" 
                                                         aria-valuemin="0" aria-valuemax="100">
                                                        <?= $percentage ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <!-- ปุ่มดูข้อมูลหลักสูตร -->
                                                <a href="view_curriculum.php?id=<?= urlencode($stat['curriculum_id']) ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> ดู
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <!-- ข้อความเมื่อไม่มีข้อมูลหลักสูตร -->
                        <p class="text-muted text-center">ไม่มีข้อมูลหลักสูตร</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ส่วนสถิติตามห้องเรียนและนักศึกษาล่าสุด -->
    <?php if (count($room_stats) > 0): ?>
    <div class="row mb-4">
        <!-- ตารางสถิติตามห้องเรียน -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-building"></i> จำนวนนักศึกษาตามห้องเรียน</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>ห้อง</th>
                                    <th>จำนวน</th>
                                    <th>สัดส่วน</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($room_stats as $room): 
                                    // คำนวณเปอร์เซ็นต์ของนักศึกษาในแต่ละห้อง
                                    $room_percentage = $total_students > 0 ? round(($room['count']/$total_students)*100, 1) : 0;
                                ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($room['class_room']) ?></strong></td>
                                        <td><?= $room['count'] ?> คน</td>
                                        <td>
                                            <!-- Progress bar แสดงสัดส่วนเทียบกับห้องที่มีนักศึกษามากที่สุด -->
                                            <div class="progress" style="height: 15px;">
                                                <div class="progress-bar bg-secondary" 
                                                     style="width: <?= ($room['count']/max(array_column($room_stats, 'count')))*100 ?>%">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- รายการนักศึกษาที่เพิ่มล่าสุด -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> นักศึกษาที่เพิ่มล่าสุด</h5>
                </div>
                <div class="card-body">
                    <?php if (count($recent_students) > 0): ?>
                        <!-- แสดงรายการนักศึกษา 5 คนล่าสุด พร้อม scrollbar -->
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach (array_slice($recent_students, 0, 5) as $student): ?>
                                <div class="d-flex align-items-center mb-2 p-2 bg-light rounded">
                                    <div class="me-3">
                                        <i class="bi bi-person-circle text-primary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">
                                            <!-- ลิงก์ไปดูข้อมูลนักศึกษา -->
                                            <a href="view_student.php?id=<?= urlencode($student['student_id']) ?>" 
                                               class="text-decoration-none">
                                                <?= htmlspecialchars($student['fullname']) ?>
                                            </a>
                                        </div>
                                        <!-- แสดงรหัสนักศึกษาและหลักสูตร -->
                                        <small class="text-muted">
                                            <?= htmlspecialchars($student['student_id']) ?> | 
                                            <?= htmlspecialchars($student['curriculum_name'] ?? 'ไม่ระบุหลักสูตร') ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- ปุ่มดูรายการทั้งหมด -->
                        <div class="text-center mt-3">
                            <a href="list_students.php" class="btn btn-sm btn-outline-primary">
                                ดูรายการทั้งหมด
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- ข้อความเมื่อยังไม่มีนักศึกษา -->
                        <p class="text-muted text-center">ยังไม่มีนักศึกษา</p>
                    <?php endif; ?>
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
                    <!-- ชุดปุ่มสำหรับการดำเนินการหลักของระบบ -->
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <a href="list_students.php" class="btn btn-primary">
                            <i class="bi bi-list"></i> รายการนักศึกษา
                        </a>
                        <a href="add_student.php" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> เพิ่มนักศึกษา
                        </a>
                        <a href="list_curriculum.php" class="btn btn-info">
                            <i class="bi bi-book"></i> จัดการหลักสูตร
                        </a>
                        <a href="search.php" class="btn btn-warning">
                            <i class="bi bi-search"></i> ค้นหาข้อมูล
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

<!-- โหลด footer template -->
<?php require_once 'includes/footer.php'; ?>