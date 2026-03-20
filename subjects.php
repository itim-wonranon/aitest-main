<?php require_once 'includes/session_check.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูลรายวิชา - สาธิตวิทยา</title>
    
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

            <!-- Main Workspace: Subjects Master Data -->
            <main class="container-fluid px-4 pb-4">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-book me-2"></i>ระบบจัดการข้อมูลรายวิชา</h2>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                    <div>
                        <button class="btn btn-theme shadow-sm" data-bs-toggle="modal" data-bs-target="#subjectModal" onclick="openAddModal()">
                            <i class="fas fa-plus fa-sm text-dark-50 me-1"></i> เพิ่มข้อมูลวิชา
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
                            <table class="table table-hover table-bordered" id="subjectsTable" width="100%" cellspacing="0">
                                <thead class="table-light">
                                    <tr>
                                        <th>รหัสวิชา</th>
                                        <th>ชื่อรายวิชา</th>
                                        <th>ประเภทวิชา</th>
                                        <th>หน่วยกิต</th>
                                        <th>กลุ่มสาระการเรียนรู้</th>
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
+
            <!-- Footer Placeholder -->
            <div id="footer-placeholder"></div>
            
        </div>
    </div>

    <!-- Subject Modal (Add / Edit) -->
    <div class="modal fade" id="subjectModal" tabindex="-1" aria-labelledby="subjectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form id="subjectForm">
                    <div class="modal-header bg-light border-bottom-0 pb-2">
                        <h5 class="modal-title fw-bold" id="subjectModalLabel">เพิ่มข้อมูลรายวิชา</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="subject_id" name="id">
                        <input type="hidden" id="action_type" name="action" value="create">
                        
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label class="form-label fw-bold">รหัสวิชา <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="subject_code" name="subject_code" required placeholder="เช่น ค31101">
                            </div>
                            <div class="col-md-7 mb-3">
                                <label class="form-label fw-bold">ชื่อรายวิชา <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="subject_name" name="subject_name" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">ประเภทวิชา <span class="text-danger">*</span></label>
                                <select class="form-select" id="subject_type" name="subject_type" required>
                                    <option value="">-- เลือกประเภท --</option>
                                    <option value="รายวิชาพื้นฐาน">รายวิชาพื้นฐาน</option>
                                    <option value="รายวิชาเพิ่มเติม">รายวิชาเพิ่มเติม</option>
                                    <option value="กิจกรรมพัฒนาผู้เรียน">กิจกรรมพัฒนาผู้เรียน</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">หน่วยกิต <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="credit" name="credit" required step="0.5" min="0.5" max="3.0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">กลุ่มสาระการเรียนรู้ <span class="text-danger">*</span></label>
                            <select class="form-select" id="department" name="department" required>
                                <option value="">-- เลือกกลุ่มสาระ --</option>
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
                    <p class="text-muted mb-4">คุณต้องการลบข้อมูลวิชา <br><strong id="delete_subject_name" class="text-danger"></strong> ใช่หรือไม่?</p>
                    <input type="hidden" id="delete_subject_id">
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

    <!-- Page Specific Script for Subject CRUD -->
    <script>
        let table;
        const userRole = '<?php echo $_SESSION["role"]; ?>';

        $(document).ready(function() {
            table = $('#subjectsTable').DataTable({
                ajax: {
                    url: 'api/subjects.php?action=read',
                    dataSrc: 'data'
                },
                columns: [
                    { data: 'subject_code', render: function(data) { return '<strong>' + data + '</strong>'; } },
                    { data: 'subject_name' },
                    { 
                        data: 'subject_type',
                        render: function(data) {
                            if(data === 'รายวิชาพื้นฐาน') return `<span class="badge bg-primary">${data}</span>`;
                            if(data === 'รายวิชาเพิ่มเติม') return `<span class="badge bg-info text-dark">${data}</span>`;
                            return `<span class="badge bg-secondary">${data}</span>`;
                        }
                    },
                    { data: 'credit', className: 'text-center' },
                    { data: 'department' },
                    {
                        data: null,
                        className: 'text-center',
                        render: function (data, type, row) {
                            if(userRole === 'admin') {
                                return `
                                    <button class="btn btn-sm btn-outline-primary me-1" onclick='openEditModal(${JSON.stringify(row)})'><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" onclick='deleteSubject(${row.id}, "${row.subject_code} ${row.subject_name}")'><i class="fas fa-trash"></i></button>
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

            $('#subjectForm').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize(); // No file upload required for subjects

                $.ajax({
                    url: 'api/subjects.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            showAlert('success', response.message);
                            $('#subjectModal').modal('hide');
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
            $('#subjectForm')[0].reset();
            $('#action_type').val('create');
            $('#subject_id').val('');
            $('#subjectModalLabel').text('เพิ่มข้อมูลรายวิชา');
        }

        function openEditModal(subject) {
            $('#subject_id').val(subject.id);
            $('#action_type').val('update');
            $('#subject_code').val(subject.subject_code);
            $('#subject_name').val(subject.subject_name);
            $('#subject_type').val(subject.subject_type);
            $('#credit').val(subject.credit);
            $('#department').val(subject.department);

            $('#subjectModalLabel').text('แก้ไขข้อมูลรายวิชา');
            $('#subjectModal').modal('show');
        }

        function deleteSubject(id, name) {
            $('#delete_subject_id').val(id);
            $('#delete_subject_name').text(name);
            $('#deleteModal').modal('show');
        }

        function confirmDelete() {
            const id = $('#delete_subject_id').val();
            $.ajax({
                url: 'api/subjects.php',
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
