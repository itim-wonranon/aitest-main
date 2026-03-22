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
    <title>บันทึกผลการเรียน - สาธิตวิทยา</title>
    
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/style.css">
    <style>
        .spreadsheet-table {
            border-collapse: collapse;
            width: 100%;
        }
        .spreadsheet-table th, .spreadsheet-table td {
            border: 1px solid #ced4da;
            padding: 0;
            vertical-align: middle;
        }
        .spreadsheet-table th {
            padding: 8px;
            background-color: #f8f9fa;
        }
        .cell-input {
            width: 100%;
            height: 100%;
            border: none;
            padding: 8px;
            text-align: center;
            outline: none;
            background-color: transparent;
        }
        .cell-input:focus {
            background-color: #e7f1ff;
            box-shadow: inset 0 0 0 2px #0d6efd;
        }
        .cell-input.is-invalid {
            background-color: #f8d7da;
            color: #842029;
        }
        .cell-readonly {
            background-color: #e9ecef;
            font-weight: bold;
            text-align: center;
        }
        .published-badge {
            font-size: 0.75rem;
            position: absolute;
            top: 5px;
            right: 5px;
        }
        .table-responsive {
            overflow-x: auto;
            max-height: 600px;
            overflow-y: auto;
        }
        /* Sticky headers and first column */
        .spreadsheet-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
        }
        .spreadsheet-table tbody th {
            position: sticky;
            left: 0;
            z-index: 1;
            background-color: #fff;
        }
        .spreadsheet-table thead th:first-child {
            z-index: 3;
            left: 0;
        }
    </style>
