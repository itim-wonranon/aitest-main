<?php 
require_once 'includes/session_check.php'; 
check_role(['admin', 'teacher']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูลระดับชั้นเรียน - สาธิตวิทยา</title>
    
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
                    <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-layer-group me-2"></i>ระบบจัดการข้อมูลระดับชั้นเรียน</h2>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                    <div>
                        <button class="btn btn-theme shadow-sm" data-bs-toggle="modal" data-bs-target="#classModal" onclick="openAddModal()">
                            <i class="fas fa-plus fa-sm text-dark-50 me-1"></i> เพิ่มระดับชั้นเรียน
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
                            <table class="table table-hover table-bordered" id="classesTable" width="100%" cellspacing="0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>ระดับชั้นเรียน</th>
                                        <th>รายละเอียด (กลุ่มการศึกษา)</th>
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

    <!-- Class Modal (Add / Edit) -->
    <div class="modal fade" id="classModal" tabindex="-1" aria-labelledby="classModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form id="classForm">
                    <div class="modal-header bg-light border-bottom-0 pb-2">
                        <h5 class="modal-title fw-bold" id="classModalLabel">เพิ่มข้อมูลระดับชั้นเรียน</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="class_id" name="id">
                        <input type="hidden" id="action_type" name="action" value="create">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">ระดับชั้นเรียน <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="level_name" name="level_name" required placeholder="เช่น ม.1, ป.6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">รายละเอียด (เพิ่มเติม)</label>
                            <input type="text" class="form-control" id="level_description" name="level_description" placeholder="เช่น มัธยมศึกษาตอนต้นปีที่ 1">
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
                    <p class="text-muted mb-4">คุณต้องการลบข้อมูล <br><strong id="delete_class_name" class="text-danger"></strong> ใช่หรือไม่?</p>
                    <input type="hidden" id="delete_class_id">
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
            table = $('#classesTable').DataTable({
                ajax: {
                    url: 'api/classes.php?action=read',
                    dataSrc: 'data'
                },
                columns: [
                    { data: 'id' },
                    { data: 'level_name', render: function(data) { return '<strong>' + data + '</strong>'; } },
                    { data: 'level_description' },
                    {
                        data: null,
                        className: 'text-center',
                        render: function (data, type, row) {
                            if(userRole === 'admin') {
                                return `
                                    <button class="btn btn-sm btn-outline-primary me-1" onclick='openEditModal(${JSON.stringify(row)})'><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" onclick='deleteClass(${row.id}, "${row.level_name}")'><i class="fas fa-trash"></i></button>
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

            $('#classForm').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();

                $.ajax({
                    url: 'api/classes.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            showAlert('success', response.message);
                            $('#classModal').modal('hide');
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
            $('#classForm')[0].reset();
            $('#action_type').val('create');
            $('#class_id').val('');
            $('#classModalLabel').text('เพิ่มข้อมูลระดับชั้นเรียน');
        }

        function openEditModal(cls) {
            $('#class_id').val(cls.id);
            $('#action_type').val('update');
            $('#level_name').val(cls.level_name);
            $('#level_description').val(cls.level_description);

            $('#classModalLabel').text('แก้ไขข้อมูลระดับชั้นเรียน');
            $('#classModal').modal('show');
        }

        function deleteClass(id, name) {
            $('#delete_class_id').val(id);
            $('#delete_class_name').text(name);
            $('#deleteModal').modal('show');
        }

        function confirmDelete() {
            const id = $('#delete_class_id').val();
            $.ajax({
                url: 'api/classes.php',
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
