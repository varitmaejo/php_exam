<?php
/**
 * ไฟล์: activity_logs.php
 * ฟังก์ชัน: แสดงบันทึกกิจกรรมของระบบ (เฉพาะผู้ดูแลระบบ)
 */

$page_title = "บันทึกกิจกรรม";

require_once 'config.php';
require_once 'auth_config.php';

// ตรวจสอบสิทธิ์ - เฉพาะผู้ดูแลระบบเท่านั้น
requireAuth('admin');

// การตั้งค่าการแบ่งหน้า
$records_per_page = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $records_per_page;

// ตัวกรองข้อมูล
$filter_user = isset($_GET['user']) ? trim($_GET['user']) : '';
$filter_action = isset($_GET['action']) ? trim($_GET['action']) : '';
$filter_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filter_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// สร้าง WHERE clause สำหรับการกรอง
$where_conditions = [];
$params = [];

if (!empty($filter_user)) {
    $where_conditions[] = "(u.username LIKE ? OR u.full_name LIKE ?)";
    $params[] = "%$filter_user%";
    $params[] = "%$filter_user%";
}

if (!empty($filter_action)) {
    $where_conditions[] = "al.action LIKE ?";
    $params[] = "%$filter_action%";
}

if (!empty($filter_date_from)) {
    $where_conditions[] = "DATE(al.created_at) >= ?";
    $params[] = $filter_date_from;
}

if (!empty($filter_date_to)) {
    $where_conditions[] = "DATE(al.created_at) <= ?";
    $params[] = $filter_date_to;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

try {
    // นับจำนวนรายการทั้งหมด
    $count_sql = "
        SELECT COUNT(*) 
        FROM activity_logs al 
        LEFT JOIN users u ON al.user_id = u.user_id 
        $where_clause
    ";
    
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $records_per_page);

    // ดึงข้อมูลกิจกรรม
    $sql = "
        SELECT 
            al.log_id,
            al.user_id,
            al.action,
            al.description,
            al.ip_address,
            al.created_at,
            u.username,
            u.full_name,
            u.user_role
        FROM activity_logs al 
        LEFT JOIN users u ON al.user_id = u.user_id 
        $where_clause
        ORDER BY al.created_at DESC 
        LIMIT $records_per_page OFFSET $offset
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $activities = $stmt->fetchAll();

    // ดึงรายการ action ที่มีในระบบ
    $actions_stmt = $pdo->query("SELECT DISTINCT action FROM activity_logs ORDER BY action");
    $available_actions = $actions_stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    $activities = [];
    $available_actions = [];
    $total_records = 0;
    $total_pages = 0;
}

require_once 'includes/protected_header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-clipboard-data me-2"></i>บันทึกกิจกรรม</h2>
        <div>
            <span class="badge bg-info">ทั้งหมด <?= number_format($total_records) ?> รายการ</span>
        </div>
    </div>

    <!-- ตัวกรองข้อมูล -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-funnel me-2"></i>กรองข้อมูล
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="user" class="form-label">ผู้ใช้</label>
                    <input type="text" class="form-control" name="user" id="user" 
                           placeholder="ชื่อผู้ใช้หรือชื่อเต็ม" value="<?= htmlspecialchars($filter_user) ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="action" class="form-label">กิจกรรม</label>
                    <select class="form-select" name="action" id="action">
                        <option value="">ทั้งหมด</option>
                        <?php foreach ($available_actions as $action): ?>
                            <option value="<?= htmlspecialchars($action) ?>" 
                                    <?= $filter_action === $action ? 'selected' : '' ?>>
                                <?= htmlspecialchars($action) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="date_from" class="form-label">วันที่เริ่มต้น</label>
                    <input type="date" class="form-control" name="date_from" id="date_from" 
                           value="<?= htmlspecialchars($filter_date_from) ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="date_to" class="form-label">วันที่สิ้นสุด</label>
                    <input type="date" class="form-control" name="date_to" id="date_to" 
                           value="<?= htmlspecialchars($filter_date_to) ?>">
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <div class="d-grid w-100 gap-1">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> กรอง
                        </button>
                        <a href="activity_logs.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-clockwise"></i> รีเซ็ต
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ตารางแสดงข้อมูล -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">รายการกิจกรรม</h5>
            <?php if ($total_pages > 1): ?>
                <small class="text-muted">
                    หน้า <?= $page ?> จาก <?= $total_pages ?> 
                    (แสดง <?= min($records_per_page, $total_records - $offset) ?> จาก <?= $total_records ?> รายการ)
                </small>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <?php if (empty($activities)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <p class="mt-3 text-muted">ไม่พบบันทึกกิจกรรม</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>วันที่/เวลา</th>
                                <th>ผู้ใช้</th>
                                <th>กิจกรรม</th>
                                <th>รายละเอียด</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= date('d/m/Y', strtotime($activity['created_at'])) ?></div>
                                    <small class="text-muted"><?= date('H:i:s', strtotime($activity['created_at'])) ?></small>
                                </td>
                                <td>
                                    <?php if ($activity['username']): ?>
                                        <div class="fw-bold"><?= htmlspecialchars($activity['full_name']) ?></div>
                                        <small class="text-muted">@<?= htmlspecialchars($activity['username']) ?></small>
                                        <br>
                                        <span class="badge bg-<?= $activity['user_role'] == 'admin' ? 'danger' : ($activity['user_role'] == 'faculty_staff' ? 'success' : 'secondary') ?> badge-sm">
                                            <?= getRoleDisplayName($activity['user_role']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">ผู้ใช้ที่ถูกลบ</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= getActionBadgeClass($activity['action']) ?>">
                                        <?= htmlspecialchars($activity['action']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 300px;" 
                                         title="<?= htmlspecialchars($activity['description']) ?>">
                                        <?= htmlspecialchars($activity['description']) ?>
                                    </div>
                                </td>
                                <td>
                                    <code><?= htmlspecialchars($activity['ip_address']) ?></code>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="card-footer">
            <nav aria-label="Activity logs pagination">
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <!-- Previous Page -->
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Page Numbers -->
                    <?php
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    if ($start > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">1</a>
                        </li>
                        <?php if ($start > 2): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif;
                    endif;
                    
                    for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor;
                    
                    if ($end < $total_pages): 
                        if ($end < $total_pages - 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>"><?= $total_pages ?></a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Next Page -->
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>

<?php
/**
 * ฟังก์ชันช่วยสำหรับกำหนดสี badge ตาม action
 */
function getActionBadgeClass($action) {
    switch (strtoupper($action)) {
        case 'LOGIN':
            return 'success';
        case 'LOGOUT':
            return 'secondary';
        case 'LOGIN_FAILED':
            return 'danger';
        case 'ADD_USER':
        case 'ADD_STUDENT':
        case 'ADD_CURRICULUM':
            return 'primary';
        case 'EDIT_USER':
        case 'EDIT_STUDENT':
        case 'EDIT_CURRICULUM':
            return 'info';
        case 'DELETE_USER':
        case 'DELETE_STUDENT':
        case 'DELETE_CURRICULUM':
            return 'danger';
        case 'BACKUP':
            return 'warning';
        case 'RESET_PASSWORD':
        case 'TOGGLE_USER_STATUS':
            return 'warning';
        default:
            return 'light';
    }
}

require_once 'includes/footer.php'; 
?>