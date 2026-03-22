<?php 
require_once 'includes/session_check.php'; 
check_role(['admin']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการตารางเรียน - สาธิตวิทยา</title>
    
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/style.css">
    <style>
        .drag-item {
            cursor: grab;
            padding: 8px 12px;
            margin-bottom: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            transition: all 0.2s;
        }
        .drag-item:active { cursor: grabbing; }
        .drag-item:hover { background-color: #e9ecef; }
        
        .droppable-area {
            min-height: 80px;
            border: 2px dashed #dee2e6;
            border-radius: 4px;
            padding: 5px;
            background-color: #fff;
            transition: background-color 0.2s;
        }
        .droppable-area.drag-over {
            background-color: #e2e3e5;
            border-color: #secondary;
        }
        
        .schedule-card {
            background-color: #e7f1ff;
            border-left: 4px solid #0d6efd;
            padding: 8px;
            font-size: 0.85rem;
            margin-bottom: 4px;
            border-radius: 4px;
            position: relative;
        }
        .schedule-card .btn-delete {
            position: absolute;
            top: 2px;
            right: 2px;
            cursor: pointer;
            color: #dc3545;
            display: none;
        }
        .schedule-card:hover .btn-delete { display: block; }
        
        .time-header {
            width: 120px;
            text-align: center;
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .day-header {
            width: 100px;
            text-align: center;
            font-weight: bold;
            background-color: #f8f9fa;
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
                    <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-calendar-alt me-2"></i>ระบบจัดการตารางเรียน (Drag & Drop)</h2>
                    <div>
                        <button class="btn btn-outline-secondary shadow-sm me-2" onclick="window.print()">
                            <i class="fas fa-print me-1"></i> พิมพ์ / Export PDF
                        </button>
                    </div>
                </div>

                <div id="alertBox"></div>

                <div class="row">
                    <!-- Sidebar: Core Data Mapping items to drag -->
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 mb-4 h-100">
                            <div class="card-header bg-white fw-bold">
                                <i class="fas fa-book me-2"></i> ข้อมูลรายวิชา (ลากเพื่อจัดตาราง)
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
                                    <label class="form-label fw-bold small">เลือกกลุ่มนักเรียน (ห้อง)</label>
                                    <select id="select_classroom" class="form-select form-select-sm">
                                        <option value="">-- เลือกห้องเรียน --</option>
                                        <!-- Populated via JS -->
                                    </select>
                                </div>
                                <hr>
                                
                                <label class="form-label fw-bold small">ลากบล็อกวิชาไปวางในตาราง</label>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="search_subject" placeholder="ค้นหาวิชา...">
                                </div>
                                
                                <div id="draggables_container" style="max-height: 400px; overflow-y: auto;">
                                    <!-- Draggable subjects + teachers + rooms form -->
                                    <form id="dragConfigForm" class="p-2 border rounded bg-light mb-3">
                                        <div class="mb-2">
                                            <label class="form-label small mb-1">1. เลือกวิชา</label>
                                            <select id="drag_subject" class="form-select form-select-sm">
                                                <!-- Populated via JS -->
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small mb-1">2. เลือกครูผู้สอน</label>
                                            <select id="drag_teacher" class="form-select form-select-sm">
                                                <!-- Populated via JS -->
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small mb-1">3. เลือกสถานที่ (Physical Room)</label>
                                            <select id="drag_room" class="form-select form-select-sm">
                                                <!-- Populated via JS -->
                                            </select>
                                        </div>
                                        <div class="badge bg-primary text-wrap w-100 drag-item" draggable="true" id="draggable_block" ondragstart="drag(event)">
                                            <i class="fas fa-grip-lines mb-1 d-block"></i>
                                            <span id="drag_preview_text">ลากฉัน (Drag me)</span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Area: The Schedule Grid -->
                    <div class="col-md-9">
                        <div class="card shadow-sm border-0 mb-4 h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <ul class="nav nav-pills" id="multiViewTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active py-1 px-3 me-2" id="view-class-tab" data-bs-toggle="pill" data-bs-target="#view-class" type="button" role="tab"><i class="fas fa-users me-1"></i> ดูตามห้องเรียน</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link py-1 px-3 me-2" id="view-teacher-tab" data-bs-toggle="pill" data-bs-target="#view-teacher" type="button" role="tab"><i class="fas fa-chalkboard-teacher me-1"></i> ดูตามครูผู้สอน</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link py-1 px-3" id="view-room-tab" data-bs-toggle="pill" data-bs-target="#view-room" type="button" role="tab"><i class="fas fa-building me-1"></i> ดูตามสถานที่</button>
                                    </li>
                                </ul>
                                
                                <button class="btn btn-sm btn-outline-primary" onclick="loadScheduleData()">
                                    <i class="fas fa-sync-alt"></i> รีเฟรช
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="tab-content" id="multiViewContent">
                                    <!-- View By Class Tab -->
                                    <div class="tab-pane fade show active" id="view-class" role="tabpanel">
                                        <h5 class="text-center mb-3" id="tableTitleClass">เลือกห้องเรียนเพื่อดู/จัดตารางสอน</h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered schedule-table">
                                                <thead>
                                                    <tr>
                                                        <th class="day-header">วัน / เวลา</th>
                                                        <th class="time-header">08:00 - 09:00</th>
                                                        <th class="time-header">09:00 - 10:00</th>
                                                        <th class="time-header">10:00 - 11:00</th>
                                                        <th class="time-header">11:00 - 12:00</th>
                                                        <th class="time-header bg-light">12:00 - 13:00</th>
                                                        <th class="time-header">13:00 - 14:00</th>
                                                        <th class="time-header">14:00 - 15:00</th>
                                                        <th class="time-header">15:00 - 16:00</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="scheduleGridClass">
                                                    <!-- Generated via JS -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- View By Teacher Tab -->
                                    <div class="tab-pane fade" id="view-teacher" role="tabpanel">
                                        <div class="mb-3 d-flex align-items-center">
                                            <label class="me-2 fw-bold">เลือกครู:</label>
                                            <select id="view_teacher_select" class="form-select w-auto" onchange="renderTeacherSchedule()">
                                                <option value="">-- เลือกครูผู้สอน --</option>
                                            </select>
                                        </div>
                                        <h5 class="text-center mb-3" id="tableTitleTeacher">ตารางสอนรายบุคคล</h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered schedule-table">
                                                <thead>
                                                    <tr>
                                                        <th class="day-header">วัน / เวลา</th>
                                                        <th class="time-header">08:00 - 09:00</th>
                                                        <th class="time-header">09:00 - 10:00</th>
                                                        <th class="time-header">10:00 - 11:00</th>
                                                        <th class="time-header">11:00 - 12:00</th>
                                                        <th class="time-header bg-light">12:00 - 13:00</th>
                                                        <th class="time-header">13:00 - 14:00</th>
                                                        <th class="time-header">14:00 - 15:00</th>
                                                        <th class="time-header">15:00 - 16:00</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="scheduleGridTeacher">
                                                    <!-- Generated via JS -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- View By Room Tab -->
                                    <div class="tab-pane fade" id="view-room" role="tabpanel">
                                        <div class="mb-3 d-flex align-items-center">
                                            <label class="me-2 fw-bold">เลือกสถานที่:</label>
                                            <select id="view_room_select" class="form-select w-auto" onchange="renderRoomSchedule()">
                                                <option value="">-- เลือกสถานที่เรียน --</option>
                                            </select>
                                        </div>
                                        <h5 class="text-center mb-3" id="tableTitleRoom">ตารางการใช้สถานที่</h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered schedule-table">
                                                <thead>
                                                    <tr>
                                                        <th class="day-header">วัน / เวลา</th>
                                                        <th class="time-header">08:00 - 09:00</th>
                                                        <th class="time-header">09:00 - 10:00</th>
                                                        <th class="time-header">10:00 - 11:00</th>
                                                        <th class="time-header">11:00 - 12:00</th>
                                                        <th class="time-header bg-light">12:00 - 13:00</th>
                                                        <th class="time-header">13:00 - 14:00</th>
                                                        <th class="time-header">14:00 - 15:00</th>
                                                        <th class="time-header">15:00 - 16:00</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="scheduleGridRoom">
                                                    <!-- Generated via JS -->
                                                </tbody>
                                            </table>
                                        </div>
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

    <!-- Modals for Alerts (Conflict) -->
    <div class="modal fade" id="conflictModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center p-4">
                    <i class="fas fa-times-circle text-danger fa-4x mb-3"></i>
                    <h4 class="fw-bold text-dark">พบข้อผิดพลาด (Conflict!)</h4>
                    <p class="text-muted" id="conflictMessage">ตารางชนกัน</p>
                    <button type="button" class="btn btn-secondary px-4 mt-2" data-bs-dismiss="modal">ตกลง</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <script src="js/script.js"></script>

    <script>
        let allSchedules = [];
        let mapData = {
            subjects: [], teachers: [], physical_rooms: [], classrooms: []
        };
        const days = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์'];
        const times = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00'];
        
        $(document).ready(function() {
            // Setup Empty Grid
            generateEmptyGrid('scheduleGridClass', true);
            generateEmptyGrid('scheduleGridTeacher', false);
            generateEmptyGrid('scheduleGridRoom', false);

            // Fetch Options
            $.ajax({
                url: 'api/schedules.php',
                type: 'GET',
                data: { action: 'get_options' },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        mapData = res.data;
                        populateSelects();
                    }
                }
            });

            // Update Drag Preview
            $('#dragConfigForm select').on('change', updateDragPreview);

            // Trigger Load Data
            $('#select_classroom, #filter_semester, #filter_year').on('change', function() {
                if ($('#select_classroom').val()) {
                    let className = $('#select_classroom option:selected').text();
                    $('#tableTitleClass').text('ตารางเรียนห้อง: ' + className);
                }
                loadScheduleData();
            });
        });

        function populateSelects() {
            // Populate Subjects
            const subjectOpts = mapData.subjects.map(s => `<option value="${s.id}">${s.subject_code} ${s.subject_name}</option>`);
            $('#drag_subject').html('<option value="">-- เลือกวิชา --</option>' + subjectOpts.join(''));

            // Populate Teachers
            const teacherOpts = mapData.teachers.map(t => `<option value="${t.id}">${t.full_name}</option>`);
            $('#drag_teacher, #view_teacher_select').html('<option value="">-- เลือกครูผู้สอน --</option>' + teacherOpts.join(''));

            // Populate Physical Rooms
            const roomOpts = mapData.physical_rooms.map(r => `<option value="${r.id}">${r.room_name} (${r.room_type})</option>`);
            $('#drag_room, #view_room_select').html('<option value="">-- เลือกสถานที่ --</option>' + roomOpts.join(''));

            // Populate Classrooms
            const classOpts = mapData.classrooms.map(c => `<option value="${c.id}">${c.class_level}/${c.room_name}</option>`);
            $('#select_classroom').html('<option value="">-- เลือกห้องเรียน --</option>' + classOpts.join(''));
        }

        function updateDragPreview() {
            const subj = $('#drag_subject option:selected').text();
            $('#drag_preview_text').text(subj ? subj.substring(0, 20) + '...' : 'ลากฉัน');
        }

        // Drag and Drop Handlers
        function allowDrop(ev) {
            ev.preventDefault();
            ev.currentTarget.classList.add('drag-over');
        }

        function dragLeave(ev) {
            ev.currentTarget.classList.remove('drag-over');
        }

        function drag(ev) {
            const subj = $('#drag_subject').val();
            const teacher = $('#drag_teacher').val();
            const room = $('#drag_room').val();
            
            if(!subj || !teacher || !room) {
                showAlert('warning', 'กรุณาเลือก วิชา, ครู, และสถานที่ ให้ครบก่อนลาก');
                ev.preventDefault();
                return;
            }

            const payload = {
                subject_id: subj,
                teacher_id: teacher,
                physical_room_id: room
            };
            
            ev.dataTransfer.setData("application/json", JSON.stringify(payload));
        }

        function drop(ev, dayIndex, timeIndex) {
            ev.preventDefault();
            ev.currentTarget.classList.remove('drag-over');
            
            const classroom_id = $('#select_classroom').val();
            if(!classroom_id) {
                showAlert('warning', 'กรุณาเลือกห้องเรียนด้านซ้ายมือก่อนจัดตาราง');
                return;
            }

            const dataStr = ev.dataTransfer.getData("application/json");
            if (!dataStr) return;
            
            const data = JSON.parse(dataStr);
            const academic_year = $('#filter_year').val();
            const semester = $('#filter_semester').val();
            
            const start_time = times[timeIndex] + ':00';
            const end_time = times[timeIndex+1] ? times[timeIndex+1] + ':00' : '16:00:00'; // Assuming 1 hr periods
            const day_of_week = dayIndex + 1; // 1=Mon

            // Optional: Show loading
            
            $.ajax({
                url: 'api/schedules.php',
                type: 'POST',
                data: {
                    action: 'create',
                    classroom_id: classroom_id,
                    subject_id: data.subject_id,
                    teacher_id: data.teacher_id,
                    physical_room_id: data.physical_room_id,
                    day_of_week: day_of_week,
                    start_time: start_time,
                    end_time: end_time,
                    academic_year: academic_year,
                    semester: semester
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        showAlert('success', res.message);
                        loadScheduleData();
                    } else if (res.conflict) {
                        $('#conflictMessage').text(res.message);
                        $('#conflictModal').modal('show');
                    } else {
                        showAlert('danger', res.message);
                    }
                }
            });
        }

        function loadScheduleData() {
            const term = $('#filter_semester').val() + '/' + $('#filter_year').val();
            
            $.ajax({
                url: 'api/schedules.php',
                type: 'GET',
                data: { action: 'read', term: term },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        allSchedules = res.data;
                        renderClassSchedule();
                        renderTeacherSchedule();
                        renderRoomSchedule();
                    }
                }
            });
        }

        function generateEmptyGrid(tbodyId, isDroppable) {
            let html = '';
            for (let i = 0; i < 5; i++) {
                html += `<tr><td class="day-header align-middle">${days[i]}</td>`;
                for (let j = 0; j < 8; j++) {
                    if (j === 4) { // 12:00 - 13:00 Break time
                        html += `<td class="bg-light text-center text-muted align-middle" style="height: 80px;">พักกลางวัน</td>`;
                        continue;
                    }
                    
                    if (isDroppable) {
                        html += `<td class="droppable-area" id="cell_${i}_${j}" ondrop="drop(event, ${i}, ${j})" ondragover="allowDrop(event)" ondragleave="dragLeave(event)"></td>`;
                    } else {
                        html += `<td class="droppable-area" id="cell_${tbodyId}_${i}_${j}"></td>`;
                    }
                }
                html += `</tr>`;
            }
            $('#' + tbodyId).html(html);
        }

        function renderClassSchedule() {
            const class_id = $('#select_classroom').val();
            
            // Clear droppable cells
            for(let i=0; i<5; i++) {
                for(let j=0; j<8; j++) {
                    if (j !== 4) $(`#cell_${i}_${j}`).empty();
                }
            }

            if(!class_id) return;

            const schedules = allSchedules.filter(s => s.classroom_id == class_id);
            
            schedules.forEach(schedule => {
                const dayIdx = schedule.day_of_week - 1;
                // Simple parsing for 1-hour slots matching our grid
                const timeIdx = times.indexOf(schedule.start_time.substring(0, 5));
                
                if (dayIdx >= 0 && dayIdx <= 4 && timeIdx >= 0) {
                    const cellId = `#cell_${dayIdx}_${timeIdx}`;
                    const cardHtml = `
                        <div class="schedule-card shadow-sm">
                            <span class="btn-delete" title="ลบข้อมูล" onclick="deleteSchedule(${schedule.id})"><i class="fas fa-times-circle"></i></span>
                            <div class="fw-bold">${schedule.subject_code}</div>
                            <div class="text-truncate" title="${schedule.subject_name}">${schedule.subject_name}</div>
                            <div class="small"><i class="fas fa-user-tie text-muted"></i> ${schedule.teacher_name.split(' ')[0]}</div>
                            <div class="small text-muted"><i class="fas fa-map-marker-alt"></i> ${schedule.physical_room_name}</div>
                        </div>
                    `;
                    $(cellId).append(cardHtml);
                    // Removing droppable overlay from this cell to prevent overlap visually
                    // $(cellId).attr('ondrop', 'return false;');
                }
            });
        }

        function renderTeacherSchedule() {
            const teacher_id = $('#view_teacher_select').val();
            const tbodyId = 'scheduleGridTeacher';
            
            for(let i=0; i<5; i++) {
                for(let j=0; j<8; j++) {
                    if (j !== 4) $(`#cell_${tbodyId}_${i}_${j}`).empty();
                }
            }

            if(!teacher_id) {
                $('#tableTitleTeacher').text('ตารางสอนรายบุคคล');
                return;
            }

            $('#tableTitleTeacher').text('ตารางสอน: ' + $('#view_teacher_select option:selected').text());

            const schedules = allSchedules.filter(s => s.teacher_id == teacher_id);
            
            schedules.forEach(schedule => {
                const dayIdx = schedule.day_of_week - 1;
                const timeIdx = times.indexOf(schedule.start_time.substring(0, 5));
                
                if (dayIdx >= 0 && dayIdx <= 4 && timeIdx >= 0) {
                    const cellId = `#cell_${tbodyId}_${dayIdx}_${timeIdx}`;
                    const cardHtml = `
                        <div class="schedule-card shadow-sm bg-warning bg-opacity-10 border-warning">
                            <div class="fw-bold">${schedule.subject_code}</div>
                            <div class="small"><i class="fas fa-users text-muted"></i> ม.${schedule.class_level}/${schedule.class_room_name}</div>
                            <div class="small text-muted"><i class="fas fa-map-marker-alt"></i> ${schedule.physical_room_name}</div>
                        </div>
                    `;
                    $(cellId).append(cardHtml);
                }
            });
        }

        function renderRoomSchedule() {
            const room_id = $('#view_room_select').val();
            const tbodyId = 'scheduleGridRoom';
            
            for(let i=0; i<5; i++) {
                for(let j=0; j<8; j++) {
                    if (j !== 4) $(`#cell_${tbodyId}_${i}_${j}`).empty();
                }
            }

            if(!room_id) {
                $('#tableTitleRoom').text('ตารางการใช้สถานที่');
                return;
            }

            $('#tableTitleRoom').text('ตารางสถานที่: ' + $('#view_room_select option:selected').text());

            const schedules = allSchedules.filter(s => s.physical_room_id == room_id);
            
            schedules.forEach(schedule => {
                const dayIdx = schedule.day_of_week - 1;
                const timeIdx = times.indexOf(schedule.start_time.substring(0, 5));
                
                if (dayIdx >= 0 && dayIdx <= 4 && timeIdx >= 0) {
                    const cellId = `#cell_${tbodyId}_${dayIdx}_${timeIdx}`;
                    const cardHtml = `
                        <div class="schedule-card shadow-sm bg-success bg-opacity-10 border-success">
                            <div class="fw-bold">${schedule.subject_code}</div>
                            <div class="small"><i class="fas fa-users text-muted"></i> ม.${schedule.class_level}/${schedule.class_room_name}</div>
                            <div class="small text-muted"><i class="fas fa-user-tie"></i> ${schedule.teacher_name.split(' ')[0]}</div>
                        </div>
                    `;
                    $(cellId).append(cardHtml);
                }
            });
        }

        function deleteSchedule(id) {
            if(confirm("ต้องการลบวิชานี้ออกจากตารางใช่หรือไม่?")) {
                $.ajax({
                    url: 'api/schedules.php',
                    type: 'POST',
                    data: { action: 'delete', id: id },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            showAlert('success', res.message);
                            loadScheduleData();
                        } else {
                            showAlert('danger', res.message);
                        }
                    }
                });
            }
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
        }
    </script>
</body>
</html>
