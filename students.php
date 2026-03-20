<?php 
require_once 'includes/session_check.php'; 
check_role(['admin', 'teacher']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูลนักเรียน - สาธิตวิทยา</title>
    
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

            <!-- Main Workspace: Students Master Data -->
            <main class="container-fluid px-4 pb-4">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-user-graduate me-2"></i>ระบบจัดการข้อมูลนักเรียน</h2>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                    <div>
                        <button class="btn btn-theme shadow-sm" data-bs-toggle="modal" data-bs-target="#studentModal" onclick="openAddModal()">
                            <i class="fas fa-plus fa-sm text-dark-50 me-1"></i> เพิ่มข้อมูลนักเรียน
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
                            <table class="table table-hover table-bordered" id="studentsTable" width="100%" cellspacing="0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ภาพประจำตัว</th>
                                        <th>รหัสประจำตัว</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>ระดับชั้น</th>
                                        <th>ห้อง</th>
                                        <th>เบอร์ผู้ปกครอง</th>
                                        <th class="text-center">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data populated by DataTables via AJAX -->
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

    <!-- Student Modal (Add / Edit) -->
    <div class="modal fade" id="studentModal" tabindex="-1" aria-labelledby="studentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form id="studentForm" enctype="multipart/form-data">
                    <div class="modal-header bg-light border-bottom-0 pb-2">
                        <h5 class="modal-title fw-bold" id="studentModalLabel">เพิ่มข้อมูลนักเรียน</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="student_id" name="id">
                        <input type="hidden" id="action_type" name="action" value="create">
                        
                        <div class="text-center mb-3 d-none" id="preview_area">
                            <img id="image_preview" src="" class="rounded-circle border border-2 shadow-sm" width="100" height="100" style="object-fit: cover;">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">รูปภาพประจำตัว (อัปโหลดสูงสุด 2MB)</label>
                            <input class="form-control" type="file" id="profile_image" name="profile_image" accept="image/png, image/jpeg, image/webp" onchange="previewImage(event)">
                        </div>

                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label class="form-label fw-bold">รหัสประจำตัว <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="student_code" name="student_code" required>
                            </div>
                            <div class="col-md-7 mb-3">
                                <label class="form-label fw-bold">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                        </div>

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
                                <label class="form-label fw-bold">ห้อง <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="room" name="room" required min="1">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">เบอร์โทรศัพท์ผู้ปกครอง</label>
                            <input type="text" class="form-control" id="parent_phone" name="parent_phone">
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
                    <p class="text-muted mb-4">คุณต้องการลบข้อมูลนักเรียน <br><strong id="delete_student_name" class="text-danger"></strong> ใช่หรือไม่?</p>
                    <input type="hidden" id="delete_student_id">
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

    <!-- Page Specific Script for Student CRUD -->
    <script>
        let table;
        const userRole = '<?php echo $_SESSION["role"]; ?>';

        $(document).ready(function() {
            table = $('#studentsTable').DataTable({
                ajax: {
                    url: 'api/students.php?action=read',
                    dataSrc: 'data'
                },
                columns: [
                    { 
                        data: 'profile_image',
                        render: function(data) {
                            if(data) {
                                return `<img src="images/profiles/${data}" class="rounded-circle shadow-sm" width="45" height="45" style="object-fit: cover;">`;
                            } else {
                                return `<img src="https://ui-avatars.com/api/?name=St&background=E9ECEF&color=6C757D&rounded=true" width="45">`;
                            }
                        }
                    },
                    { data: 'student_code' },
                    { data: 'full_name' },
                    { data: 'class_level' },
                    { data: 'room' },
                    { data: 'parent_phone' },
                    {
                        data: null,
                        className: 'text-center',
                        render: function (data, type, row) {
                            if(userRole === 'admin') {
                                return `
                                    <button class="btn btn-sm btn-outline-primary me-1" onclick='openEditModal(${JSON.stringify(row)})'><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" onclick='deleteStudent(${row.id}, "${row.full_name}")'><i class="fas fa-trash"></i></button>
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

            $('#studentForm').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                $.ajax({
                    url: 'api/students.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    processData: false, 
                    contentType: false, 
                    success: function(response) {
                        if (response.status === 'success') {
                            showAlert('success', response.message);
                            $('#studentModal').modal('hide');
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

        function previewImage(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#image_preview').attr('src', e.target.result);
                    $('#preview_area').removeClass('d-none');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function openAddModal() {
            $('#studentForm')[0].reset();
            $('#action_type').val('create');
            $('#student_id').val('');
            $('#preview_area').addClass('d-none');
            $('#profile_image').val('');
            $('#studentModalLabel').text('เพิ่มข้อมูลนักเรียน');
        }

        function openEditModal(student) {
            $('#student_id').val(student.id);
            $('#action_type').val('update');
            $('#student_code').val(student.student_code);
            $('#full_name').val(student.full_name);
            $('#class_level').val(student.class_level);
            $('#room').val(student.room);
            $('#parent_phone').val(student.parent_phone);
            
            $('#profile_image').val('');
            if (student.profile_image) {
                $('#image_preview').attr('src', 'images/profiles/' + student.profile_image);
                $('#preview_area').removeClass('d-none');
            } else {
                $('#preview_area').addClass('d-none');
            }

            $('#studentModalLabel').text('แก้ไขข้อมูลนักเรียน');
            $('#studentModal').modal('show');
        }

        function deleteStudent(id, name) {
            $('#delete_student_id').val(id);
            $('#delete_student_name').text(name);
            $('#deleteModal').modal('show');
        }

        function confirmDelete() {
            const id = $('#delete_student_id').val();
            $.ajax({
                url: 'api/students.php',
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
            setTimeout(() => {
                $('.alert').alert('close');
            }, 4000);
        }
    </script>
</body>
</html>