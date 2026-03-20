<?php 
require_once 'includes/session_check.php'; 
check_role(['admin', 'teacher']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูลบุคลากรครู - สาธิตวิทยา</title>
    
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

            <!-- Main Workspace: Teachers Master Data -->
            <main class="container-fluid px-4 pb-4">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-chalkboard-teacher me-2"></i>ระบบจัดการข้อมูลบุคลากรครู</h2>
                    <?php if($_SESSION['role'] == 'admin'): ?>
                    <div>
                        <button class="btn btn-theme shadow-sm" data-bs-toggle="modal" data-bs-target="#teacherModal" onclick="openAddModal()">
                            <i class="fas fa-plus fa-sm text-dark-50 me-1"></i> เพิ่มข้อมูลครู
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Alert Message Area -->
                <div id="alertBox"></div>

                <!-- Data Table Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="teachersTable" width="100%" cellspacing="0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ภาพประจำตัว</th>
                                        <th>รหัสประจำตัว</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>เบอร์โทรศัพท์</th>
                                        <th>Line ID</th>
                                        <th>หมวดวิชา</th>
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

    <!-- Teacher Modal (Add / Edit) -->
    <div class="modal fade" id="teacherModal" tabindex="-1" aria-labelledby="teacherModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form id="teacherForm" enctype="multipart/form-data">
                    <div class="modal-header bg-light border-bottom-0 pb-2">
                        <h5 class="modal-title fw-bold" id="teacherModalLabel">เพิ่มข้อมูลครู</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="teacher_id" name="id">
                        <input type="hidden" id="action_type" name="action" value="create">
                        
                        <div class="text-center mb-3 d-none" id="preview_area">
                            <img id="image_preview" src="" class="rounded-circle border border-2 shadow-sm" width="100" height="100" style="object-fit: cover;">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">รูปภาพประจำตัว (อัปโหลดสูงสุด 2MB)</label>
                            <input class="form-control" type="file" id="profile_image" name="profile_image" accept="image/png, image/jpeg, image/webp" onchange="previewImage(event)">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">รหัสประจำตัวครู <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="teacher_code" name="teacher_code" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">เบอร์โทรศัพท์</label>
                                <input type="text" class="form-control" id="phone" name="phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Line ID</label>
                                <input type="text" class="form-control" id="line_id" name="line_id">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">หมวดวิชาที่สังกัด</label>
                            <select class="form-select" id="department" name="department">
                                <option value="">-- เลือกหมวดวิชา --</option>
                                <option value="คณิตศาสตร์">คณิตศาสตร์</option>
                                <option value="วิทยาศาสตร์">วิทยาศาสตร์</option>
                                <option value="ภาษาไทย">ภาษาไทย</option>
                                <option value="ภาษาต่างประเทศ">ภาษาต่างประเทศ</option>
                                <option value="สังคมศึกษา">สังคมศึกษา</option>
                                <option value="ศิลปะ">ศิลปะ</option>
                                <option value="สุขศึกษาและพลศึกษา">สุขศึกษาและพลศึกษา</option>
                                <option value="การงานอาชีพ">การงานอาชีพ</option>
                            </select>
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
                    <p class="text-muted mb-4">คุณต้องการลบข้อมูลครู <br><strong id="delete_teacher_name" class="text-danger"></strong> ใช่หรือไม่?</p>
                    <input type="hidden" id="delete_teacher_id">
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

    <!-- Page Specific Script for Teacher CRUD -->
    <script>
        let table;
        // The role is embedded for front-end control, but backend verification is secure
        const userRole = '<?php echo $_SESSION["role"]; ?>';

        $(document).ready(function() {
            // Initialize DataTable
            table = $('#teachersTable').DataTable({
                ajax: {
                    url: 'api/teachers.php?action=read',
                    dataSrc: 'data'
                },
                columns: [
                    { 
                        data: 'profile_image',
                        render: function(data) {
                            if(data) {
                                return `<img src="images/profiles/${data}" class="rounded-circle shadow-sm" width="45" height="45" style="object-fit: cover;">`;
                            } else {
                                return `<img src="https://ui-avatars.com/api/?name=Th&background=E9ECEF&color=6C757D&rounded=true" width="45">`;
                            }
                        }
                    },
                    { data: 'teacher_code' },
                    { data: 'full_name' },
                    { data: 'phone' },
                    { data: 'line_id', render: function(data) { return data ? '<span class="text-success"><i class="fab fa-line me-1"></i> '+data+'</span>' : '-'; } },
                    { data: 'department' },
                    {
                        data: null,
                        className: 'text-center',
                        render: function (data, type, row) {
                            if(userRole === 'admin') {
                                return `
                                    <button class="btn btn-sm btn-outline-primary me-1" onclick='openEditModal(${JSON.stringify(row)})'><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" onclick='deleteTeacher(${row.id}, "${row.full_name}")'><i class="fas fa-trash"></i></button>
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

            // Handle Form Submission (Add/Edit)
            $('#teacherForm').on('submit', function(e) {
                e.preventDefault();
                // To upload files, we must use FormData
                const formData = new FormData(this);

                $.ajax({
                    url: 'api/teachers.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    processData: false, // critical for file uploads
                    contentType: false, // critical for file uploads
                    success: function(response) {
                        if (response.status === 'success') {
                            showAlert('success', response.message);
                            $('#teacherModal').modal('hide');
                            table.ajax.reload(null, false); // Reload without resetting pagination
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
            $('#teacherForm')[0].reset();
            $('#action_type').val('create');
            $('#teacher_id').val('');
            $('#preview_area').addClass('d-none');
            $('#profile_image').val('');
            $('#teacherModalLabel').text('เพิ่มข้อมูลครู');
        }

        function openEditModal(teacher) {
            $('#teacher_id').val(teacher.id);
            $('#action_type').val('update');
            $('#teacher_code').val(teacher.teacher_code);
            $('#full_name').val(teacher.full_name);
            $('#phone').val(teacher.phone);
            $('#line_id').val(teacher.line_id);
            $('#department').val(teacher.department);
            
            // Handle Profile Image view logic
            $('#profile_image').val('');
            if (teacher.profile_image) {
                $('#image_preview').attr('src', 'images/profiles/' + teacher.profile_image);
                $('#preview_area').removeClass('d-none');
            } else {
                $('#preview_area').addClass('d-none');
            }

            $('#teacherModalLabel').text('แก้ไขข้อมูลครู');
            $('#teacherModal').modal('show');
        }

        function deleteTeacher(id, name) {
            $('#delete_teacher_id').val(id);
            $('#delete_teacher_name').text(name);
            $('#deleteModal').modal('show');
        }

        function confirmDelete() {
            const id = $('#delete_teacher_id').val();
            $.ajax({
                url: 'api/teachers.php',
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
