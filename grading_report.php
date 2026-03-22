<?php

require_once 'includes/session_check.php';

// Only Admin and Teacher can access
check_role(['admin', 'teacher']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานผลการเรียน - สาธิตวิทยา</title>
    
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="wrapper">
        <div id="sidebar-placeholder"></div>

        <div id="content">
            <div id="header-placeholder"></div>

            <main class="container-fluid px-4 pb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-chart-pie me-2"></i>รายงานและสถิติผลการเรียน (Grade Dashboard)</h2>
                    <button class="btn btn-outline-secondary shadow-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> พิมพ์ / Export PDF
                    </button>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body bg-light rounded">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">ปีการศึกษา/เทอม</label>
                                <div class="input-group">
                                    <select id="filter_year" class="form-select form-select-sm">
                                        <option value="2567">2567</option>
                                        <option value="2568" selected>2568</option>
                                    </select>
                                    <select id="filter_semester" class="form-select form-select-sm">
                                        <option value="1">เทอม 1</option>
                                        <option value="2">เทอม 2</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">เลือกวิชา</label>
                                <select id="select_subject" class="form-select form-select-sm">
                                    <!-- Loaded via JS -->
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">ระดับชั้น</label>
                                <select id="filter_class_level" class="form-select form-select-sm">
                                    <option value="1">ม.1</option>
                                    <option value="2">ม.2</option>
                                    <option value="3">ม.3</option>
                                    <option value="4">ม.4</option>
                                    <option value="5">ม.5</option>
                                    <option value="6">ม.6</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">ห้องเรียน</label>
                                <select id="filter_room" class="form-select form-select-sm">
                                    <option value="1">/1</option>
                                    <option value="2">/2</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary btn-sm w-100 fw-bold" onclick="generateReport()">
                                    <i class="fas fa-chart-bar me-1"></i> ดูสถิติห้องนี้
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="reportContainer" style="display: none;">
                    
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm border-start border-primary border-4 py-2">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">จำนวนนักเรียนทั้งหมด</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="statTotalStudents">0 คน</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300" style="color:#dddfeb;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm border-start border-success border-4 py-2">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">สถานะส่งเกรดแล้ว</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="statPublished">0 คน</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clipboard-check fa-2x text-gray-300" style="color:#dddfeb;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm border-start border-info border-4 py-2">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">คะแนนรวมเฉลี่ยของห้อง</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="statAvgScore">0 / 100</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calculator fa-2x text-gray-300" style="color:#dddfeb;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm border-start border-warning border-4 py-2">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">ติด '0', 'ร', 'มส'</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="statFailed">0 คน</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300" style="color:#dddfeb;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts & Tables -->
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header bg-white fw-bold"><i class="fas fa-chart-pie me-2"></i>การกระจายตัวของเกรด (Grade Distribution)</div>
                                <div class="card-body d-flex justify-content-center align-items-center">
                                    <div style="width: 80%; max-height: 300px;">
                                        <canvas id="gradePieChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header bg-white fw-bold"><i class="fas fa-list-ol me-2"></i>สรุปจำนวนนักเรียนตามเกรด</div>
                                <div class="card-body">
                                    <table class="table table-bordered table-sm text-center">
                                        <thead class="table-light">
                                            <tr>
                                                <th>เกรด</th>
                                                <th>จำนวนนักเรียน (คน)</th>
                                                <th>ร้อยละ (%)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="gradeBreakdownTbody">
                                            <!-- Dynamically filled -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div id="emptyState" class="text-center py-5">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">กรุณาเลือกวิชาและห้องเรียนเพื่อดูรายงานสถิติ</h5>
                </div>

            </main>

            <div id="footer-placeholder"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="js/script.js"></script>

    <script>
        let pieChart = null;

        $(document).ready(function() {
            // Fetch Subjects
            $.ajax({
                url: 'api/schedules.php', 
                type: 'GET',
                data: { action: 'get_options' },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        const subjectOpts = res.data.subjects.map(s => `<option value="${s.id}">${s.subject_code} ${s.subject_name}</option>`);
                        $('#select_subject').html('<option value="">-- เลือกวิชา --</option>' + subjectOpts.join(''));
                    }
                }
            });
        });

        function generateReport() {
            const subject_id = $('#select_subject').val();
            const year = $('#filter_year').val();
            const semester = $('#filter_semester').val();
            const class_level = $('#filter_class_level').val();
            const room = $('#filter_room').val();

            if (!subject_id) {
                alert('กรุณาเลือกวิชาก่อน');
                return;
            }

            $.ajax({
                url: 'api/grading.php',
                type: 'GET',
                data: { action: 'get_students_scores', subject_id, academic_year: year, semester, class_level, room },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        const students = res.data;
                        processStatistics(students);
                        $('#emptyState').hide();
                        $('#reportContainer').show();
                    }
                }
            });
        }

        function processStatistics(students) {
            let totalStudents = students.length;
            let publishedCount = 0;
            let failedCount = 0;
            let totalScoreSum = 0;
            let studentsWithScoreCount = 0;

            let gradesCount = {
                '4': 0, '3.5': 0, '3': 0, '2.5': 0, 
                '2': 0, '1.5': 0, '1': 0, '0': 0, 
                'ร': 0, 'มส': 0, '-': 0
            };

            students.forEach(s => {
                if (s.score_record) {
                    if (parseInt(s.score_record.is_published) === 1) publishedCount++;
                    
                    let grade = s.score_record.grade;
                    if (grade === null || grade === '') grade = '-';
                    
                    if (gradesCount[grade] !== undefined) {
                        gradesCount[grade]++;
                    } else {
                        gradesCount['-']++; // Catch all
                    }

                    if (['0', 'ร', 'มส'].includes(grade)) {
                        failedCount++;
                    }

                    if (s.score_record.total_score !== null) {
                        totalScoreSum += parseFloat(s.score_record.total_score);
                        studentsWithScoreCount++;
                    }
                } else {
                    gradesCount['-']++;
                }
            });

            $('#statTotalStudents').text(totalStudents + ' คน');
            $('#statPublished').text(publishedCount + ' คน');
            $('#statFailed').text(failedCount + ' คน');
            
            let avgScore = studentsWithScoreCount > 0 ? (totalScoreSum / studentsWithScoreCount).toFixed(2) : '0';
            $('#statAvgScore').text(avgScore + ' / 100');

            renderTable(gradesCount, totalStudents);
            renderChart(gradesCount);
        }

        function renderTable(gradesCount, total) {
            const gradeLabels = ['4', '3.5', '3', '2.5', '2', '1.5', '1', '0', 'ร', 'มส', '-'];
            let html = '';
            
            gradeLabels.forEach(g => {
                let count = gradesCount[g];
                let pct = total > 0 ? ((count / total) * 100).toFixed(1) : '0.0';
                
                let textClass = "";
                if(['0', 'ร', 'มส'].includes(g)) textClass = "text-danger fw-bold";
                else if (g === '-') textClass = "text-muted";
                
                let gText = g === '-' ? 'รอกรอกผล' : g;

                html += `<tr>
                    <td class="${textClass}">${gText}</td>
                    <td>${count}</td>
                    <td>${pct}%</td>
                </tr>`;
            });
            $('#gradeBreakdownTbody').html(html);
        }

        function renderChart(gradesCount) {
            const ctx = document.getElementById('gradePieChart').getContext('2d');
            
            if(pieChart) {
                pieChart.destroy();
            }

            const data = {
                labels: ['เกรด 4', 'เกรด 3-3.5', 'เกรด 2-2.5', 'เกรด 1-1.5', 'เกรด 0/ร/มส', 'ยังไม่มีผล'],
                datasets: [{
                    data: [
                        gradesCount['4'],
                        gradesCount['3'] + gradesCount['3.5'],
                        gradesCount['2'] + gradesCount['2.5'],
                        gradesCount['1'] + gradesCount['1.5'],
                        gradesCount['0'] + gradesCount['ร'] + gradesCount['มส'],
                        gradesCount['-']
                    ],
                    backgroundColor: [
                        '#1cc88a', // 4 = green
                        '#36b9cc', // 3 = cyan
                        '#4e73df', // 2 = blue
                        '#f6c23e', // 1 = yellow
                        '#e74a3b', // 0 = red
                        '#eaecf4'  // none = gray
                    ],
                    hoverOffset: 4
                }]
            };

            pieChart = new Chart(ctx, {
                type: 'pie',
                data: data,
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }
    </script>
</body>
</html>
