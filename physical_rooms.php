<?php 
require_once 'includes/session_check.php'; 
check_role(['admin', 'teacher']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสถานที่เรียน (Physical Rooms) - สาธิตวิทยา</title>
    
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
                    <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-building me-2"></i>ระบบจัดการสถานที่เรียน</h2>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                    <div>
                        <button class="btn btn-theme shadow-sm" data-bs-toggle="modal" data-bs-target="#roomModal" onclick="openAddModal()">
                            <i class="fas fa-plus fa-sm text-dark-50 me-1"></i> เพิ่มสถานที่เรียน
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <div id="alertBox"></div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="roomsTable" width="100%" cellspacing="0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>ชื่อห้อง/สถานที่</th>
                                        <th>ประเภทห้อง</th>
                                        <th>ความจุนักเรียน</th>
                                        <th>สถานะ</th>
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

    <!-- Room Modal (Add / Edit) -->
    <div class="modal fade" id="roomModal" tabindex="-1" aria-labelledby="roomModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form id="roomForm">
                    <div class="modal-header bg-light border-bottom-0 pb-2">
                        <h5 class="modal-title fw-bold" id="roomModalLabel">เพิ่มข้อมูลสถานที่เรียน</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="room_id" name="id">
                        <input type="hidden" id="action_type" name="action" value="create">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">ชื่อห้อง/สถานที่ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="room_name" name="room_name" required placeholder="เช่น 501, Lab วิทย์ 1, สนามฟุตบอล">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">ประเภทห้อง <span class="text-danger">*</span></label>
                            <select class="form-select" id="room_type" name="room_type" required>
                                <option value="">-- เลือกประเภท --</option>
                                <option value="ห้องเรียนรวม">ห้องเรียนรวม</option>
                                <option value="ห้องแล็บวิทย์">ห้องแล็บวิทย์</option>
                                <option value="ห้องคอมพิวเตอร์">ห้องคอมพิวเตอร์</option>
                                <option value="ดนตรี/ศิลปะ">ดนตรี/ศิลปะ</option>
                                <option value="สนามกีฬา">สนามกีฬา</option>
                                <option value="ห้องสมุด">ห้องสมุด</option>
                                <option value="อื่นๆ">อื่นๆ</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">ความจุนักเรียน (คน)</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" value="40" min="1">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">สถานะ</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active">ใช้งาน (Active)</option>
                                <option value="inactive">ปิดปรับปรุง (Inactive)</option>
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

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center p-4">
                    <i class="fas fa-exclamation-triangle text-danger fa-3x mb-3"></i>
                    <h5 class="fw-bold text-dark">ยืนยันการลบข้อมูล</h5>
                    <p class="text-muted mb-4">คุณต้องการลบสถานที่เรียน <br><strong id="delete_room_name_text" class="text-danger"></strong> ใช่หรือไม่?</p>
                    <input type="hidden" id="delete_room_id">
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
        const userRole = '<?php echo $_SESSION["role"]; ?>';

        $(document).ready(function() {
            table = $('#roomsTable').DataTable({
                ajax: {
                    url: 'api/physical_rooms.php?action=read',
                    dataSrc: 'data'
                },
                columns: [
                    { data: 'id' },
                    { data: 'room_name', render: function(data) { return '<strong>' + data + '</strong>'; } },
                    { data: 'room_type' },
                    { data: 'capacity', className: 'text-center' },
                    { 
                        data: 'status', 
                        className: 'text-center',
                        render: function(data) {
                            return data === 'active' 
                                ? '<span class="badge bg-success">ใช้งาน</span>' 
                                : '<span class="badge bg-danger">ปิดปรับปรุง</span>';
                        }
                    },
                    {
                        data: null,
                        className: 'text-center',
                        render: function (data, type, row) {
                            if(userRole === 'admin') {
                                return `
                                    <button class="btn btn-sm btn-outline-primary me-1" onclick='openEditModal(${JSON.stringify(row)})'><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" onclick='deleteRoom(${row.id}, "${row.room_name}")'><i class="fas fa-trash"></i></button>
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

            $('#roomForm').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();

                $.ajax({
                    url: 'api/physical_rooms.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            showAlert('success', response.message);
                            $('#roomModal').modal('hide');
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
            $('#roomForm')[0].reset();
            $('#action_type').val('create');
            $('#room_id').val('');
            $('#roomModalLabel').text('เพิ่มข้อมูลสถานที่เรียน');
        }

        function openEditModal(row) {
            $('#room_id').val(row.id);
            $('#action_type').val('update');
            $('#room_name').val(row.room_name);
            $('#room_type').val(row.room_type);
            $('#capacity').val(row.capacity);
            $('#status').val(row.status);

            $('#roomModalLabel').text('แก้ไขข้อมูลสถานที่เรียน');
            $('#roomModal').modal('show');
        }

        function deleteRoom(id, name) {
            $('#delete_room_id').val(id);
            $('#delete_room_name_text').text(name);
            $('#deleteModal').modal('show');
        }

        function confirmDelete() {
            const id = $('#delete_room_id').val();
            $.ajax({
                url: 'api/physical_rooms.php',
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
