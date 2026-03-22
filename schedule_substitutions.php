<?php 
require_once 'includes/session_check.php'; 
check_role(['admin']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการครูสอนแทน - สาธิตวิทยา</title>
    
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="wrapper">
        <div id="sidebar-placeholder"></div>

        <div id="content">
            <div id="header-placeholder"></div>

            <main class="container-fluid px-4 pb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-people-arrows me-2"></i>จัดการครูสอนแทน (Substitute Management)</h2>
                    <div>
                        <button class="btn btn-theme shadow-sm" data-bs-toggle="modal" data-bs-target="#substituteModal" onclick="openAddModal()">
                            <i class="fas fa-plus fa-sm text-dark-50 me-1"></i> เพิ่มการสอนแทน
                        </button>
                    </div>
                </div>

                <div id="alertBox"></div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="substitutionsTable" width="100%" cellspacing="0">
                                <thead class="table-light">
                                    <tr>
                                        <th>วันที่สอนแทน</th>
                                        <th>คาบเรียน / ห้อง</th>
                                        <th>วิชา</th>
                                        <th>ครูประจำวิชา (เดิม)</th>
                                        <th>ครูสอนแทน (ใหม่)</th>
                                        <th>เหตุผล</th>
                                        <th class="text-center">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </main>

            <div id="footer-placeholder"></div>
        </div>
    </div>

    <!-- Substitute Modal Add -->
    <div class="modal fade" id="substituteModal" tabindex="-1" aria-labelledby="substituteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <form id="substituteForm">
                    <div class="modal-header bg-light border-bottom-0 pb-2">
                        <h5 class="modal-title fw-bold" id="substituteModalLabel">บันทึกการจัดครูสอนแทน</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">เลือกครูประจำวิชา (ที่ลา/ติดธุระ) <span class="text-danger">*</span></label>
                                <select class="form-select" id="orig_teacher" required onchange="loadTeacherSchedules()">
                                    <option value="">-- เลือกครู --</option>
                                    <!-- Populated via JS -->
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">วันที่สอนแทน <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="absence_date" name="absence_date" required onchange="loadTeacherSchedules()">
                                <small class="text-muted">เลือกวันที่เพื่อดูตารางสอนของครูในวันนั้น</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">เลือกคาบเรียนที่ต้องการให้สอนแทน <span class="text-danger">*</span></label>
                            <select class="form-select" id="schedule_id" name="schedule_id" required disabled>
                                <option value="">-- กรุณาเลือกครูและวันทีก่อน --</option>
                            </select>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">เลือกครูสอนแทน <span class="text-danger">*</span></label>
                            <select class="form-select" id="substitute_teacher_id" name="substitute_teacher_id" required>
                                <option value="">-- เลือกครูสอนแทน --</option>
                                <!-- Populated via JS -->
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">เหตุผลการสอนแทน</label>
                            <input type="text" class="form-control" name="reason" placeholder="เช่น ลาป่วย, ติดราชการ">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-theme">บันทึกข้อมูล</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center p-4">
                    <i class="fas fa-exclamation-triangle text-danger fa-3x mb-3"></i>
                    <h5 class="fw-bold text-dark">ยืนยันการยกเลิก</h5>
                    <p class="text-muted mb-4">คุณต้องการลบข้อมูลการสอนแทนนี้ ใช่หรือไม่?</p>
                    <input type="hidden" id="delete_id">
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="button" class="btn btn-danger px-4" onclick="confirmDelete()">ลบข้อมูล</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script src="js/script.js"></script>

    <script>
        let table;
        let teachers = [];
        let schedules = [];

        $(document).ready(function() {
            table = $('#substitutionsTable').DataTable({
                ajax: {
                    url: 'api/substitutions.php?action=read',
                    dataSrc: 'data'
                },
                columns: [
                    { data: 'absence_date', render: function(data) {
                        const d = new Date(data);
                        return d.toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' });
                    }},
                    { data: null, render: function(data, type, row) { 
                        return `เวลา ${row.start_time.substring(0,5)} - ${row.end_time.substring(0,5)} <br><small class="text-muted">ม.${row.class_level}/${row.class_room_name}</small>`; 
                    }},
                    { data: 'subject_name' },
                    { data: 'original_teacher_name', render: function(data) { return `<span class="text-danger"><i class="fas fa-user-minus"></i> ${data}</span>`; } },
                    { data: 'substitute_teacher_name', render: function(data) { return `<span class="text-success"><i class="fas fa-user-plus"></i> ${data}</span>`; } },
                    { data: 'reason' },
                    {
                        data: null,
                        className: 'text-center',
                        render: function (data, type, row) {
                            return `<button class="btn btn-sm btn-outline-danger" onclick='deleteSubstitution(${row.id})'><i class="fas fa-trash"></i></button>`;
                        }
                    }
                ],
                order: [[0, 'desc']],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
                }
            });

            // Fetch Data for Modals
            $.ajax({
                url: 'api/schedules.php',
                type: 'GET',
                data: { action: 'get_options' },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        teachers = res.data.teachers;
                        
                        const teacherOpts = teachers.map(t => `<option value="${t.id}">${t.full_name}</option>`);
                        $('#orig_teacher, #substitute_teacher_id').html('<option value="">-- เลือกครู --</option>' + teacherOpts.join(''));
                    }
                }
            });
            
            $.ajax({
                url: 'api/schedules.php',
                type: 'GET',
                data: { action: 'read', term: '' }, // load all
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        schedules = res.data;
                    }
                }
            });

            $('#substituteForm').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();

                $.ajax({
                    url: 'api/substitutions.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            showAlert('success', response.message);
                            $('#substituteModal').modal('hide');
                            table.ajax.reload(null, false); 
                        } else {
                            showAlert('danger', response.message);
                        }
                    },
                    error: function() {
                        showAlert('danger', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                    }
                });
            });
        });

        function loadTeacherSchedules() {
            const t_id = $('#orig_teacher').val();
            const dateStr = $('#absence_date').val();
            
            if(!t_id || !dateStr) {
                $('#schedule_id').html('<option value="">-- กรุณาเลือกครูและวันทีก่อน --</option>').prop('disabled', true);
                return;
            }

            const d = new Date(dateStr);
            let dayOfWeek = d.getDay(); // 0(Sun) - 6(Sat)
            if(dayOfWeek === 0) dayOfWeek = 7;
            
            const teacherSchedules = schedules.filter(s => s.teacher_id == t_id && s.day_of_week == dayOfWeek);
            
            if(teacherSchedules.length === 0) {
                $('#schedule_id').html('<option value="">-- ไม่พบตารางสอนในวันนี้ --</option>').prop('disabled', true);
            } else {
                const options = teacherSchedules.map(s => `<option value="${s.id}">${s.start_time.substring(0,5)} - ${s.end_time.substring(0,5)} | วิชา ${s.subject_name} (ม.${s.class_level}/${s.class_room_name})</option>`);
                $('#schedule_id').html('<option value="">-- เลือกคาบเรียนที่ต้องการให้สอนแทน --</option>' + options.join('')).prop('disabled', false);
            }
        }

        function openAddModal() {
            $('#substituteForm')[0].reset();
            $('#schedule_id').html('<option value="">-- กรุณาเลือกครูและวันทีก่อน --</option>').prop('disabled', true);
        }

        function deleteSubstitution(id) {
            $('#delete_id').val(id);
            $('#deleteModal').modal('show');
        }

        function confirmDelete() {
            const id = $('#delete_id').val();
            $.ajax({
                url: 'api/substitutions.php',
                type: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    $('#deleteModal').modal('hide');
                    if (response.status === 'success') {
                        showAlert('success', response.message);
                        table.ajax.reload(null, false);
                    } else {
                        showAlert('danger', response.message);
                    }
                },
                error: function() {
                    $('#deleteModal').modal('hide');
                    showAlert('danger', 'เกิดข้อผิดพลาดในการลบข้อมูล');
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
        }
    </script>
</body>
</html>
