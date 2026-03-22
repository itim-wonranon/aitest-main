<?php 
require_once 'includes/session_check.php'; 
// Available for teachers and admins
check_role(['admin', 'teacher']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานเวลาเรียน - สาธิตวิทยา</title>
    
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <link rel="stylesheet" href="css/style.css">
    <style>
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
        .flagged-row {
            background-color: #ffe5e5 !important;
        }
        @media print {
            .wrapper, .sidebar, #header-placeholder, .card-header, #filterForm {
                display: none !important;
            }
            body { background-color: #fff; margin: 0; padding: 0; }
            #content { width: 100%; padding: 0; margin: 0; }
            .card { border: none !important; box-shadow: none !important; }
            .table { border: 1px solid #dee2e6 !important; }
            .table td, .table th { background-color: #fff !important; color: #000 !important; }
            /* Show a title for print */
            #printTitle { display: block !important; margin-bottom: 20px; }
            /* Avoid breaking rows */
            tr { page-break-inside: avoid; }
        }
        #printTitle { display: none; }
    </style>
</head>
<body>

    <div class="wrapper">
        <div id="sidebar-placeholder"></div>

        <div id="content">
            <div id="header-placeholder"></div>

            <main class="container-fluid px-4 pb-4" id="mainSection">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-chart-pie me-2"></i>รายงานสรุปเวลาเรียนและสถิติ</h2>
                    <button class="btn btn-outline-primary shadow-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> พิมพ์รายงาน / PDF
                    </button>
                </div>
                
                <h3 id="printTitle" class="text-center fw-bold">รายงานสถิติเวลาเรียน</h3>

                <!-- Filters -->
                <div class="card shadow-sm border-0 mb-4" id="filterForm">
                    <div class="card-body bg-light rounded">
                        <form id="reportForm" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">ระดับชั้น</label>
                                <select name="class_level" id="class_level" class="form-select form-select-sm" required>
                                    <option value="">-- เลือกชั้น --</option>
                                    <option value="1">มัธยมศึกษาปีที่ 1</option>
                                    <option value="2">มัธยมศึกษาปีที่ 2</option>
                                    <option value="3">มัธยมศึกษาปีที่ 3</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">ห้อง</label>
                                <select name="room" id="room" class="form-select form-select-sm" required>
                                    <option value="">-- ห้อง --</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold small">รายวิชา</label>
                                <select name="subject_id" id="subject_id" class="form-select form-select-sm" required>
                                    <option value="">-- เลือกวิชา --</option>
                                    <!-- Options via JS -->
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold"><i class="fas fa-search"></i> ประมวลผล</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="reportContent" style="display:none;">
                    
                    <div class="alert alert-info border-0 shadow-sm mb-4">
                        <i class="fas fa-info-circle me-2"></i> ข้อมูลตารางเรียนทั้งหมด <strong id="totalSessions">0</strong> คาบ (คิดวิเคราะห์ข้อมูลจากนักเรียนชั้น <span id="lblClass"></span> วิชา <span id="lblSubject"></span>)
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-top-0 border-end-0 border-bottom-0 border-primary border-5 shadow-sm h-100 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                                จำนวนนักเรียนทั้งหมด</div>
                                            <div class="h3 mb-0 fw-bold text-dark" id="statTotalStudents">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300" style="opacity:0.3"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-top-0 border-end-0 border-bottom-0 border-success border-5 shadow-sm h-100 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                                เข้าเรียนเฉลี่ย (ทั้งห้อง)</div>
                                            <div class="h3 mb-0 fw-bold text-dark" id="statAvgAttendance">0%</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-chart-line fa-2x text-gray-300" style="opacity:0.3"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-top-0 border-end-0 border-bottom-0 border-danger border-5 shadow-sm h-100 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">
                                                นักเรียนที่เข้าเรียนต่ำกว่า 80% (เสี่ยง มส.)</div>
                                            <div class="h3 mb-0 fw-bold text-dark" id="statFlaggedStudents">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300" style="color:#e74a3b; opacity:0.5"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-top-0 border-end-0 border-bottom-0 border-warning border-5 shadow-sm h-100 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                                สถิติการลารวมห้อง</div>
                                            <div class="h3 mb-0 fw-bold text-dark" id="statTotalLeaves">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-bed fa-2x text-gray-300" style="opacity:0.3"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts and Table -->
                    <div class="row mb-4">
                        <div class="col-lg-4 mb-4">
                            <div class="card shadow-sm h-100 border-0">
                                <div class="card-header bg-white fw-bold py-3"><i class="fas fa-chart-pie me-2 text-primary"></i> สัดส่วนสถานะการเข้าเรียนรวม</div>
                                <div class="card-body d-flex justify-content-center align-items-center">
                                    <div style="width: 100%; max-width: 300px;">
                                        <canvas id="attendancePieChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-8 mb-4">
                            <div class="card shadow-sm h-100 border-0">
                                <div class="card-header bg-white fw-bold py-3">
                                    <i class="fas fa-list me-2 text-primary"></i> สถิติรายบุคคล
                                    <span class="badge bg-danger ms-2"><i class="fas fa-exclamation-circle"></i> สีแดง = เสี่ยง มส. (< 80%)</span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped mb-0 text-center align-middle" id="analyticsTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>รหัสนักเรียน</th>
                                                    <th>ชื่อ-สกุล</th>
                                                    <th class="text-success" title="มา">มา</th>
                                                    <th class="text-warning" title="สาย">สาย</th>
                                                    <th class="text-danger" title="ขาด">ขาด</th>
                                                    <th class="text-info" title="ป่วย">ลาป่วย</th>
                                                    <th class="text-primary" title="กิจ">ลากิจ</th>
                                                    <th>% เข้าเรียน</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Dynamic Content -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </main>

            <div id="footer-placeholder"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script src="js/script.js"></script>

    <script>
        let myPieChart = null;

        $(document).ready(function() {
            loadSubjects();

            $('#reportForm').on('submit', function(e) {
                e.preventDefault();
                generateReport();
            });
        });

        function loadSubjects() {
            $.ajax({
                url: 'api/schedules.php',
                type: 'GET',
                data: { action: 'get_subjects' },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        let options = '<option value="">-- เลือกวิชา --</option>';
                        res.data.forEach(sub => {
                            options += `<option value="${sub.id}">${sub.subject_code} - ${sub.subject_name}</option>`;
                        });
                        $('#subject_id').html(options);
                    }
                }
            });
        }

        function generateReport() {
            const class_level = $('#class_level').val();
            const room = $('#room').val();
            const subject_id = $('#subject_id').val();
            const subject_name = $('#subject_id option:selected').text();

            $('#lblClass').text(`ม.${class_level}/${room}`);
            $('#lblSubject').text(subject_name);

            const btn = $('#reportForm button[type=submit]');
            const origHtml = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

            $.ajax({
                url: 'api/attendance_analytics.php',
                type: 'GET',
                data: { 
                    action: 'get_dashboard_stats',
                    class_level: class_level,
                    room: room,
                    subject_id: subject_id
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        $('#reportContent').slideDown();
                        renderDashboard(res.data, res.total_sessions);
                    } else {
                        alert('Error: ' + res.message);
                    }
                    btn.html(origHtml).prop('disabled', false);
                },
                error: function() {
                    alert('เกิดข้อผิดพลาดในการโหลดข้อมูล');
                    btn.html(origHtml).prop('disabled', false);
                }
            });
        }

        function renderDashboard(students, total_sessions) {
            $('#totalSessions').text(total_sessions);
            
            let totalStudents = students.length;
            let flaggedStudents = 0;
            let totalLeaves = 0;
            let sumPercentage = 0;

            let overallCounts = {
                present: 0,
                late: 0,
                absent: 0,
                sick_leave: 0,
                business_leave: 0
            };

            let tableHtml = '';

            students.forEach(st => {
                let s = st.stats;
                
                overallCounts.present += s.present;
                overallCounts.late += s.late;
                overallCounts.absent += s.absent;
                overallCounts.sick_leave += s.sick_leave;
                overallCounts.business_leave += s.business_leave;

                totalLeaves += (s.sick_leave + s.business_leave);
                
                let percent = st.attendance_percent;
                sumPercentage += percent;

                if (st.is_flagged) {
                    flaggedStudents++;
                }

                let rowClass = st.is_flagged ? 'flagged-row text-danger fw-bold' : '';
                let badgeClass = st.is_flagged ? 'bg-danger' : 'bg-success';

                tableHtml += `
                    <tr class="${rowClass}">
                        <td class="fw-bold">${st.student_code}</td>
                        <td class="text-start">${st.full_name}</td>
                        <td>${s.present}</td>
                        <td>${s.late}</td>
                        <td>${s.absent}</td>
                        <td>${s.sick_leave}</td>
                        <td>${s.business_leave}</td>
                        <td><span class="badge ${badgeClass} fs-6">${percent}%</span></td>
                    </tr>
                `;
            });

            $('#analyticsTable tbody').html(tableHtml);

            // Update Cards
            let avgAtt = totalStudents > 0 ? (sumPercentage / totalStudents).toFixed(2) : 0;
            $('#statTotalStudents').text(totalStudents);
            $('#statAvgAttendance').text(`${avgAtt}%`);
            $('#statFlaggedStudents').text(flaggedStudents);
            $('#statTotalLeaves').text(totalLeaves);

            // Update Chart
            updatePieChart(overallCounts);
        }

        function updatePieChart(data) {
            Chart.defaults.font.family = "'Prompt', sans-serif";
            
            if (myPieChart) {
                myPieChart.destroy();
            }

            const ctx = document.getElementById('attendancePieChart').getContext('2d');
            myPieChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['มา', 'สาย', 'ขาด', 'ลาป่วย', 'ลากิจ'],
                    datasets: [{
                        data: [data.present, data.late, data.absent, data.sick_leave, data.business_leave],
                        backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b', '#36b9cc', '#4e73df'],
                        hoverBackgroundColor: ['#17a673', '#dda20a', '#be2617', '#2c9faf', '#2e59d9'],
                        hoverBorderColor: 'rgba(234, 236, 244, 1)',
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        caretPadding: 10,
                    },
                    legend: {
                        display: true,
                        position: 'bottom'
                    },
                    cutoutPercentage: 70,
                }
            });
        }
    </script>
</body>
</html>
