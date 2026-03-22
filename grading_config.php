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
    <title>จัดการโครงสร้างคะแนน - สาธิตวิทยา</title>
    
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="wrapper">
        <div id="sidebar-placeholder"></div>

        <div id="content">
            <div id="header-placeholder"></div>

            <main class="container-fluid px-4 pb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-sliders-h me-2"></i>กำหนดโครงสร้างคะแนน (Grading Configuration)</h2>
                </div>

                <div id="alertBox"></div>

                <div class="row">
                    <!-- Left Column: Selection -->
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0 mb-4 h-100">
                            <div class="card-header bg-white fw-bold">
                                1. เลือกรายวิชา
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">เทอม / ปีการศึกษา</label>
                                    <div class="input-group">
                                        <select id="filter_semester" class="form-select form-select-sm">
                                            <option value="1">เทอม 1</option>
                                            <option value="2">เทอม 2</option>
                                        </select>
                                        <select id="filter_year" class="form-select form-select-sm">
                                            <option value="2567">2567</option>
                                            <option value="2568" selected>2568</option>
                                            <option value="2569">2569</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small">เลือกวิชา</label>
                                    <select id="select_subject" class="form-select form-select-sm">
                                        <option value="">-- กำลังโหลดข้อมูลวิชา --</option>
                                    </select>
                                </div>
                                <button class="btn btn-primary w-100 mt-2" onclick="loadConfig()">
                                    <i class="fas fa-search me-1"></i> ดึงข้อมูลโครงสร้าง
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Configuration Form -->
                    <div class="col-md-8">
                        <div class="card shadow-sm border-0 mb-4 h-100" id="configCard" style="display: none;">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <span class="fw-bold"><i class="fas fa-cog me-2"></i>2. ตั้งค่าสัดส่วนคะแนนและเกณฑ์</span>
                                <span class="badge bg-info text-dark" id="subjectBadge">วิชา...</span>
                            </div>
                            <div class="card-body">
                                <form id="configForm">
                                    <input type="hidden" id="config_subject_id">
                                    <input type="hidden" id="config_year">
                                    <input type="hidden" id="config_semester">

                                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary">สัดส่วนคะแนน (Weighting)</h5>
                                    <p class="text-muted small mb-3">กำหนดคะแนนเต็มสำหรับแต่ละส่วน รวมแล้วต้องได้ 100 คะแนนพอดี</p>
                                    
                                    <div id="weightingContainer">
                                        <!-- Dynamic inputs for weighting -->
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-md-8">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addWeightRow()">
                                                <i class="fas fa-plus"></i> เพิ่มช่องคะแนน
                                            </button>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <h5 class="mb-0 fw-bold">รวม: <span id="totalWeight" class="text-success">0</span> / 100</h5>
                                            <small id="weightWarning" class="text-danger" style="display:none;">ต้องรวมได้ 100 คะแนน</small>
                                        </div>
                                    </div>

                                    <h5 class="fw-bold mt-5 mb-3 border-bottom pb-2 text-primary">เกณฑ์ตัดเกรด (Grade Thresholds)</h5>
                                    <p class="text-muted small mb-3">กำหนดคะแนนขั้นต่ำ (คะแนนรวม) ที่จะได้เกรดแต่ละระดับ</p>
                                    
                                    <div class="row g-3 align-items-center mb-4">
                                        <div class="col-md-3 col-6"><div class="input-group input-group-sm"><span class="input-group-text fw-bold bg-light">เกรด 4</span><input type="number" class="form-control grade-input" data-grade="4" value="80" disabled></div></div>
                                        <div class="col-md-3 col-6"><div class="input-group input-group-sm"><span class="input-group-text fw-bold bg-light">เกรด 3.5</span><input type="number" class="form-control grade-input" data-grade="3.5" value="75"></div></div>
                                        <div class="col-md-3 col-6"><div class="input-group input-group-sm"><span class="input-group-text fw-bold bg-light">เกรด 3</span><input type="number" class="form-control grade-input" data-grade="3" value="70"></div></div>
                                        <div class="col-md-3 col-6"><div class="input-group input-group-sm"><span class="input-group-text fw-bold bg-light">เกรด 2.5</span><input type="number" class="form-control grade-input" data-grade="2.5" value="65"></div></div>
                                        <div class="col-md-3 col-6"><div class="input-group input-group-sm"><span class="input-group-text fw-bold bg-light">เกรด 2</span><input type="number" class="form-control grade-input" data-grade="2" value="60"></div></div>
                                        <div class="col-md-3 col-6"><div class="input-group input-group-sm"><span class="input-group-text fw-bold bg-light">เกรด 1.5</span><input type="number" class="form-control grade-input" data-grade="1.5" value="55"></div></div>
                                        <div class="col-md-3 col-6"><div class="input-group input-group-sm"><span class="input-group-text fw-bold bg-light">เกรด 1</span><input type="number" class="form-control grade-input" data-grade="1" value="50"></div></div>
                                        <div class="col-md-3 col-6"><div class="input-group input-group-sm"><span class="input-group-text fw-bold bg-light text-danger">เกรด 0</span><input type="text" class="form-control bg-light" value="ต่ำกว่าเกรด 1" disabled></div></div>
                                    </div>

                                    <div class="mt-4 text-end">
                                        <button type="submit" class="btn btn-theme px-4" id="btnSaveConfig">
                                            <i class="fas fa-save me-1"></i> บันทึกโครงสร้างคะแนน
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Placeholder when no subject selected -->
                        <div class="card shadow-sm border-0 mb-4 h-100 d-flex align-items-center justify-content-center bg-light" id="emptyStateCard" style="min-height: 400px;">
                            <div class="text-center text-muted">
                                <i class="fas fa-mouse-pointer fa-3x mb-3 text-secondary"></i>
                                <h5>กรุณาเลือกวิชาด้านซ้ายมือ</h5>
                                <p>เพื่อกำหนดหรือแก้ไขสัดส่วนคะแนนและเกณฑ์ตัดเกรด</p>
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
    
    <script src="js/script.js"></script>

    <script>
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

            // Calculate total weight on input change
            $(document).on('input', '.weight-input', function() {
                recalculateTotalWeight();
            });

            $('#configForm').on('submit', function(e) {
                e.preventDefault();
                saveConfig();
            });
        });

        function loadConfig() {
            const subject_id = $('#select_subject').val();
            const year = $('#filter_year').val();
            const semester = $('#filter_semester').val();

            if (!subject_id) {
                showAlert('warning', 'กรุณาเลือกวิชา');
                return;
            }

            const subject_text = $('#select_subject option:selected').text();
            $('#subjectBadge').text(`${subject_text} (เทอม ${semester}/${year})`);
            
            $('#config_subject_id').val(subject_id);
            $('#config_year').val(year);
            $('#config_semester').val(semester);

            // Fetch Config
            $.ajax({
                url: 'api/grading.php',
                type: 'GET',
                data: { 
                    action: 'get_config', 
                    subject_id: subject_id, 
                    academic_year: year, 
                    semester: semester 
                },
                dataType: 'json',
                success: function(res) {
                    $('#emptyStateCard').hide();
                    $('#configCard').show();
                    
                    if (res.data) {
                        // Populate existing data
                        const weights = res.data.weight_criteria;
                        const thresholds = res.data.grade_thresholds;
                        
                        renderWeightRows(weights);
                        
                        if(thresholds) {
                            $('.grade-input[data-grade="3.5"]').val(thresholds['3.5'] || 75);
                            $('.grade-input[data-grade="3"]').val(thresholds['3'] || 70);
                            $('.grade-input[data-grade="2.5"]').val(thresholds['2.5'] || 65);
                            $('.grade-input[data-grade="2"]').val(thresholds['2'] || 60);
                            $('.grade-input[data-grade="1.5"]').val(thresholds['1.5'] || 55);
                            $('.grade-input[data-grade="1"]').val(thresholds['1'] || 50);
                        }
                    } else {
                        // Default rows if none exists
                        const defaultWeights = {
                            "จิตพิสัย/พฤติกรรม": 10,
                            "คะแนนเก็บระหว่างภาค": 40,
                            "สอบกลางภาค": 20,
                            "สอบปลายภาค": 30
                        };
                        renderWeightRows(defaultWeights);
                    }
                    recalculateTotalWeight();
                }
            });
        }

        function renderWeightRows(weights) {
            let html = '';
            for (const [key, val] of Object.entries(weights)) {
                html += `
                    <div class="row g-2 mb-2 weight-row">
                        <div class="col-md-7">
                            <input type="text" class="form-control form-control-sm weight-name" value="${key}" placeholder="ชื่อหัวคะแนน (เช่น สอบกลางภาค)" required>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control weight-input" value="${val}" min="1" max="100" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeWeightRow(this)"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                `;
            }
            $('#weightingContainer').html(html);
        }

        function addWeightRow() {
            const html = `
                <div class="row g-2 mb-2 weight-row">
                    <div class="col-md-7">
                        <input type="text" class="form-control form-control-sm weight-name" value="" placeholder="ชื่อหัวคะแนน (เช่น สอบปลายภาค)" required>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control weight-input" value="0" min="1" max="100" required>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeWeightRow(this)"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            `;
            $('#weightingContainer').append(html);
            recalculateTotalWeight();
        }

        function removeWeightRow(btn) {
            $(btn).closest('.weight-row').remove();
            recalculateTotalWeight();
        }

        function recalculateTotalWeight() {
            let total = 0;
            $('.weight-input').each(function() {
                total += parseInt($(this).val()) || 0;
            });
            
            $('#totalWeight').text(total);
            
            if (total === 100) {
                $('#totalWeight').removeClass('text-danger').addClass('text-success');
                $('#weightWarning').hide();
                $('#btnSaveConfig').prop('disabled', false);
            } else {
                $('#totalWeight').removeClass('text-success').addClass('text-danger');
                $('#weightWarning').show();
                $('#btnSaveConfig').prop('disabled', true);
            }
        }

        function saveConfig() {
            // Check weights exactly 100
            let total = parseInt($('#totalWeight').text());
            if (total !== 100) {
                showAlert('danger', 'สัดส่วนคะแนนรวมต้องเท่ากับ 100 พอดี');
                return;
            }

            // Build weight_criteria JSON
            let weight_criteria = {};
            // To maintain order, we could use array, but for simplicity we rely on unique names
            // Let's use array of objects to maintain frontend rendering order and allow duplicate names if needed (though bad UI)
            // Actually, requirements often use key-value. Let's use simple key-value, append indexing if duplicate.
            let counter = 1;
            $('.weight-row').each(function() {
                let name = $(this).find('.weight-name').val().trim();
                let val = parseInt($(this).find('.weight-input').val()) || 0;
                
                if(!name) { name = "คะแนน " + counter; }
                if(weight_criteria[name]) { name = name + " (" + counter + ")"; }
                
                weight_criteria[name] = val;
                counter++;
            });

            // Build Grade Thresholds JSON
            let grade_thresholds = {
                "4": parseInt($('.grade-input[data-grade="4"]').val()) || 80,
                "3.5": parseInt($('.grade-input[data-grade="3.5"]').val()) || 75,
                "3": parseInt($('.grade-input[data-grade="3"]').val()) || 70,
                "2.5": parseInt($('.grade-input[data-grade="2.5"]').val()) || 65,
                "2": parseInt($('.grade-input[data-grade="2"]').val()) || 60,
                "1.5": parseInt($('.grade-input[data-grade="1.5"]').val()) || 55,
                "1": parseInt($('.grade-input[data-grade="1"]').val()) || 50,
                "0": 0
            };

            const payload = {
                action: 'save_config',
                subject_id: $('#config_subject_id').val(),
                academic_year: $('#config_year').val(),
                semester: $('#config_semester').val(),
                weight_criteria: JSON.stringify(weight_criteria),
                grade_thresholds: JSON.stringify(grade_thresholds)
            };

            const btn = $('#btnSaveConfig');
            const origHtml = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i> กำลังบันทึก...').prop('disabled', true);

            $.ajax({
                url: 'api/grading.php',
                type: 'POST',
                data: payload,
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        showAlert('success', res.message);
                    } else {
                        showAlert('danger', res.message);
                    }
                    btn.html(origHtml).prop('disabled', false);
                },
                error: function() {
                    showAlert('danger', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                    btn.html(origHtml).prop('disabled', false);
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
            setTimeout(() => { $('.alert').alert('close'); }, 4000);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
</body>
</html>
