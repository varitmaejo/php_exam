<?php
require_once 'config.php';
require_once 'functions.php';

// ตรวจสอบการเข้าถึง (ในระบบจริงควรมีการ authentication)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'backup') {
        try {
            $backup_file = 'backups/student_db_' . date('Y-m-d_H-i-s') . '.sql';
            $backup_dir = dirname($backup_file);
            
            if (!file_exists($backup_dir)) {
                mkdir($backup_dir, 0777, true);
            }
            
            // คำสั่ง mysqldump
            $command = "mysqldump --user=root --password= --host=localhost student_db > $backup_file";
            exec($command, $output, $return_code);
            
            if ($return_code == 0) {
                log_activity('BACKUP', 'Database backup created: ' . $backup_file);
                $success_message = "สำรองข้อมูลเรียบร้อยแล้ว: $backup_file";
            } else {
                throw new Exception('ไม่สามารถสำรองข้อมูลได้');
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            log_activity('BACKUP_ERROR', $error_message);
        }
    }
}

// ดึงรายการไฟล์สำรอง
$backup_files = [];
if (is_dir('backups')) {
    $backup_files = array_diff(scandir('backups'), ['.', '..']);
    rsort($backup_files); // เรียงจากใหม่ไปเก่า
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สำรองและกู้คืนข้อมูล</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>สำรองและกู้คืนข้อมูล</h2>
            <a href="index.php" class="btn btn-secondary">กลับหน้าหลัก</a>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>

        <!-- สำรองข้อมูล -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>สำรองข้อมูล</h5>
            </div>
            <div class="card-body">
                <p>สำรองข้อมูลทั้งหมดในระบบเป็นไฟล์ SQL</p>
                <form method="POST">
                    <input type="hidden" name="action" value="backup">
                    <button type="submit" class="btn btn-primary" 
                            onclick="return confirm('คุณต้องการสำรองข้อมูลหรือไม่?')">
                        สำรองข้อมูลเดิม
                    </button>
                </form>
            </div>
        </div>

        <!-- รายการไฟล์สำรอง -->
        <div class="card">
            <div class="card-header">
                <h5>ไฟล์สำรองข้อมูล</h5>
            </div>
            <div class="card-body">
                <?php if (empty($backup_files)): ?>
                    <p class="text-muted">ไม่มีไฟล์สำรองข้อมูล</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ไฟล์</th>
                                    <th>ขนาด</th>
                                    <th>วันที่สร้าง</th>
                                    <th>การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($backup_files as $file): ?>
                                    <?php
                                    $file_path = "backups/$file";
                                    $file_size = file_exists($file_path) ? number_format(filesize($file_path) / 1024, 2) . ' KB' : 'ไม่ทราบ';
                                    $file_date = file_exists($file_path) ? date('d/m/Y H:i:s', filemtime($file_path)) : 'ไม่ทราบ';
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($file) ?></td>
                                        <td><?= $file_size ?></td>
                                        <td><?= $file_date ?></td>
                                        <td>
                                            <a href="backups/<?= urlencode($file) ?>" 
                                               class="btn btn-success btn-sm" download>ดาวน์โหลด</a>
                                            <a href="?delete=<?= urlencode($file) ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบไฟล์นี้?')">ลบ</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// ลบไฟล์สำรอง
if (isset($_GET['delete'])) {
    $file_to_delete = 'backups/' . basename($_GET['delete']);
    if (file_exists($file_to_delete)) {
        unlink($file_to_delete);
        log_activity('BACKUP_DELETE', 'Backup file deleted: ' . $_GET['delete']);
        header('Location: backup.php');
        exit;
    }
}
?>