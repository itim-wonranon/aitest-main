<?php 
require_once 'includes/session_check.php'; 
// Admin and Teacher can access to approve
check_role(['admin', 'teacher']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการใบลา - สาธิตวิทยา</title>
    
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
                    <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-file-medical me-2"></i>จัดการใบลา (Leave Requests)</h2>
                    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createLeaveModal">
                        <i class="fas fa-plus me-1"></i> เพิ่มข้อมูลใบลาลงระบบ
                    </button>
                </div>
                
                <div id="alertBox"></div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body bg-light rounded mb-3">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">สถานะใบลา</label>
                                <select id="filterStatus" class="form-select form-select-sm">
                                    <option value="">ทั้งหมด</option>
                                    <option value="pending" selected>รออนุมัติ (Pending)</option>
                                    <option value="approved">อนุมัติแล้ว (Approved)</option>
                                    <option value="rejected">ไม่อนุมัติ (Rejected)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="leavesTable" width="100%" cellspacing="0">
                                <thead class="table-light text-center align-middle">
                                    <tr>
                                        <th width="15%">วันที่ยื่นเอกสาร</th>
                                        <th width="20%">ข้อมูลนักเรียน</th>
                                        <th width="20%">รายละเอียดการลา</th>
                                        <th width="25%">เหตุผล</th>
                                        <th width="10%">สถานะ</th>
                                        <th width="10%">การจัดการ</th>
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

    <!-- Create Leave Modal -->
    <div class="modal fade" id="createLeaveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form id="leaveForm">
                    <div class="modal-header bg-primary text-white border-bottom-0 pb-2">
                        <h5 class="modal-title fw-bold">สร้างรายการใบลา</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">นักเรียน (ID ชั่วคราวสำหรับการทดสอบ) <span class="text-danger">*</span></label>
                            <input type="number" name="student_id" class="form-control" required placeholder="ใส่รหัสไอดีนักเรียนในตาราง students (เช่น 1)">
                            <small class="text-muted">ในระบบจริงจะเป็น Dropdown เลือกระดับชั้นและพิมพ์ค้นหาชื่อ หรือนร.กรอกเอง</small>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">ลาตั้งแต่วันที่ <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">ถึงวันที่ <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">ประเภทการลา <span class="text-danger">*</span></label>
                            <select name="leave_type" class="form-select" required>
                                <option value="sick_leave">ลาป่วย</option>
                                <option value="business_leave">ลากิจ</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">เหตุผลการลา / รายละเอียด <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveLeave">บันทึกข้อมูลและอนุมัติทันที</button>
                    </div>
                </form>
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

        $(document).ready(function() {
            table = $('#leavesTable').DataTable({
                ajax: {
                    url: 'api/leaves.php?action=read_requests',
                    dataSrc: 'data'
                },
                columns: [
                    { 
                        data: 'created_at',
                        render: function(data) {
                            return new Date(data).toLocaleString('th-TH');
                        }
                    },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            return `<div class="fw-bold text-primary">${row.student_code}</div>
                                    <div class="">${row.student_name}</div>
                                    <div class="small text-muted border-top mt-1 pt-1">ม.${row.class_level}/${row.room_name}</div>`;
                        }
                    },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            const badgeStr = row.leave_type === 'sick_leave' 
                                ? '<span class="badge bg-danger">ลาป่วย</span>' 
                                : '<span class="badge bg-warning text-dark">ลากิจ</span>';
                            return `
                                <div class="mb-1">${badgeStr}</div>
                                <div class="small">
                                    <i class="far fa-calendar-alt text-muted"></i> 
                                    ${row.start_date} สิ้นสุด ${row.end_date}
                                </div>
                            `;
                        }
                    },
                    { 
                        data: 'reason',
                        render: function(data) {
                            return `<div class="text-wrap" style="max-height: 80px; overflow-y: auto;">${data}</div>`;
                        }
                    },
                    { 
                        data: 'status',
                        className: 'text-center align-middle',
                        render: function(data) {
                            if(data === 'approved') return '<span class="badge bg-success"><i class="fas fa-check"></i> อนุมัติ</span>';
                            if(data === 'rejected') return '<span class="badge bg-danger"><i class="fas fa-times"></i> ไม่อนุมัติ</span>';
                            return '<span class="badge" style="background-color:#fd7e14;"><i class="fas fa-clock"></i> รอตรวจสอบ</span>';
                        }
                    },
                    { 
                        data: null,
                        className: 'text-center align-middle',
                        render: function(data, type, row) {
                            if(row.status === 'pending') {
                                return `
                                    <div class="d-flex flex-column gap-1">
                                        <button class="btn btn-sm btn-outline-success" onclick="updateStatus(${row.id}, 'approved')"> อนุมัติ</button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="updateStatus(${row.id}, 'rejected')"> ไม่อนุมัติ</button>
                                    </div>
                                `;
                            } else {
                                return `<button class="btn btn-sm btn-outline-secondary" onclick="updateStatus(${row.id}, 'pending')"><i class="fas fa-undo"></i> รีเซ็ต</button>`;
                            }
                        }
                    }
                ],
                order: [[0, 'desc']], 
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
                }
            });

            $('#filterStatus').on('change', function() {
                var search = $(this).val();
                if(search === 'pending') {
                    table.search('รอตรวจสอบ').draw();
                } else if(search === 'approved') {
                    table.search('อนุมัติ').draw();
                } else if(search === 'rejected') {
                    table.search('ไม่อนุมัติ').draw();
                } else {
                    table.search('').draw();
                }
            });

            // Initial filter
            table.on('init.dt', function() {
                table.search('รอตรวจสอบ').draw();
            });

            $('#leaveForm').on('submit', function(e) {
                e.preventDefault();
                saveLeave();
            });
        });

        function saveLeave() {
            const btn = $('#btnSaveLeave');
            const origText = btn.text();
            btn.text('กำลังบันทึก...').prop('disabled', true);

            $.ajax({
                url: 'api/leaves.php',
                type: 'POST',
                data: $('#leaveForm').serialize() + '&action=create_request',
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        showAlert('success', res.message);
                        $('#leaveForm')[0].reset();
                        $('#createLeaveModal').modal('hide');
                        table.ajax.reload();
                    } else {
                        showAlert('danger', res.message);
                    }
                    btn.text(origText).prop('disabled', false);
                },
                error: function() {
                    showAlert('danger', 'เกิดข้อผิดพลาด');
                    btn.text(origText).prop('disabled', false);
                }
            });
        }

        function updateStatus(id, status) {
            let msg = status === 'approved' ? 'อนุมัติใบลา?' : (status === 'rejected' ? 'ปฏิเสธใบลา (ไม่อนุมัติ)?' : 'รีเซ็ตสถานะกลับเป็น รอตรวจสอบ?');
            if(confirm('คุณต้องการ ' + msg)) {
                $.ajax({
                    url: 'api/leaves.php',
                    type: 'POST',
                    data: { action: 'update_status', id: id, status: status },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            table.ajax.reload(null, false);
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