</head>
<body>

    <div class="wrapper">
        <div id="sidebar-placeholder"></div>

        <div id="content">
            <div id="header-placeholder"></div>

            <main class="container-fluid px-4 pb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-edit me-2"></i>บันทึกผลการเรียน (Grade Entry)</h2>
                </div>

                <div id="alertBox"></div>

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
                            <div class="col-md-3">
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
                                <button class="btn btn-primary btn-sm w-100 fw-bold" onclick="loadStudentsAndConfig()">
                                    <i class="fas fa-search me-1"></i> โหลดรายชื่อนักเรียน
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4" id="tableCard" style="display: none;">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <div>
                            <span class="fw-bold fs-5 me-3" id="tableTitle">รายชื่อนักเรียนชั้น... วิชา...</span>
                            <span class="badge bg-secondary" id="publishStatusBadge">สถานะ: ร่าง (Draft)</span>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="saveScores(0)">
                                <i class="fas fa-save me-1"></i> บันทึกฉบับร่าง (Draft)
                            </button>
                            <button type="button" class="btn btn-success" onclick="publishScores()">
                                <i class="fas fa-bullhorn me-1"></i> ประกาศผล (Publish)
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table spreadsheet-table text-center mb-0" id="gradingTable">
                                <thead>
                                    <tr id="tableHeaderRow">
                                        <th width="5%" class="align-middle">เลขที่</th>
                                        <th width="10%" class="align-middle">รหัสนักเรียน</th>
                                        <th width="20%" class="text-start align-middle">ชื่อ-นามสกุล</th>
                                        <!-- Dynamic Score columns will go here -->
                                        <th width="10%" class="align-middle table-info">รวม<br><small>(100)</small></th>
                                        <th width="10%" class="align-middle table-warning">เกรด</th>
                                        <th width="8%" class="align-middle table-secondary">พิมพ์เกรด<br><small>('ร', 'มส')</small></th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                    <!-- Dynamic Rows via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Empty State -->
                <div id="noConfigState" style="display: none;" class="text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h4>วิชานี้ยังไม่ได้ตั้งค่าโครงสร้างคะแนน</h4>
                    <p class="text-muted">กรุณาไปที่เมนู "กำหนดโครงสร้างคะแนน" เพื่อตั้งค่าสัดส่วนคะแนนก่อนนำเข้าเกรด</p>
                    <a href="grading_config.php" class="btn btn-primary mt-2">ไปหน้าตั้งค่าโครงสร้างคะแนน</a>
                </div>

            </main>

            <div id="footer-placeholder"></div>
        </div>
    </div>
    
    <!-- Edit Reason Modal -->
    <div class="modal fade" id="reasonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-dark border-bottom-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-key me-2"></i>ยืนยันการแก้ไขข้อมูล</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p>ระบบตรวจพบว่าคะแนนชุดนี้ถูก <strong>"ประกาศผล (Publish)"</strong> ไปแล้ว การบันทึกหรือแก้ไขเพิ่มเติมจะถูกบันทึกประวัติ (Log) ตามนโยบายของโรงเรียน</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">เหตุผลการแก้ไข (เช่น สอบแก้ตัว, อัปเดตคะแนนตกหล่น): <span class="text-danger">*</span></label>
                        <input type="text" id="logReason" class="form-control" placeholder="กรอกเหตุผล...">
                    </div>
                    <input type="hidden" id="pendingActionIsPublish">
                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="button" class="btn btn-warning" onclick="confirmSaveWithReason()">ยืนยันบันทึกข้อมูล</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <script src="js/script.js"></script>

    <script>
        let currentConfig = null;
        let studentsData = [];
        let weightKeys = [];
        let currentlyPublished = false;

        $(document).ready(function() {
            // Fetch Subjects for dropdown
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

            // Delegate events for dynamic inputs
            $(document).on('input', '.score-input, .manual-grade-input', function() {
                const tr = $(this).closest('tr');
                calculateRow(tr);
            });
            
            // Allow arrow keys to navigate spreadsheet
            $(document).on('keydown', '.cell-input', function(e) {
                let currentTd = $(this).closest('td');
                let currentTr = $(this).closest('tr');
                let cellIndex = currentTd.index();

                if (e.key === 'ArrowUp') {
                    currentTr.prev().find(`td:eq(${cellIndex}) .cell-input`).focus();
                    e.preventDefault();
                } else if (e.key === 'ArrowDown') {
                    currentTr.next().find(`td:eq(${cellIndex}) .cell-input`).focus();
                    e.preventDefault();
                } else if (e.key === 'ArrowRight') {
                    currentTd.next().find('.cell-input').focus();
                    e.preventDefault();
                } else if (e.key === 'ArrowLeft') {
                    currentTd.prev().find('.cell-input').focus();
                    e.preventDefault();
                }
            });
        });

        function loadStudentsAndConfig() {
            const subject_id = $('#select_subject').val();
            const year = $('#filter_year').val();
            const semester = $('#filter_semester').val();
            const class_level = $('#filter_class_level').val();
            const room = $('#filter_room').val();

            if (!subject_id) {
                showAlert('warning', 'กรุณาเลือกวิชาก่อน');
                return;
            }

            // Step 1: Load config to build columns
            $.ajax({
                url: 'api/grading.php',
                type: 'GET',
                data: { action: 'get_config', subject_id, academic_year: year, semester },
                dataType: 'json',
                success: function(res) {
                    if (res.data && res.data.weight_criteria) {
                        currentConfig = res.data;
                        buildTableHeaders(currentConfig.weight_criteria);
                        
                        // Step 2: Load students & existing scores
                        fetchStudentScores(class_level, room, subject_id, year, semester);
                        
                    } else {
                        $('#tableCard').hide();
                        $('#noConfigState').show();
                    }
                }
            });
        }

        function buildTableHeaders(weights) {
            $('#noConfigState').hide();
            $('#tableCard').show();
            
            weightKeys = Object.keys(weights);
            
            let html = `
                <th width="3%" class="align-middle text-center" style="background-color: #e9ecef;">ที่</th>
                <th width="10%" class="align-middle" style="background-color: #e9ecef;">รหัสนักเรียน</th>
                <th width="18%" class="text-start align-middle" style="background-color: #e9ecef; z-index:2;">ชื่อ-นามสกุล</th>
            `;
            
            weightKeys.forEach(key => {
                html += `<th class="align-middle text-center text-primary" style="background-color: #f8f9fa;">${key} <br><small>(${weights[key]})</small></th>`;
            });
            
            html += `
                <th width="8%" class="align-middle text-center" style="background-color: #cff4fc;">รวม<br><small>(100)</small></th>
                <th width="8%" class="align-middle text-center" style="background-color: #fff3cd;">เกรด</th>
                <th width="6%" class="align-middle text-center" style="background-color: #e2e3e5;">พิเศษ<br><small>('ร', 'มส')</small></th>
            `;
            
            $('#tableHeaderRow').html(html);
        }

        function fetchStudentScores(class_level, room, subject_id, year, semester) {
            $.ajax({
                url: 'api/grading.php',
                type: 'GET',
                data: { action: 'get_students_scores', subject_id, academic_year: year, semester, class_level, room },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        studentsData = res.data;
                        renderTableBody();
                    }
                }
            });
        }

        function renderTableBody() {
            let html = '';
            currentlyPublished = false;

            studentsData.forEach((student, index) => {
                // Determine if this was published
                let isPub = student.score_record && parseInt(student.score_record.is_published) === 1;
                if(isPub) currentlyPublished = true;
                
                let scoresData = student.score_record && student.score_record.scores_data ? student.score_record.scores_data : {};
                let manualGrade = ''; // We will store 'ร' or 'มส' in the separate box, but in DB they save grade as 'ร'
                
                // If actual grade from DB is a string like 'ร', we put it in manual field
                if(student.score_record && ['ร', 'มส', 'ข'].includes(student.score_record.grade)) {
                    manualGrade = student.score_record.grade;
                }

                html += `<tr data-studentid="${student.id}">`;
                html += `<th class="text-center align-middle bg-light text-muted">${index + 1}</th>`;
                html += `<td>${student.student_code}</td>`;
                html += `<th class="text-start align-middle fw-normal">${student.full_name}</th>`;
                
                weightKeys.forEach(key => {
                    let val = scoresData[key] !== undefined ? scoresData[key] : '';
                    let max = currentConfig.weight_criteria[key];
                    html += `<td><input type="text" class="cell-input score-input" data-key="${key}" data-max="${max}" value="${val}"></td>`;
                });
                
                html += `<td><input type="text" class="cell-input cell-readonly sum-score" readonly></td>`;
                html += `<td><input type="text" class="cell-input cell-readonly calc-grade text-danger fw-bold fs-5" readonly></td>`;
                html += `<td><input type="text" class="cell-input manual-grade-input text-danger fw-bold" value="${manualGrade}" placeholder="..."></td>`;
                
                html += `</tr>`;
            });

            $('#tableBody').html(html);
            
            // Set title and UI
            const subj = $('#select_subject option:selected').text();
            $('#tableTitle').text(`ชั้น ม.${$('#filter_class_level').val()}${$('#filter_room option:selected').text()} - ${subj}`);
            
            if(currentlyPublished) {
                $('#publishStatusBadge').removeClass('bg-secondary').addClass('bg-success').text('สถานะ: ประกาศผลแล้ว (Published)');
            } else {
                $('#publishStatusBadge').removeClass('bg-success').addClass('bg-secondary').text('สถานะ: ร่าง (Draft)');
            }
            
            // Calculate initial runs
            $('#tableBody tr').each(function() {
                calculateRow($(this));
            });
        }

        function calculateRow(tr) {
            let total = 0;
            let hasError = false;
            let manualGrade = tr.find('.manual-grade-input').val().trim().toUpperCase();

            tr.find('.score-input').each(function() {
                let input = $(this);
                let val = parseFloat(input.val());
                let max = parseFloat(input.data('max'));
                
                if (!isNaN(val)) {
                    if (val > max || val < 0) {
                        input.addClass('is-invalid');
                        hasError = true;
                    } else {
                        input.removeClass('is-invalid');
                        total += val;
                    }
                } else {
                    input.removeClass('is-invalid');
                }
            });

            tr.find('.sum-score').val(hasError ? 'ERR' : total.toFixed(2));

            // Determine Grade based on threshold
            let finalGrade = '';
            
            if (manualGrade && ['ร', 'มส', 'ข'].includes(manualGrade)) {
                // Manual override
                finalGrade = manualGrade;
                tr.find('.calc-grade').val('-');
            } else if (!hasError) {
                // Auto calculate
                let thres = currentConfig.grade_thresholds;
                // Since total might be float, we compare normally
                if (total >= thres['4']) finalGrade = '4';
                else if (total >= thres['3.5']) finalGrade = '3.5';
                else if (total >= thres['3']) finalGrade = '3';
                else if (total >= thres['2.5']) finalGrade = '2.5';
                else if (total >= thres['2']) finalGrade = '2';
                else if (total >= thres['1.5']) finalGrade = '1.5';
                else if (total >= thres['1']) finalGrade = '1';
                else finalGrade = '0';
                
                tr.find('.calc-grade').val(finalGrade);
            } else {
                tr.find('.calc-grade').val('-');
            }
        }

        function publishScores() {
            if($('.score-input.is-invalid').length > 0) {
                showAlert('danger', 'พบข้อมูลผิดพลาด (ช่องสีแดง คะแนนเกินเกณฑ์) กรุณาแก้ไขก่อน Publish');
                return;
            }
            
            if (currentlyPublished) {
                // Need reason modal because we are modifying something already published
                $('#pendingActionIsPublish').val('1');
                $('#logReason').val('');
                $('#reasonModal').modal('show');
            } else {
                if(confirm('ต้องการ "ประกาศผลเรียน" สำหรับนักเรียนห้องนี้ใช่หรือไม่?\n\nเมื่อประกาศแล้ว ระบบจะเปิดให้นักเรียน/ผู้ปกครองมองเห็นผลเกรดได้ทันที')) {
                    executeSave(1, 'Initial Publish');
                }
            }
        }

        function saveScores(isPublish) {
            if($('.score-input.is-invalid').length > 0) {
                showAlert('danger', 'พบข้อมูลผิดพลาด (ช่องสีแดง คะแนนเกินเกณฑ์) กรุณาแก้ไขก่อนบันทึก');
                return;
            }
            
            if (currentlyPublished) {
                // Need reason modal
                $('#pendingActionIsPublish').val(isPublish);
                $('#logReason').val('');
                $('#reasonModal').modal('show');
            } else {
                executeSave(isPublish, 'Draft Update');
            }
        }
        
        function confirmSaveWithReason() {
            const reason = $('#logReason').val().trim();
            if(!reason) {
                alert('กรุณากรอกเหตุผลการแก้ไขด้วยครับ');
                return;
            }
            const isPub = $('#pendingActionIsPublish').val() === '1' ? 1 : 0;
            executeSave(isPub, reason);
            $('#reasonModal').modal('hide');
        }

        function executeSave(isPublish, reason) {
            let students_data = {};

            $('#tableBody tr').each(function() {
                const tr = $(this);
                const s_id = tr.data('studentid');
                
                let scoresObj = {};
                tr.find('.score-input').each(function() {
                    let key = $(this).data('key');
                    let val = $(this).val();
                    scoresObj[key] = val !== '' ? parseFloat(val) : '';
                });

                let total = tr.find('.sum-score').val();
                if(total === 'ERR' || total === '') total = '';
                
                let grade = tr.find('.manual-grade-input').val().trim().toUpperCase();
                if(!grade) {
                    grade = tr.find('.calc-grade').val();
                }

                students_data[s_id] = {
                    scores_data: scoresObj,
                    total_score: total,
                    grade: grade
                };
            });

            const payload = {
                action: 'save_scores',
                subject_id: $('#select_subject').val(),
                academic_year: $('#filter_year').val(),
                semester: $('#filter_semester').val(),
                is_published: isPublish,
                log_reason: reason,
                students_data: students_data
            };

            $.ajax({
                url: 'api/grading.php',
                type: 'POST',
                data: payload,
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        showAlert('success', res.message);
                        if(isPublish === 1) {
                            currentlyPublished = true;
                            $('#publishStatusBadge').removeClass('bg-secondary').addClass('bg-success').text('สถานะ: ประกาศผลแล้ว (Published)');
                        }
                    } else {
                        showAlert('danger', res.message);
                    }
                },
                error: function() {
                    showAlert('danger', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                }
            });
        }

        function showAlert(type, message) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            $('#alertBox').html(alertHtml);
            setTimeout(() => { $('.alert').alert('close'); }, 5000);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
</body>
</html>
