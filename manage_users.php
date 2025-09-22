<?php
/**
 * ไฟล์: manage_users.php
 * ฟังก์ชัน: จัดการผู้ใช้งานระบบ (เฉพาะผู้ดูแลระบบ)
 */

$page_title = "จัดการผู้ใช้งาน";

require_once 'config.php';
require_once 'auth_config.php';

// ตรวจสอบสิทธิ์ - เฉพาะผู้ดูแลระบบเท่านั้น
requireAuth('admin');

$message = '';
$error = '';

// การดำเนินการต่างๆ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // ตรวจสอบ CSRF Token
    if (!validateCSRFToken($csrf_token)) {
        $error = 'Invalid request token';
    } else {
        switch ($action) {
            case 'add_user':
                $username = trim($_POST['username'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $full_name = trim($_POST['full_name'] ?? '');
                $password = $_POST['password'] ?? '';
                $user_role = $_POST['user_role'] ?? '';
                
                if (empty($username) || empty($email) || empty($full_name) || empty($password) || empty($user_role)) {
                    $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
                } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
                    $error = 'รหัสผ่านต้องมีความยาวอย่างน้อย ' . PASSWORD_MIN_LENGTH . ' ตัวอักษร';
                } else {
                    try {
                        // ตรวจสอบว่า username หรือ email ซ้ำหรือไม่
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
                        $stmt->execute([$username, $email]);
                        
                        if ($stmt->fetchColumn() > 0) {
                            $error = 'ชื่อผู้ใช้หรืออีเมลนี้มีอยู่ในระบบแล้ว';
                        } else {
                            // เพิ่มผู้ใช้ใหม่
                            $password_hash = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, email, full_name, user_role) VALUES (?, ?, ?, ?, ?)");
                            $stmt->execute([$username, $password_hash, $email, $full_name, $user_role]);
                            
                            logActivity($_SESSION['user_id'], 'ADD_USER', "เพิ่มผู้ใช้ใหม่: $username", $pdo);
                            $message = 'เพิ่มผู้ใช้ใหม่เรียบร้อยแล้ว';
                        }
                    } catch (PDOException $e) {
                        $error = 'เกิดข้อผิดพลาดในการเพิ่มผู้ใช้';
                    }
                }
                break;
                
            case 'toggle_status':
                $user_id = intval($_POST['user_id'] ?? 0);
                if ($user_id > 0 && $user_id != $_SESSION['user_id']) {
                    try {
                        $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE user_id = ?");
                        $stmt->execute([$user_id]);
                        
                        logActivity($_SESSION['user_id'], 'TOGGLE_USER_STATUS', "เปลี่ยนสถานะผู้ใช้ ID: $user_id", $pdo);
                        $message = 'เปลี่ยนสถานะผู้ใช้เรียบร้อยแล้ว';
                    } catch (PDOException $e) {
                        $error = 'เกิดข้อผิดพลาดในการเปลี่ยนสถานะผู้ใช้';
                    }
                } else {
                    $error = 'ไม่สามารถเปลี่ยนสถานะตัวเองได้';
                }
                break;
                
            case 'reset_password':
                $user_id = intval($_POST['user_id'] ?? 0);
                $new_password = $_POST['new_password'] ?? '';
                
                if ($user_id > 0 && !empty($new_password)) {
                    if (strlen($new_password) < PASSWORD_MIN_LENGTH) {
                        $error = 'รหัสผ่านต้องมีความยาวอย่างน้อย ' . PASSWORD_MIN_LENGTH . ' ตัวอักษร';
                    } else {
                        try {
                            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                            $stmt->execute([$password_hash, $user_id]);
                            
                            logActivity($_SESSION['user_id'], 'RESET_PASSWORD', "รีเซ็ตรหัสผ่านสำหรับผู้ใช้ ID: $user_id", $pdo);
                            $message = 'รีเซ็ตรหัสผ่านเรียบร้อยแล้ว';
                        } catch (PDOException $e) {
                            $error = 'เกิดข้อผิดพลาดในการรีเซ็ตรหัสผ่าน';
                        }
                    }
                } else {
                    $error = 'ข้อมูลไม่ครบถ้วน';
                }
                break;
        }
    }
}

