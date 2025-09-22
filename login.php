<?php
/**
 * ไฟล์: login.php
 * ฟังก์ชัน: หน้าเข้าสู่ระบบ
 */

require_once 'config.php';
require_once 'auth_config.php';

// ถ้าล็อกอินแล้ว เปลี่ยนเส้นทางไปหน้าหลัก
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error_message = '';
$success_message = '';

// ตรวจสอบการส่งข้อมูลจากฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // ตรวจสอบ CSRF Token
    if (!validateCSRFToken($csrf_token)) {
        $error_message = 'Invalid request. Please try again.';
    } elseif (empty($username) || empty($password)) {
        $error_message = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
    } else {
        // พยายามเข้าสู่ระบบ
        if (login($username, $password, $pdo)) {
            $success_message = 'เข้าสู่ระบบสำเร็จ กำลังเปลี่ยนเส้นทาง...';
            header('refresh:2;url=index.php');
        } else {
            $error_message = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
        }
    }
}

// สร้าง CSRF Token
$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ระบบจัดการข้อมูลนักศึกษา</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.7.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            max-width: 400px;
            width: 100%;
            border: none;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            text-align: center;
            padding: 2rem 1rem;
        }
        .login-body {
            padding: 2rem;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: bold;
            width: 100%;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .guest-access {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card login-card">
                    <!-- Header -->
                    <div class="login-header">
                        <i class="bi bi-mortarboard-fill fs-1 mb-3"></i>
                        <h3 class="mb-0">ระบบจัดการข้อมูลนักศึกษา</h3>
                        <p class="mb-0 mt-2">คณะวิทยาศาสตร์และเทคโนโลยี</p>
                    </div>
                    
                    <!-- Body -->
                    <div class="login-body">
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?= htmlspecialchars($error_message) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success_message): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <?= htmlspecialchars($success_message) ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            
                            <!-- Username Field -->
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person-fill me-2"></i>ชื่อผู้ใช้
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       placeholder="กรอกชื่อผู้ใช้" required 
                                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                            </div>
                            
                            <!-- Password Field -->
                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock-fill me-2"></i>รหัสผ่าน
                                </label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="กรอกรหัสผ่าน" required>
                            </div>
                            
                            <!-- Login Button -->
                            <button type="submit" class="btn btn-primary btn-login">
                                <i class="bi bi-box-arrow-in-right me-2"></i>เข้าสู่ระบบ
                            </button>
                        </form>
                        
                        <!-- Guest Access Notice -->
                        <div class="guest-access">
                            <h6 class="text-muted mb-2">
                                <i class="bi bi-info-circle me-2"></i>สำหรับผู้ใช้ทั่วไป
                            </h6>
                            <p class="small text-muted mb-2">
                                สามารถดูและค้นหาข้อมูลนักศึกษาได้โดยไม่ต้องเข้าสู่ระบบ
                            </p>
                            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-eye me-2"></i>เข้าดูข้อมูลโดยไม่ล็อกอิน
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Demo Accounts Info -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-key me-2"></i>บัญชีทดสอบ
                        </h6>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>ผู้ดูแลระบบ:</strong><br>
                                <small>admin / password</small>
                            </div>
                            <div class="col-md-4">
                                <strong>เจ้าหน้าที่คณะ:</strong><br>
                                <small>faculty1 / password</small>
                            </div>
                            <div class="col-md-4">
                                <strong>ผู้ใช้ทั่วไป:</strong><br>
                                <small>guest / password</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>