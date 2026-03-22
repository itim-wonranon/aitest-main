<?php 
require_once 'includes/session_check.php'; 
check_role(['admin', 'teacher']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกเวลาเรียนรายคาบ - สาธิตวิทยา</title>
    
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/style.css">
    <style>
        .student-check-card {
            transition: all 0.2s;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .student-check-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .status-present { border-color: #1cc88a; background-color: #e8f9f3; }
        .status-late { border-color: #f6c23e; background-color: #fef9e8; }
        .status-absent { border-color: #e74a3b; background-color: #fdeceb; }
        .status-sick_leave { border-color: #36b9cc; background-color: #ebf8fa; }
        .status-business_leave { border-color: #4e73df; background-color: #edf1fc; }
        
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.8rem;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .action-btns .btn {
            font-size: 0.8rem;
            padding: 4px 8px;
            margin: 2px;
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
                    <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-clipboard-check me-2"></i>บันทึกเวลาเรียนรายคาบ (Daily Attendance)</h2>
                </div>

                <div id="alertBox"></div>

                <!-- Step 1: Schedule Selection -->
                <div class="card shadow-sm border-0 mb-4" id="step1">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-calendar-day me-2"></i> เลือกตารางสอนเพื่อเช็กชื่อ</div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="form-label mb-0 small fw-bold">วันที่:</label>
                            <input type="date" id="filter_date" class="form-control form-control-sm" value="<?php echo date('Y-m-d'); ?>" onchange="loadSchedules()">
                        </div>
                    </div>
                    <div class="card-body bg-light">
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="schedulesContainer">
                            <!-- Schedules will be dynamically loaded here -->
                            <div class="col mx-auto text-center py-4 text-muted w-100" id="loadingSchedules">
                                <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                                <p>กำลังโหลดตารางเรียน...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Attendance Entry -->
                <div class="card shadow-sm border-0 mb-4" id="step2" style="display:none;">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center sticky-top" style="top: 0; z-index: 10;">
                        <div>
                            <button class="btn btn-sm btn-outline-secondary me-3" onclick="backToStep1()"><i class="fas fa-arrow-left"></i> กลับ</button>
                            <span class="fw-bold fs-5" id="attendanceTitle">เช็กชื่อ ม.1/1 - คณิตศาสตร์</span>
                            <div class="small text-muted mt-1" id="attendanceTimeInfo"><i class="far fa-clock"></i> เวลา 08:30 - 09:20</div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-warning text-dark me-2" id="lateWarningBadge" style="display:none;">
                                <i class="fas fa-exclamation-triangle"></i> เกินเวลา 15 นาที ระบบจะส่งแจ้งเตือนสาย
                            </span>
                            <button class="btn btn-primary shadow-sm px-4" id="btnSaveBulk" onclick="saveBulkAttendance()">
                                <i class="fas fa-save me-1"></i> บันทึกข้อมูลและส่งแจ้งเตือน
                            </button>
                        </div>
                    </div>
                    <div class="card-body bg-light p-4">
                        
                        <!-- Quick Set All -->
                        <div class="d-flex gap-2 mb-4 p-3 bg-white rounded shadow-sm border align-items-center">
                            <span class="fw-bold me-2">ตั้งค่าทั้งห้องเป็น:</span>
                            <button class="btn btn-sm btn-outline-success" onclick="setAll('present')"><i class="fas fa-check"></i> มาเรียน (Present)</button>
                            <button class="btn btn-sm btn-outline-warning" onclick="setAll('late')"><i class="fas fa-clock"></i> สาย (Late)</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="setAll('absent')"><i class="fas fa-times"></i> ขาด (Absent)</button>
                        </div>

                        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-3" id="studentsContainer">
                            <!-- Student Cards will go here -->
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
        let currentScheduleId = null;
        let selectedDate = null;
        let scheduleStartTime = null;

        $(document).ready(function() {
            loadSchedules();
        });

        function loadSchedules() {
            selectedDate = $('#filter_date').val();
            $('#schedulesContainer').html('<div class="col text-center w-100 text-muted"><i class="fas fa-circle-notch fa-spin fa-2x mb-2"></i><p>กำลังโหลด...</p></div>');
            
            $.ajax({
                url: 'api/attendance.php',
                type: 'GET',
                data: { action: 'get_teacher_schedules', date: selectedDate },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        renderSchedules(res.data);
                    } else {
                        showAlert('danger', res.message);
                    }
                }
            });
        }

        function renderSchedules(schedules) {
            if (schedules.length === 0) {
                $('#schedulesContainer').html(`
                    <div class="col w-100 text-center py-5 text-muted bg-white rounded border">
                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                        <h5>ไม่มีตารางสอนในวันนี้</h5>
                        <p>ตรวจสอบวันที่ที่เลือกอีกครั้ง หรือติดต่อ Admin</p>
                    </div>
                `);
                return;
            }

            let html = '';
            schedules.forEach(sch => {
                const isChecked = sch.is_checked;
                const badge = isChecked 
                    ? `<span class="badge bg-success position-absolute top-0 end-0 m-3"><i class="fas fa-check-circle"></i> เช็กชื่อแล้ว</span>` 
                    : `<span class="badge bg-secondary position-absolute top-0 end-0 m-3"><i class="fas fa-clock"></i> รอดำเนินการ</span>`;
                
                const cardClass = isChecked ? 'border-success' : 'border-primary';
                
                html += `
                    <div class="col">
                        <div class="card h-100 shadow-sm ${cardClass} position-relative" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'" onclick="openAttendance(${sch.id}, '${sch.start_time}')">
                            ${badge}
                            <div class="card-body pt-4">
                                <h5 class="fw-bold text-dark">${sch.subject_code} ${sch.subject_name}</h5>
                                <p class="mb-1 fw-bold text-primary"><i class="fas fa-users"></i> ชั้น ม.${sch.class_level}/${sch.room}</p>
                                <p class="mb-0 text-muted small"><i class="far fa-clock"></i> เวลา: ${sch.start_time.substring(0,5)} - ${sch.end_time.substring(0,5)}</p>
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#schedulesContainer').html(html);
        }

        function openAttendance(scheduleId, startTime) {
            currentScheduleId = scheduleId;
            scheduleStartTime = startTime;
            
            $('#step1').hide();
            $('#step2').show();
            $('#studentsContainer').html('<div class="text-center w-100 py-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
            window.scrollTo({ top: 0, behavior: 'smooth' });

            $.ajax({
                url: 'api/attendance.php',
                type: 'GET',
                data: { action: 'get_attendance_list', schedule_id: scheduleId, date: selectedDate },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        const info = res.schedule_info;
                        $('#attendanceTitle').text(`เช็กชื่อ ม.${info.class_level}/${info.room} - ${info.subject_name}`);
                        $('#attendanceTimeInfo').html(`<i class="far fa-clock"></i> เวลา ${info.start_time.substring(0,5)} - ${info.end_time.substring(0,5)} (วันที่: ${selectedDate})`);
                        
                        checkLateWarning();
                        renderStudents(res.data);
                    }
                }
            });
        }
        
        function checkLateWarning() {
            // Very simple warning check if recorded 15 mins after class start
            const now = new Date();
            const classTimeStr = selectedDate + 'T' + scheduleStartTime;
            const classStartTime = new Date(classTimeStr);
            
            // If today is the class date
            if (selectedDate === now.toISOString().split('T')[0]) {
                const diffMins = (now - classStartTime) / (1000 * 60);
                if (diffMins > 15) {
                    $('#lateWarningBadge').show();
                } else {
                    $('#lateWarningBadge').hide();
                }
            } else {
                $('#lateWarningBadge').hide();
            }
        }

        function renderStudents(students) {
            let html = '';
            
            const statusMap = {
                'present': { text: 'มา', icon: 'check', class: 'status-present', color: 'text-success' },
                'late': { text: 'สาย', icon: 'clock', class: 'status-late', color: 'text-warning' },
                'absent': { text: 'ขาด', icon: 'times', class: 'status-absent', color: 'text-danger' },
                'sick_leave': { text: 'ลาป่วย', icon: 'bed', class: 'status-sick_leave', color: 'text-info' },
                'business_leave': { text: 'ลากิจ', icon: 'briefcase', class: 'status-business_leave', color: 'text-primary' }
            };

            students.forEach((st, idx) => {
                let status = st.attendance_status || 'present';
                let stMap = statusMap[status];
                
                // Readonly style if it is mapped from Leave (usually teacher shouldn't override approved leave without checking admin)
                // But for flexibility, we allow override. We will just add a visual indicator.
                let leaveBadge = (st.note === 'ดึงข้อมูลจากใบลา') ? '<span class="badge bg-dark ms-1" style="font-size:0.6rem;"><i class="fas fa-lock"></i> อนุมัติการลาแล้ว</span>' : '';

                html += `
                    <div class="col student-col" data-id="${st.id}">
                        <div class="card h-100 px-3 py-3 student-check-card ${stMap.class}" id="card_${st.id}" data-status="${status}">
                            <div class="status-badge fw-bold ${stMap.color} bg-white shadow-sm" id="badge_${st.id}">
                                <i class="fas fa-${stMap.icon}"></i> ${stMap.text}
                            </div>
                            
                            <div class="d-flex align-items-center mb-3 mt-1">
                                <div class="bg-white rounded-circle d-flex justify-content-center align-items-center shadow-sm" style="width: 40px; height: 40px; border: 1px solid #ccc;">
                                    <i class="fas fa-user text-secondary"></i>
                                </div>
                                <div class="ms-3">
                                    <div class="fw-bold text-dark lh-sm">${st.full_name}</div>
                                    <div class="small text-muted lh-1">เลขที่ ${idx + 1} | รหัส ${st.student_code}</div>
                                    ${leaveBadge}
                                </div>
                            </div>

                            <div class="action-btns d-flex flex-wrap justify-content-center border-top pt-2 mt-auto">
                                <button type="button" class="btn btn-outline-success ${status === 'present' ? 'active' : ''}" onclick="setStatus(${st.id}, 'present')">มา</button>
                                <button type="button" class="btn btn-outline-warning text-dark ${status === 'late' ? 'active alert-warning fw-bold border-warning' : ''}" onclick="setStatus(${st.id}, 'late')">สาย</button>
                                <button type="button" class="btn btn-outline-danger ${status === 'absent' ? 'active' : ''}" onclick="setStatus(${st.id}, 'absent')">ขาด</button>
                                <button type="button" class="btn btn-outline-info text-dark ${status === 'sick_leave' ? 'active alert-info fw-bold border-info' : ''}" onclick="setStatus(${st.id}, 'sick_leave')">ป่วย</button>
                                <button type="button" class="btn btn-outline-primary ${status === 'business_leave' ? 'active' : ''}" onclick="setStatus(${st.id}, 'business_leave')">กิจ</button>
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#studentsContainer').html(html);
        }

        function setStatus(studentId, status) {
            const card = $(`#card_${studentId}`);
            
            // Remove active classes from buttons
            card.find('.action-btns .btn').removeClass('active alert-warning alert-info fw-bold border-warning border-info');
            
            // Add active class to clicked button
            let btnClass = '';
            if (status === 'late') btnClass = 'active alert-warning fw-bold border-warning';
            else if (status === 'sick_leave') btnClass = 'active alert-info fw-bold border-info';
            else btnClass = 'active';
            
            card.find(`button[onclick="setStatus(${studentId}, '${status}')"]`).addClass(btnClass);

            // Update card classes
            card.removeClass('status-present status-late status-absent status-sick_leave status-business_leave').addClass(`status-${status}`);
            card.attr('data-status', status);

            // Update badge
            const badge = $(`#badge_${studentId}`);
            badge.removeClass('text-success text-warning text-danger text-info text-primary');
            
            let html = '';
            if(status === 'present') { html = '<i class="fas fa-check"></i> มา'; badge.addClass('text-success'); }
            if(status === 'late') { html = '<i class="fas fa-clock"></i> สาย'; badge.addClass('text-warning'); }
            if(status === 'absent') { html = '<i class="fas fa-times"></i> ขาด'; badge.addClass('text-danger'); }
            if(status === 'sick_leave') { html = '<i class="fas fa-bed"></i> ลาป่วย'; badge.addClass('text-info'); }
            if(status === 'business_leave') { html = '<i class="fas fa-briefcase"></i> ลากิจ'; badge.addClass('text-primary'); }
            
            badge.html(html);
        }

        function setAll(status) {
            if(confirm(`ต้องการตั้งค่าให้นักเรียนทุกคนมีสถานะเป็น "${status}" ใช่หรือไม่? (ไม่รวมคนที่มีใบลาอนุมัติ)`)) {
                $('.student-col').each(function() {
                    let stId = $(this).data('id');
                    // Skip if locked (has approved leave)
                    if ($(this).find('.fa-lock').length === 0) {
                        setStatus(stId, status);
                    }
                });
            }
        }

        function saveBulkAttendance() {
            let students_data = {};
            let hasAbsentOrLate = false;

            $('.student-col').each(function() {
                let sId = $(this).data('id');
                let st = $(this).find(`div[id^='card_']`).attr('data-status');
                
                students_data[sId] = {
                    status: st,
                    note: $(this).find('.fa-lock').length > 0 ? 'ดึงข้อมูลจากใบลา' : '' // Simplistic note preservation
                };
                
                if (st === 'absent' || st === 'late') {
                    hasAbsentOrLate = true;
                }
            });

            const btn = $('#btnSaveBulk');
            const origHtml = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin me-1"></i> กำลังบันทึก...').prop('disabled', true);

            $.ajax({
                url: 'api/attendance.php',
                type: 'POST',
                data: {
                    action: 'save_bulk_attendance',
                    schedule_id: currentScheduleId,
                    date: selectedDate,
                    students_data: students_data
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        // Simulate Instant Alert System Push Notification
                        if (hasAbsentOrLate) {
                            showToastAlert('success', '<i class="fas fa-bell"></i>', 'บันทึกสำเร็จ ส่ง Push notification ให้นักเรียนที่ขาด/สายแล้ว (Simulated)');
                        } else {
                            showToastAlert('success', '<i class="fas fa-check-circle"></i>', res.message);
                        }
                        
                        setTimeout(() => {
                            backToStep1();
                        }, 2000);
                        
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

        function backToStep1() {
            $('#step2').hide();
            $('#step1').show();
            // Reload to update checkmarks
            loadSchedules(); 
            window.scrollTo({ top: 0, behavior: 'smooth' });
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
        
        function showToastAlert(type, icon, message) {
            const toastHtml = `
                <div id="liveToast" class="toast position-fixed top-0 end-0 m-4 align-items-center text-white bg-${type} border-0 show" role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 1055;">
                  <div class="d-flex">
                    <div class="toast-body fs-6 fw-bold">
                      ${icon} ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                  </div>
                </div>
            `;
            $('body').append(toastHtml);
            setTimeout(() => { 
                $('#liveToast').removeClass('show'); 
                setTimeout(() => $('#liveToast').remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>
