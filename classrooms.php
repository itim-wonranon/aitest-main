<?php

require_once 'includes/session_check.php';

check_role(['admin', 'teacher']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูลห้องเรียน - สาธิตวิทยา</title>
    
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
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

            <!-- Main Workspace -->
            <main class="container-fluid px-4 pb-4">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-door-open me-2"></i>ระบบจัดการข้อมูลห้องเรียน</h2>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                    <div>
                        <button class="btn btn-theme shadow-sm" data-bs-toggle="modal" data-bs-target="#classroomModal" onclick="openAddModal()">
                            <i class="fas fa-plus fa-sm text-dark-50 me-1"></i> เพิ่มห้องเรียน
                        </button>
                    </div>
                    <?php
endif; ?>
                </div>

                <!-- Alert Message Area -->
                <div id="alertBox"></div>

                <!-- Data Table Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="classroomsTable" width="100%" cellspacing="0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>ระดับชั้น</th>
                                        <th>ห้อง</th>
                                        <th>สายการเรียน</th>
                                        <th>ครูประจำชั้น</th>
                                        <th>ความจุนักเรียน</th>
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

            <!-- Footer Placeholder -->
            <div id="footer-placeholder"></div>
            
        </div>
    </div>

    <!-- Classroom Modal (Add / Edit) -->
    <div class="modal fade" id="classroomModal" tabindex="-1" aria-labelledby="classroomModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form id="classroomForm">
                    <div class="modal-header bg-light border-bottom-0 pb-2">
                        <h5 class="modal-title fw-bold" id="classroomModalLabel">เพิ่มข้อมูลห้องเรียน</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="classroom_id" name="id">
                        <input type="hidden" id="action_type" name="action" value="create">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">ระดับชั้น <span class="text-danger">*</span></label>
                                <select class="form-select" id="class_level" name="class_level" required>
                                    <option value="">-- เลือกระดับชั้น --</option>
                                    <option value="ม.1">ม.1</option>
                                    <option value="ม.2">ม.2</option>
                                    <option value="ม.3">ม.3</option>
                                    <option value="ม.4">ม.4</option>
                                    <option value="ม.5">ม.5</option>
                                    <option value="ม.6">ม.6</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">ระดับ/ป้ายห้อง <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="room_name" name="room_name" required placeholder="เช่น 1, 2, A">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">สายการเรียน</label>
                            <input type="text" class="form-control" id="program" name="program" placeholder="เช่น วิทย์-คณิต, ศิลป์-ภาษา, ห้องเรียนปกติ">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">ครูประจำชั้น</label>
                            <input type="text" class="form-control" id="homeroom_teacher" name="homeroom_teacher" placeholder="ระบุชื่อครูประจำชั้น">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">ความจุนักเรียน <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="capacity" name="capacity" value="40" min="1" required>
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center p-4">
                    <i class="fas fa-exclamation-triangle text-danger fa-3x mb-3"></i>
                    <h5 class="fw-bold text-dark">ยืนยันการลบข้อมูล</h5>
                    <p class="text-muted mb-4">คุณต้องการลบห้องเรียน <br><strong id="delete_room_name" class="text-danger"></strong> ใช่หรือไม่?</p>
                    <input type="hidden" id="delete_classroom_id">
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="button" class="btn btn-danger px-4" onclick="confirmDelete()">ลบข้อมูล</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Popper.js and Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Custom Layout Loader Script -->
    <script src="js/script.js"></script>

    <!-- Page Specific Script -->
    <script>
        let table;
        const userRole = '<?php echo $_SESSION["role"]; ?>';

        $(document).ready(function() {
            table = $('#classroomsTable').DataTable({
                ajax: {
                    url: 'api/classrooms.php?action=read',
                    dataSrc: 'data'
                },
                columns: [
                    { data: 'id' },
                    { data: 'class_level', render: function(data) { return '<span class="badge bg-info text-dark">' + data + '</span>'; } },
                    { data: 'room_name', render: function(data) { return '<strong>' + data + '</strong>'; } },
                    { data: 'program', render: function(data) { return data ? data : '-'; } },
                    { data: 'homeroom_teacher', render: function(data) { return data ? data : '-'; } },
                    { data: 'capacity', className: 'text-center' },
                    {
                        data: null,
                        className: 'text-center',
                        render: function (data, type, row) {
                            if(userRole === 'admin') {
                                return `
                                    <button class="btn btn-sm btn-outline-primary me-1" onclick='openEditModal(${JSON.stringify(row)})'><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" onclick='deleteClassroom(${row.id}, "${row.class_level}/${row.room_name}")'><i class="fas fa-trash"></i></button>
                                `;
                            } else {
                                return `<span class="badge bg-secondary">ไม่มีสิทธิ์จัดการ</span>`;
                            }
                        }
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
                }
            });

            $('#classroomForm').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();

                $.ajax({
                    url: 'api/classrooms.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            showAlert('success', response.message);
                            $('#classroomModal').modal('hide');
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

        function openAddModal() {
            $('#classroomForm')[0].reset();
            $('#action_type').val('create');
            $('#classroom_id').val('');
            $('#classroomModalLabel').text('เพิ่มข้อมูลห้องเรียน');
        }

        function openEditModal(cr) {
            $('#classroom_id').val(cr.id);
            $('#action_type').val('update');
            $('#class_level').val(cr.class_level);
            $('#room_name').val(cr.room_name);
            $('#program').val(cr.program);
            $('#homeroom_teacher').val(cr.homeroom_teacher);
            $('#capacity').val(cr.capacity);

            $('#classroomModalLabel').text('แก้ไขข้อมูลห้องเรียน');
            $('#classroomModal').modal('show');
        }

        function deleteClassroom(id, name) {
            $('#delete_classroom_id').val(id);
            $('#delete_room_name').text(name);
            $('#deleteModal').modal('show');
        }

        function confirmDelete() {
            const id = $('#delete_classroom_id').val();
            $.ajax({
                url: 'api/classrooms.php',
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
            // Auto close alert after 4 seconds
            setTimeout(() => {
                $('.alert').alert('close');
            }, 4000);
        }
    </script>
</body>
</html>
