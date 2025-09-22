<?php
// ไฟล์: edit_student.php - หน้าแก้ไขข้อมูลนักศึกษา
$page_title = "แก้ไขนักศึกษา";
$student = null;
$student_id = isset($_GET['id']) ? trim($_GET['id']) : '';

// ตรวจสอบ ID
if (empty($student_id)) {
    header("Location: index.php");
    exit();
}

require_once 'config.php';

// ดึงข้อมูลนักศึกษาปัจจุบัน
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();

    if (!$student) {
        header("Location: index.php?error=" . urlencode("ไม่พบข้อมูลนักศึกษา"));
        exit();
    }
} catch(PDOException $e) {
    die("ข้อผิดพลาด: " . $e->getMessage());
}

$message = '';
$errors = [];

// ประมวลผลการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $curriculum_id = $_POST['curriculum_id'] ?? '';
    $class_room = trim($_POST['class_room'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // ตรวจสอบข้อมูลพื้นฐาน
    if (empty($fullname)) {
        $errors[] = 'กรุณากรอกชื่อ-นามสกุล';
    }
    if (empty($gender)) {
        $errors[] = 'กรุณาเลือกเพศ';
    }
    if (empty($curriculum_id)) {
        $errors[] = 'กรุณาเลือกหลักสูตร';
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'รูปแบบอีเมลไม่ถูกต้อง';
    }
    
    // จัดการอัพโหลดรูปภาพ
    $photo_name = $student['student_photo'];
    if (isset($_FILES['student_photo']) && $_FILES['student_photo']['error'] != UPLOAD_ERR_NO_FILE) {
        if ($_FILES['student_photo']['error'] == UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['student_photo']['name'];
            $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $filesize = $_FILES['student_photo']['size'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($filetype, $allowed)) {
                $errors[] = 'รองรับเฉพาะไฟล์ JPG, JPEG, PNG, GIF เท่านั้น';
            } elseif ($filesize > $max_size) {
                $errors[] = 'ขนาดไฟล์ต้องไม่เกิน 5MB';
            } else {
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $new_filename = 'student_' . $student_id . '_' . time() . '.' . $filetype;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['student_photo']['tmp_name'], $upload_path)) {
                    // ลบรูปเดิม
                    if (!empty($student['student_photo'])) {
                        $old_photo = 'uploads/' . $student['student_photo'];
                        if (file_exists($old_photo)) {
                            unlink($old_photo);
                        }
                    }
                    $photo_name = $new_filename;
                } else {
                    $errors[] = 'ไม่สามารถอัพโหลดไฟล์ได้';
                }
            }
        }
    }
    
    // บันทึกข้อมูลหากไม่มีข้อผิดพลาด
    if (empty($errors)) {
        try {
            $update_sql = "UPDATE students SET 
                          fullname = ?, gender = ?, curriculum_id = ?, 
                          class_room = ?, email = ?, phone = ?, student_photo = ? 
                          WHERE student_id = ?";
            
            $update_stmt = $pdo->prepare($update_sql);
            
            if ($update_stmt->execute([$fullname, $gender, $curriculum_id, $class_room, $email, $phone, $photo_name, $student_id])) {
                $message = '<div class="alert alert-success">แก้ไขข้อมูลนักศึกษาสำเร็จ</div>';
                
                // อัพเดทข้อมูลสำหรับแสดงผล
                $student['fullname'] = $fullname;
                $student['gender'] = $gender;
                $student['curriculum_id'] = $curriculum_id;
                $student['class_room'] = $class_room;
                $student['email'] = $email;
                $student['phone'] = $phone;
                $student['student_photo'] = $photo_name;
                
                // Redirect หลัง 2 วินาที
                echo "<script>setTimeout(function(){ window.location.href = 'list_students.php'; }, 2000);</script>";
            } else {
                $errors[] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
            }
        } catch(PDOException $e) {
            $errors[] = 'ข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h2><i class="bi bi-pencil-square"></i> แก้ไขข้อมูลนักศึกษา</h2>

            <!-- แสดงข้อความ -->
            <?= $message ?>

            <!-- แสดงข้อผิดพลาด -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong>พบข้อผิดพลาด:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- ฟอร์มแก้ไขข้อมูล -->
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">แก้ไขข้อมูล: <?= htmlspecialchars($student['fullname']) ?></h5>
                    <small>รหัสนักศึกษา: <?= htmlspecialchars($student['student_id']) ?></small>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">รหัสนักศึกษา</label>
                                    <input type="text" class="form-control bg-light" 
                                           value="<?= htmlspecialchars($student['student_id']) ?>" disabled>
                                    <div class="form-text">รหัสนักศึกษาไม่สามารถแก้ไขได้</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="fullname" 
                                           value="<?= htmlspecialchars($student['fullname']) ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">เพศ <span class="text-danger">*</span></label>
                                    <div class="mt-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" 
                                                   id="gender_male" value="ชาย" 
                                                   <?= $student['gender'] == 'ชาย' ? 'checked' : '' ?> required>
                                            <label class="form-check-label" for="gender_male">ชาย</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" 
                                                   id="gender_female" value="หญิง" 
                                                   <?= $student['gender'] == 'หญิง' ? 'checked' : '' ?> required>
                                            <label class="form-check-label" for="gender_female">หญิng</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">หลักสูตร <span class="text-danger">*</span></label>
                                    <select class="form-select" name="curriculum_id" required>
                                        <option value="">-- เลือกหลักสูตร --</option>
                                        <?php
                                        try {
                                            $curricula = $pdo->query("SELECT * FROM curriculum ORDER BY curriculum_name")->fetchAll();
                                            foreach ($curricula as $curriculum) {
                                                $selected = ($student['curriculum_id'] == $curriculum['curriculum_id']) ? 'selected' : '';
                                                echo "<option value='" . htmlspecialchars($curriculum['curriculum_id']) . "' $selected>";
                                                echo htmlspecialchars($curriculum['curriculum_name']);
                                                echo "</option>";
                                            }
                                        } catch(PDOException $e) {
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
                                    <input type="text" class="form-control" name="class_room" 
                                           value="<?= htmlspecialchars($student['class_room']) ?>"
                                           placeholder="เช่น CS-01">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">อีเมล</label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?= htmlspecialchars($student['email']) ?>"
                                           placeholder="example@email.com">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">โทรศัพท์</label>
                                    <input type="tel" class="form-control" name="phone" 
                                           value="<?= htmlspecialchars($student['phone']) ?>"
                                           placeholder="0812345678">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label">รูปภาพ</label>
                                    <?php if (!empty($student['student_photo']) && file_exists('uploads/' . $student['student_photo'])): ?>
                                        <div class="mb-2">
                                            <img src="uploads/<?= htmlspecialchars($student['student_photo']) ?>" 
                                                 alt="รูปปัจจุบัน" class="img-thumbnail d-block" style="max-width: 150px;">
                                            <small class="text-muted">รูปปัจจุบัน</small>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" name="student_photo" 
                                           accept="image/jpeg,image/jpg,image/png,image/gif">
                                    <div class="form-text">
                                        เลือกไฟล์ใหม่หากต้องการเปลี่ยน: JPG, JPEG, PNG, GIF (ไม่เกิน 5MB)
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="list_students.php" class="btn btn-secondary me-md-2">ยกเลิก</a>
                            <button type="submit" class="btn btn-warning">บันทึกการแก้ไข</button>
                            <a href="index.php" class="btn btn-primary">หน้าหลัก</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>