// ดึงรายการผู้ใช้ทั้งหมด
try {
    $stmt = $pdo->query("
        SELECT user_id, username, email, full_name, user_role, is_active, created_at, last_login,
               (SELECT COUNT(*) FROM user_sessions WHERE user_sessions.user_id = users.user_id AND expires_at > NOW()) as active_sessions
        FROM users 
        ORDER BY created_at DESC
    ");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
    $error = 'ไม่สามารถดึงข้อมูลผู้ใช้ได้';
}

// สร้าง CSRF Token
$csrf_token = generateCSRFToken();

require_once 'includes/protected_header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people-fill me-2"></i>จัดการผู้ใช้งาน</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-plus-circle me-2"></i>เพิ่มผู้ใช้ใหม่
        </button>
    </div>

    <!-- Alert Messages -->
    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">รายการผู้ใช้งานทั้งหมด</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>ชื่อผู้ใช้</th>
                            <th>ชื่อเต็ม</th>
                            <th>อีเมล</th>
                            <th>บทบาท</th>
                            <th>สถานะ</th>
                            <th>เข้าระบบล่าสุด</th>
                            <th>Session ที่ใช้งาน</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['user_id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($user['username']) ?></strong>
                                <?php if ($user['user_id'] == $_SESSION['user_id']): ?>
                                    <span class="badge bg-primary ms-1">คุณ</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($user['full_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="badge bg-<?= $user['user_role'] == 'admin' ? 'danger' : ($user['user_role'] == 'faculty_staff' ? 'success' : 'secondary') ?>">
                                    <?= getRoleDisplayName($user['user_role']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span class="badge bg-success">ใช้งานได้</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">ถูกระงับ</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['last_login']): ?>
                                    <?= date('d/m/Y H:i', strtotime($user['last_login'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">ยังไม่เคยเข้าระบบ</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['active_sessions'] > 0): ?>
                                    <span class="badge bg-info"><?= $user['active_sessions'] ?> session</span>
                                <?php else: ?>
                                    <span class="text-muted">ไม่มี</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                        <!-- Toggle Status Button -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                            <button type="submit" class="btn btn-<?= $user['is_active'] ? 'warning' : 'success' ?>" 
                                                    onclick="return confirm('คุณต้องการเปลี่ยนสถานะผู้ใช้นี้หรือไม่?')">
                                                <i class="bi bi-<?= $user['is_active'] ? 'pause' : 'play' ?>"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <!-- Reset Password Button -->
                                    <button type="button" class="btn btn-info" 
                                            onclick="showResetPasswordModal(<?= $user['user_id'] ?>, '<?= htmlspecialchars($user['username']) ?>')">
                                        <i class="bi bi-key"></i>
                                    </button>
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">เพิ่มผู้ใช้ใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="action" value="add_user">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">ชื่อผู้ใช้</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">อีเมล</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">ชื่อเต็ม</label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">รหัสผ่าน</label>
                        <input type="password" class="form-control" name="password" required 
                               minlength="<?= PASSWORD_MIN_LENGTH ?>">
                        <div class="form-text">รหัสผ่านต้องมีอย่างน้อย <?= PASSWORD_MIN_LENGTH ?> ตัวอักษร</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="user_role" class="form-label">บทบาท</label>
                        <select class="form-select" name="user_role" required>
                            <option value="">เลือกบทบาท</option>
                            <option value="admin">ผู้ดูแลระบบ</option>
                            <option value="faculty_staff">เจ้าหน้าที่คณะ</option>
                            <option value="guest">ผู้ใช้ทั่วไป</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success">เพิ่มผู้ใช้</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">รีเซ็ตรหัสผ่าน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="action" value="reset_password">
                    <input type="hidden" name="user_id" id="reset_user_id">
                    
                    <p>กำลังรีเซ็ตรหัสผ่านสำหรับผู้ใช้: <strong id="reset_username"></strong></p>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">รหัสผ่านใหม่</label>
                        <input type="password" class="form-control" name="new_password" required 
                               minlength="<?= PASSWORD_MIN_LENGTH ?>">
                        <div class="form-text">รหัสผ่านต้องมีอย่างน้อย <?= PASSWORD_MIN_LENGTH ?> ตัวอักษร</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-warning">รีเซ็ตรหัสผ่าน</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script>
function showResetPasswordModal(userId, username) {
    document.getElementById('reset_user_id').value = userId;
    document.getElementById('reset_username').textContent = username;
    new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
}
</script>

<?php require_once 'includes/footer.php'; ?>