<?php
/**
 * ไฟล์: includes/protected_header.php
 * ฟังก์ชัน: Header สำหรับหน้าที่ต้องการการตรวจสอบสิทธิ์
 */

require_once 'auth_config.php';

// ตรวจสอบ session timeout
checkSessionTimeout();

// ล้าง session ที่หมดอายุ
cleanupExpiredSessions($pdo);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>ระบบจัดการข้อมูลนักศึกษา</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.7.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .navbar-brand {
            font-weight: bold;
        }
        .user-info {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 0.5rem 1rem;
        }
        .role-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .role-admin {
            background-color: #dc3545;
        }
        .role-faculty {
            background-color: #198754;
        }
        .role-guest {
            background-color: #6c757d;
        }
        .notification-area {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <!-- Brand -->
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-mortarboard-fill me-2"></i>
                ระบบจัดการข้อมูลนักศึกษา
            </a>
            
            <!-- Toggle button for mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <!-- เมนูสำหรับทุกคน -->
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-house me-1"></i>หน้าหลัก
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="list_students.php">
                            <i class="bi bi-list me-1"></i>รายการนักศึกษา
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="search.php">
                            <i class="bi bi-search me-1"></i>ค้นหาข้อมูล
                        </a>
                    </li>
                    
                    <?php if (hasPermission('faculty_staff')): ?>
                    <!-- เมนูสำหรับเจ้าหน้าที่คณะขึ้นไป -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="manageDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear me-1"></i>จัดการข้อมูล
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="add_student.php">
                                <i class="bi bi-plus-circle me-2"></i>เพิ่มนักศึกษา
                            </a></li>
                            <li><a class="dropdown-item" href="list_curriculum.php">
                                <i class="bi bi-book me-2"></i>จัดการหลักสูตร
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="statistics.php">
                                <i class="bi bi-bar-chart me-2"></i>สถิติและรายงาน
                            </a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('admin')): ?>
                    <!-- เมนูสำหรับผู้ดูแลระบบเท่านั้น -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-shield-lock me-1"></i>ผู้ดูแลระบบ
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="manage_users.php">
                                <i class="bi bi-people me-2"></i>จัดการผู้ใช้งาน
                            </a></li>
                            <li><a class="dropdown-item" href="activity_logs.php">
                                <i class="bi bi-clipboard-data me-2"></i>บันทึกกิจกรรม
                            </a></li>
                            <li><a class="dropdown-item" href="backup.php">
                                <i class="bi bi-archive me-2"></i>สำรองข้อมูล
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="system_settings.php">
                                <i class="bi bi-sliders me-2"></i>ตั้งค่าระบบ
                            </a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <!-- User Info และ Logout -->
                <div class="d-flex align-items-center">
                    <?php if (isLoggedIn()): ?>
                        <div class="user-info me-3">
                            <span class="text-white me-2">
                                <i class="bi bi-person-circle me-1"></i>
                                <?= htmlspecialchars($_SESSION['full_name']) ?>
                            </span>
                            <span class="badge role-badge <?= 'role-' . ($_SESSION['user_role'] == 'faculty_staff' ? 'faculty' : $_SESSION['user_role']) ?>">
                                <?= getRoleDisplayName($_SESSION['user_role']) ?>
                            </span>
                        </div>
                        <a href="profile.php" class="btn btn-outline-light btn-sm me-2">
                            <i class="bi bi-person-gear"></i>
                        </a>
                        <a href="logout.php" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-box-arrow-right me-1"></i>ออกจากระบบ
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i>เข้าสู่ระบบ
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Notification Area -->
    <div class="notification-area">
        <?php if (!isLoggedIn()): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <div class="container">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>สำหรับผู้ใช้ทั่วไป:</strong> คุณสามารถดูและค้นหาข้อมูลนักศึกษาได้โดยไม่ต้องเข้าสู่ระบบ 
                    หากต้องการเพิ่มหรือแก้ไขข้อมูล กรุณา<a href="login.php" class="alert-link">เข้าสู่ระบบ</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        <?php endif; ?>
    </div>