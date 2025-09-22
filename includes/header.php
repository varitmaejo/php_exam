<?php
// ไฟล์: includes/header.php
?>
<!DOCTYPE html>
<html lang='th'>

<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>
        <?php if(isset($page_title))
        { 
            echo $page_title;
        }
        ?>
    </title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <!-- Bootstrap Icons -->
    <link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css' rel='stylesheet'>
</head>

<body>
    <nav class='navbar navbar-expand-lg navbar-dark bg-primary'>
        <div class='container'>
            <a class='navbar-brand' href='index.php'>
                <i class='bi bi-mortarboard-fill me-2'></i>
                ระบบจัดการนักศึกษา
            </a>

            <button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#navbarNav'>
                <span class='navbar-toggler-icon'></span>
            </button>

            <div class='collapse navbar-collapse' id='navbarNav'>
                <div class='navbar-nav ms-auto'>
                    <a class='nav-link' href='index.php'>
                        <i class='bi bi-house-fill me-1'></i>หน้าหลัก
                    </a>

                    <div class='nav-item dropdown'>
                        <a class='nav-link dropdown-toggle' href='#' role='button' data-bs-toggle='dropdown'>
                            <i class='bi bi-people-fill me-1'></i>นักศึกษา
                        </a>
                        <ul class='dropdown-menu'>
                            <li>
                                <a class='dropdown-item' href='list_students.php'>
                                    <i class='bi bi-list-ul me-2'></i>รายการนักศึกษา
                                </a>
                            </li>
                            <li>
                                <a class='dropdown-item' href='add_student.php'>
                                    <i class='bi bi-person-plus me-2'></i>เพิ่มนักศึกษา
                                </a>
                            </li>
                            <li>
                                <hr class='dropdown-divider'>
                            </li>
                            <li>
                                <a class='dropdown-item' href='search.php'>
                                    <i class='bi bi-search me-2'></i>ค้นหานักศึกษา
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class='nav-item dropdown'>
                        <a class='nav-link dropdown-toggle' href='#' role='button' data-bs-toggle='dropdown'>
                            <i class='bi bi-book-fill me-1'></i>หลักสูตร
                        </a>
                        <ul class='dropdown-menu'>
                            <li>
                                <a class='dropdown-item' href='list_curriculum.php'>
                                    <i class='bi bi-journal-text me-2'></i>รายการหลักสูตร
                                </a>
                            </li>
                            <li>
                                <a class='dropdown-item' href='add_curriculum.php'>
                                    <i class='bi bi-plus-circle me-2'></i>เพิ่มหลักสูตร
                                </a>
                            </li>
                        </ul>
                    </div>

                    <a class='nav-link' href='statistics.php'>
                        <i class='bi bi-bar-chart-fill me-1'></i>สถิติ
                    </a>

                </div>
            </div>
        </div>
    </nav>
    <div class='container mt-4'>