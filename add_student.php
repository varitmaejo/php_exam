<?php
// ไฟล์: add_student.php
$page_title = "เพิ่มนักศึกษา";
require_once 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = trim($_POST['student_id']);
    $fullname = trim($_POST['fullname']);
    $gender = $_POST['gender'] ?? '';
    $curriculum_id = $_POST['curriculum_id'];
    $class_room = trim($_POST['class_room']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    // การจัดการอัพโหลดรูปภาพ
    $photo_name = '';
    $upload_error = '';
    
    if (isset($_FILES['student_photo']) && $_FILES['student_photo']['error'] != UPLOAD_ERR_NO_FILE) {
        if ($_FILES['student_photo']['error'] == UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['student_photo']['name'];
            $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $filesize = $_FILES['student_photo']['size'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            // ตรวจสอบนามสกุลไฟล์
            if (!in_array($filetype, $allowed)) {
                $upload_error = 'รองรับเฉพาะไฟล์ JPG, JPEG, PNG, GIF เท่านั้น';
            }
            // ตรวจสอบขนาดไฟล์
            elseif ($filesize > $max_size) {
                $upload_error = 'ขนาดไฟล์ต้องไม่เกิน 2MB';
            }
            else {
                // สร้างโฟลเดอร์ uploads หากยังไม่มี
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) {
                    if (!mkdir($upload_dir, 0755, true)) {
                        $upload_error = 'ไม่สามารถสร้างโฟลเดอร์ uploads ได้';
                    }
                }
                
                if (empty($upload_error)) {
                    // สร้างชื่อไฟล์ใหม่
                    $new_filename = $student_id . '_' . time() . '.' . $filetype;
                    $upload_path = $upload_dir . $new_filename;
                    
                    // อัพโหลดไฟล์
                    if (move_uploaded_file($_FILES['student_photo']['tmp_name'], $upload_path)) {
                        $photo_name = $new_filename;
                    } else {
                        $upload_error = 'ไม่สามารถอัพโหลดไฟล์ได้ กรุณาตรวจสอบสิทธิ์การเขียนไฟล์';
                    }
                }
            }
        } else {
            // แสดงข้อผิดพลาดการอัพโหลด
            switch ($_FILES['student_photo']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $upload_error = 'ไฟล์มีขนาดใหญ่เกินไป';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $upload_error = 'อัพโหลดไฟล์ไม่สมบูรณ์';
                    break;
                default:
                    $upload_error = 'เกิดข้อผิดพลาดในการอัพโหลดไฟล์';
            }
        }
        
        if (!empty($upload_error)) {
            $message .= '<div class="alert alert-warning">การอัพโหลดรูปภาพ: ' . $upload_error . '</div>';
        }
    }
    
    if (empty($student_id) || empty($fullname) || empty($gender) || empty($curriculum_id)) {
        $message .= '<div class="alert alert-warning">กรุณากรอกข้อมูลที่จำเป็น</div>';
    } 
    // ตรวจสอบอีเมล
    elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message .= '<div class="alert alert-warning">รูปแบบอีเมลไม่ถูกต้อง</div>';
    }
    else {
        try {
            // ตรวจสอบรหัสซ้ำ
            $check = $pdo->prepare("SELECT COUNT(*) FROM students WHERE student_id = ?");
            $check->execute([$student_id]);
            
            if ($check->fetchColumn() > 0) {
                $message .= '<div class="alert alert-warning">รหัสนักศึกษานี้มีอยู่แล้ว</div>';
            } 
            // ตรวจสอบอีเมลซ้ำ
            elseif (!empty($email)) {
                $email_check = $pdo->prepare("SELECT COUNT(*) FROM students WHERE email = ?");
                $email_check->execute([$email]);
                if ($email_check->fetchColumn() > 0) {
                    $message .= '<div class="alert alert-warning">อีเมลนี้ถูกใช้งานแล้ว</div>';
                } else {
                    // เพิ่มข้อมูล
                    $insert = $pdo->prepare("INSERT INTO students (student_id, fullname, gender, curriculum_id, class_room, email, phone, student_photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    if ($insert->execute([$student_id, $fullname, $gender, $curriculum_id, $class_room, $email, $phone, $photo_name])) {
                        // บันทึกสำเร็จ - redirect ไปหน้า list_students.php
                        header("Location: list_students.php?success=" . urlencode("เพิ่มนักศึกษา " . $fullname . " สำเร็จ"));
                        exit();
                    } else {
                        $message .= '<div class="alert alert-danger">เกิดข้อผิดพลาดในการบันทึกข้อมูล</div>';
                    }
                }
            }
            else {
                // เพิ่มข้อมูลกรณีไม่มีอีเมล
                $insert = $pdo->prepare("INSERT INTO students (student_id, fullname, gender, curriculum_id, class_room, email, phone, student_photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if ($insert->execute([$student_id, $fullname, $gender, $curriculum_id, $class_room, $email, $phone, $photo_name])) {
                    // บันทึกสำเร็จ - redirect ไปหน้า list_students.php
                    header("Location: list_students.php");
                    exit();
                } else {
                    $message .= '<div class="alert alert-danger">เกิดข้อผิดพลาดในการบันทึกข้อมูล</div>';
                }
            }
        } catch(PDOException $e) {
            $message .= '<div class="alert alert-danger">ข้อผิดพลาด: ' . $e->getMessage() . '</div>';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h2><i class="bi bi-person-plus"></i> เพิ่มนักศึกษาใหม่</h2>

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                    <li class="breadcrumb-item"><a href="list_students.php">รายการนักศึกษา</a></li>
                    <li class="breadcrumb-item active">เพิ่มนักศึกษา</li>
                </ol>
            </nav>

            <!-- แสดงข้อความ -->
            <?= $message ?>

            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-plus-fill"></i>
                        กรอกข้อมูลนักศึกษา
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        รหัสนักศึกษา <span class="text-danger">*</span>
                                    </label>
                                    <!-- ช่องกรอกรหัสนักศึกษา 12 หลัก พร้อม validation -->
                                    <input type="text" class="form-control" name="student_id" 
                                           value="<?= htmlspecialchars($student_id ?? '') ?>" 
                                           placeholder="เช่น 6501234567890" maxlength="12" required>
                                    <div class="form-text">รหัสนักศึกษา 12 หลัก</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        ชื่อ-นามสกุล <span class="text-danger">*</span>
                                    </label>
                                    <!-- ช่องกรอกชื่อ-นามสกุล พร้อมป้องกันการโจมตี XSS -->
                                    <input type="text" class="form-control" name="fullname" 
                                           value="<?= htmlspecialchars($fullname ?? '') ?>" 
                                           placeholder="กรอกชื่อและนามสกุล" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        เพศ <span class="text-danger">*</span>
                                    </label>
                                    <!-- Radio buttons สำหรับเลือกเพศ -->
                                    <div class="mt-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" 
                                                   id="gender_male" value="ชาย" 
                                                   <?= ($gender ?? '') == 'ชาย' ? 'checked' : '' ?> required>
                                            <label class="form-check-label" for="gender_male">ชาย</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" 
                                                   id="gender_female" value="หญิง" 
                                                   <?= ($gender ?? '') == 'หญิง' ? 'checked' : '' ?> required>
                                            <label class="form-check-label" for="gender_female">หญิง</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        หลักสูตร <span class="text-danger">*</span>
                                    </label>
                                    <!-- Dropdown สำหรับเลือกหลักสูตร จากฐานข้อมูล -->
                                    <select class="form-select" name="curriculum_id" required>
                                        <option value="">-- เลือกหลักสูตร --</option>
                                        <?php
                                        /**
                                         * ดึงรายการหลักสูตรจากฐานข้อมูล
                                         * เพื่อแสดงในรูปแบบ dropdown
                                         */
                                        try {
                                            $curricula = $pdo->query("SELECT * FROM curriculum ORDER BY curriculum_name")->fetchAll();
                                            foreach ($curricula as $curriculum) {
                                                // เก็บค่าที่เลือกไว้หลังจากส่งฟอร์ม (กรณีมีข้อผิดพลาด)
                                                $selected = (($curriculum_id ?? '') == $curriculum['curriculum_id']) ? 'selected' : '';
                                                echo "<option value='" . htmlspecialchars($curriculum['curriculum_id']) . "' $selected>";
                                                echo htmlspecialchars($curriculum['curriculum_name']);
                                                echo "</option>";
                                            }
                                        } catch(PDOException $e) {
                                            // กรณีไม่สามารถดึงข้อมูลหลักสูตรได้
                                            echo "<option value=''>เกิดข้อผิดพลาด</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">ห้องเรียน</label>
                                    <!-- ช่องกรอกห้องเรียน (ไม่บังคับ) -->
                                    <input type="text" class="form-control" name="class_room" 
                                           value="<?= htmlspecialchars($class_room ?? '') ?>"
                                           placeholder="เช่น CS-01, วท.201">
                                    <div class="form-text">ระบุห้องเรียนหรือชั้นปี</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">อีเมล</label>
                                    <!-- ช่องกรอกอีเมล พร้อม HTML5 validation -->
                                    <input type="email" class="form-control" name="email" 
                                           value="<?= htmlspecialchars($email ?? '') ?>"
                                           placeholder="example@email.com">
                                    <div class="form-text">อีเมลสำหรับติดต่อ (ไม่บังคับ)</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">โทรศัพท์</label>
                                    <!-- ช่องกรอกเบอร์โทรศัพท์ (ไม่บังคับ) -->
                                    <input type="tel" class="form-control" name="phone" 
                                           value="<?= htmlspecialchars($phone ?? '') ?>"
                                           placeholder="0812345678">
                                    <div class="form-text">หมายเลขโทรศัพท์ (ไม่บังคับ)</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label">รูปภาพนักศึกษา</label>
                                    <!-- File input สำหรับอัพโหลดรูปภาพ พร้อมจำกัดประเภทไฟล์ -->
                                    <input type="file" class="form-control" name="student_photo" 
                                           accept="image/jpeg,image/jpg,image/png,image/gif">
                                    <div class="form-text">
                                        รองรับไฟล์: JPG, JPEG, PNG, GIF (ไม่เกิน 2MB)
                                        <br><small class="text-muted">โฟลเดอร์บันทึก: uploads/</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- ปุ่มสำหรับส่งฟอร์มและยกเลิก -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="list_students.php" class="btn btn-secondary me-md-2">
                                <i class="bi bi-arrow-left"></i> ยกเลิก
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg"></i> เพิ่มนักศึกษา
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>