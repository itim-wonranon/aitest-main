<?php require_once 'includes/session_check.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบบริหารจัดการโรงเรียนสาธิตวิทยา - Dashboard</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg">

    <!-- Google Fonts: Prompt -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="wrapper">
        <!-- Sidebar Placeholder -->
        <div id="sidebar-placeholder"></div>

        <!-- Content Area -->
        <div id="content">

            <!-- Header Placeholder -->
            <div id="header-placeholder"></div>

            <!-- Main Workspace: Dashboard -->
            <main class="container-fluid px-4 pb-4">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 mb-0 text-dark fw-bold">แดชบอร์ดภาพรวมผู้บริหาร (Overview Dashboard)</h2>
                    <div>
                        <button class="btn btn-theme shadow-sm"><i class="fas fa-download fa-sm text-dark-50 me-1"></i>
                            สร้างรายงาน</button>
                    </div>
                </div>

                <!-- Row 1: Quick Stats Cards (Module 1 Overviews) -->
                <div class="row mb-4">
                    <!-- Teachers -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card kpi-card border-left-primary h-100 py-2">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col ms-2">
                                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">จำนวนบุคลากรครู
                                        </div>
                                        <div class="h5 mb-0 fw-bold text-dark">120 ท่าน</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-chalkboard-teacher kpi-icon text-muted"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Students -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card kpi-card border-left-success h-100 py-2">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col ms-2">
                                        <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                            จำนวนนักเรียนทั้งหมด</div>
                                        <div class="h5 mb-0 fw-bold text-dark">2,450 คน</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-graduate kpi-icon text-muted"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subjects -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card kpi-card border-left-info h-100 py-2">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col ms-2">
                                        <div class="text-xs fw-bold text-info text-uppercase mb-1">รายวิชาที่เปิดสอน
                                        </div>
                                        <div class="h5 mb-0 fw-bold text-dark">85 วิชา</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-book kpi-icon text-muted"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Classrooms -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card kpi-card border-left-warning h-100 py-2">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col ms-2">
                                        <div class="text-xs fw-bold text-warning text-uppercase mb-1">จำนวนห้องเรียน
                                        </div>
                                        <div class="h5 mb-0 fw-bold text-dark">60 ห้อง</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-door-open kpi-icon text-muted"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Charts (Module 6 Analytics) -->
                <div class="row">
                    <!-- Chart 1: Attendance Today -->
                    <div class="col-xl-4 col-lg-5 mb-4">
                        <div class="card shadow-sm h-100 kpi-card">
                            <div
                                class="card-header py-3 bg-white d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 fw-bold text-dark">สถิติการมาเรียนประจำวัน (Today)</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-pie overflow-hidden position-relative" style="height: 300px;">
                                    <canvas id="attendanceChart"></canvas>
                                </div>
                                <div class="mt-4 text-center small">
                                    <span class="me-2"><i class="fas fa-circle text-success"></i> มาเรียน</span>
                                    <span class="me-2"><i class="fas fa-circle text-danger"></i> ขาด</span>
                                    <span class="me-2"><i class="fas fa-circle text-warning"></i> สาย</span>
                                    <span class="me-2"><i class="fas fa-circle text-info"></i> ลา</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart 2: Grade Distribution -->
                    <div class="col-xl-8 col-lg-7 mb-4">
                        <div class="card shadow-sm h-100 kpi-card">
                            <div
                                class="card-header py-3 bg-white d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 fw-bold text-dark">ภาพรวมผลการเรียน (Grade Distribution) ประจำภาคการศึกษา
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-bar position-relative" style="height: 300px;">
                                    <canvas id="gradeChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </main>

            <!-- Footer Placeholder -->
            <div id="footer-placeholder"></div>

        </div>
    </div>

    <!-- jQuery (Required for dynamic include and AJAX) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Popper.js and Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom Scripts -->
    <script src="js/script.js"></script>

</body>

</html>