<?php
/**
 * ไฟล์: auth_config.php
 * ฟังก์ชัน: การตั้งค่าและฟังก์ชันสำหรับระบบ Authentication
 */

// เริ่ม session ถ้ายังไม่ได้เริ่ม
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// การตั้งค่าระบบ Authentication
define('SESSION_TIMEOUT', 3600); // 1 ชั่วโมง
define('PASSWORD_MIN_LENGTH', 6);
define('LOGIN_ATTEMPTS_LIMIT', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 นาที

/**
 * ตรวจสอบการเข้าสู่ระบบ
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * ตรวจสอบสิทธิ์การเข้าถึง
 */
function hasPermission($required_role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user_role = $_SESSION['user_role'];
    
    switch ($required_role) {
        case 'admin':
            return $user_role === 'admin';
        case 'faculty_staff':
            return in_array($user_role, ['admin', 'faculty_staff']);
        case 'guest':
            return in_array($user_role, ['admin', 'faculty_staff', 'guest']);
        default:
            return false;
    }
}

/**
 * ป้องกันการเข้าถึงหน้าที่ต้องการสิทธิ์
 */
function requireAuth($required_role = 'guest') {
    if (!hasPermission($required_role)) {
        if (!isLoggedIn()) {
            // ไม่ได้ล็อกอิน - เปลี่ยนเส้นทางไปหน้า login
            header('Location: login.php');
        } else {
            // ล็อกอินแล้วแต่ไม่มีสิทธิ์ - แสดงข้อความผิดพลาด
            header('HTTP/1.0 403 Forbidden');
            die('คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }
        exit;
    }
}

/**
 * ตรวจสอบ session timeout
 */
function checkSessionTimeout() {
    if (isLoggedIn() && isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            logout();
            return false;
        }
    }
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * เข้าสู่ระบบ
 */
function login($username, $password, $pdo) {
    try {
        // ดึงข้อมูลผู้ใช้
        $stmt = $pdo->prepare("SELECT user_id, username, password_hash, user_role, is_active, full_name FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // ล็อกอินสำเร็จ
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['user_role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['last_activity'] = time();
            
            // สร้าง session ในฐานข้อมูล
            $session_id = session_id();
            $expires_at = date('Y-m-d H:i:s', time() + SESSION_TIMEOUT);
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $stmt = $pdo->prepare("INSERT INTO user_sessions (session_id, user_id, expires_at, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$session_id, $user['user_id'], $expires_at, $ip_address, $user_agent]);
            
            // อัพเดทเวลาล็อกอินล่าสุด
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $stmt->execute([$user['user_id']]);
            
            // บันทึกกิจกรรม
            logActivity($user['user_id'], 'LOGIN', 'ผู้ใช้เข้าสู่ระบบ', $pdo);
            
            return true;
        }
        
        // ล็อกอินไม่สำเร็จ
        logActivity(null, 'LOGIN_FAILED', "การล็อกอินล้มเหลว: $username", $pdo);
        return false;
        
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * ออกจากระบบ
 */
function logout($pdo = null) {
    if (isLoggedIn() && $pdo) {
        logActivity($_SESSION['user_id'], 'LOGOUT', 'ผู้ใช้ออกจากระบบ', $pdo);
        
        // ลบ session จากฐานข้อมูล
        $session_id = session_id();
        $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE session_id = ?");
        $stmt->execute([$session_id]);
    }
    
    // ล้าง session ทั้งหมด
    session_destroy();
    
    // เปลี่ยนเส้นทางไปหน้า login
    header('Location: login.php');
    exit;
}

/**
 * บันทึกกิจกรรม
 */
function logActivity($user_id, $action, $description, $pdo) {
    try {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, $description, $ip_address]);
    } catch (PDOException $e) {
        // บันทึก error log ถ้าจำเป็น
    }
}

/**
 * ดึงชื่อบทบาทเป็นภาษาไทย
 */
function getRoleDisplayName($role) {
    switch ($role) {
        case 'admin':
            return 'ผู้ดูแลระบบ';
        case 'faculty_staff':
            return 'เจ้าหน้าที่คณะ';
        case 'guest':
            return 'ผู้ใช้ทั่วไป';
        default:
            return 'ไม่ระบุ';
    }
}

/**
 * ตรวจสอบ session ที่หมดอายุและลบออก
 */
function cleanupExpiredSessions($pdo) {
    try {
        $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE expires_at < NOW()");
        $stmt->execute();
    } catch (PDOException $e) {
        // บันทึก error log
    }
}

/**
 * สร้าง CSRF Token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * ตรวจสอบ CSRF Token
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>