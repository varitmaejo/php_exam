<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานสถิตินักศึกษา</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>รายงานสถิตินักศึกษา</h2>
            <a href="index.php" class="btn btn-secondary">กลับหน้าหลัก</a>
        </div>

        <?php
        require_once 'config.php';

        // สถิติทั่วไป
        $total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
        $male_students = $pdo->query("SELECT COUNT(*) FROM students WHERE gender = 'ชาย'")->fetchColumn();
        $female_students = $pdo->query("SELECT COUNT(*) FROM students WHERE gender = 'หญิง'")->fetchColumn();

        // สถิติตามหลักสูตร
        $curriculum_stats = $pdo->query("
            SELECT c.curriculum_name, COUNT(s.student_id) as student_count
            FROM curriculum c
            LEFT JOIN students s ON c.curriculum_id = s.curriculum_id
            GROUP BY c.curriculum_id, c.curriculum_name
            ORDER BY student_count DESC
        ")->fetchAll();
        ?>

        <!-- สถิติทั่วไป -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">นักศึกษาทั้งหมด</h5>
                        <h2><?= $total_students ?> คน</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">นักศึกษาชาย</h5>
                        <h2><?= $male_students ?> คน</h2>
                        <small><?= $total_students > 0 ? round(($male_students/$total_students)*100, 1) : 0 ?>%</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">นักศึกษาหญิง</h5>
                        <h2><?= $female_students ?> คน</h2>
                        <small><?= $total_students > 0 ? round(($female_students/$total_students)*100, 1) : 0 ?>%</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- กราฟแบ่งตามเพศ -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>สัดส่วนนักศึกษาตามเพศ</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="genderChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>จำนวนนักศึกษาตามหลักสูตร</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="curriculumChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- ตารางสถิติตามหลักสูตร -->
        <div class="card">
            <div class="card-header">
                <h5>รายละเอียดตามหลักสูตร</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>หลักสูตร</th>
                                <th>จำนวนนักศึกษา</th>
                                <th>เปอร์เซ็นต์</th>
                                <th>กราฟ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($curriculum_stats as $stat): ?>
                                <tr>
                                    <td><?= htmlspecialchars($stat['curriculum_name']) ?></td>
                                    <td><?= $stat['student_count'] ?> คน</td>
                                    <td><?= $total_students > 0 ? round(($stat['student_count']/$total_students)*100, 1) : 0 ?>%</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?= $total_students > 0 ? ($stat['student_count']/$total_students)*100 : 0 ?>%">
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

    <script>
        // กราฟแบ่งตามเพศ
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        new Chart(genderCtx, {
            type: 'pie',
            data: {
                labels: ['ชาย', 'หญิง'],
                datasets: [{
                    data: [<?= $male_students ?>, <?= $female_students ?>],
                    backgroundColor: ['#36A2EB', '#FF6384']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // กราฟตามหลักสูตร
        const curriculumCtx = document.getElementById('curriculumChart').getContext('2d');
        new Chart(curriculumCtx, {
            type: 'bar',
            data: {
                labels: [<?php foreach($curriculum_stats as $stat) echo "'" . addslashes($stat['curriculum_name']) . "',"; ?>],
                datasets: [{
                    label: 'จำนวนนักศึกษา',
                    data: [<?php foreach($curriculum_stats as $stat) echo $stat['student_count'] . ','; ?>],
                    backgroundColor: '#4BC0C0'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>