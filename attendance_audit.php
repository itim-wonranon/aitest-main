<?php 
require_once 'includes/session_check.php'; 
// Only Admin can see audit logs for attendance
check_role(['admin']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติแก้ไขเวลาเรียน - สาธิตวิทยา</title>
    
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
                    <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-history me-2"></i>ประวัติการแก้ไขและ Audit Trail (เวลาเรียน)</h2>
                </div>

                <div class="alert alert-warning border-0 shadow-sm mb-4">
                    <i class="fas fa-shield-alt me-2"></i> <strong>สำหรับ Admin:</strong> หน้านี้ใช้ตรวจสอบประวัติการแก้ไขเวลาเรียนย้อนหลัง รวมถึงตรวจสอบ Device IP Address
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="auditTable" width="100%" cellspacing="0">
                                <thead class="table-light text-center align-middle">
                                    <tr>
                                        <th>วันที่แก้ไข (Timestamp)</th>
                                        <th>คาบวันที่สอน</th>
                                        <th>วิชา - ห้อง</th>
                                        <th>นักเรียน</th>
                                        <th>ข้อมูลเก่า <i class="fas fa-arrow-right"></i> ใหม่</th>
                                        <th>ผู้แก้ / เหตุผล</th>
                                        <th>IP Address (ตอนคีย์)</th>
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

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white border-bottom-0 pb-2">
                    <h5 class="modal-title fw-bold">รายละเอียดการแก้เวลาเรียน</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div id="modalContent"></div>
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
        $(document).ready(function() {
            $('#auditTable').DataTable({
                ajax: {
                    url: 'api/attendance_logs.php',
                    dataSrc: 'data'
                },
                columns: [
                    { 
                        data: 'created_at',
                        render: function(data) {
                            return `<div class="small fw-bold">${new Date(data).toLocaleString('th-TH')}</div>`;
                        }
                    },
                    { 
                        data: 'session_date',
                        className: 'text-center',
                        render: function(data) {
                            return `<span class="badge bg-secondary">${data}</span>`;
                        }
                    },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            return `<div class="fw-bold">${row.subject_code}</div>
                                    <div class="small text-muted">ม.${row.class_level}/${row.room}</div>`;
                        }
                    },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            return `<div class="fw-bold text-primary">${row.student_code}</div>
                                  <div class="small">${row.student_name}</div>`;
                        }
                    },
                    { 
                        data: null,
                        className: 'text-center',
                        render: function(data, type, row) {
                            return `
                                <div class="badge bg-danger shadow-sm mb-1">${formatStatus(row.old_status)}</div>
                                <div><i class="fas fa-arrow-down text-muted small"></i></div>
                                <div class="badge bg-success shadow-sm">${formatStatus(row.new_status)}</div>
                            `;
                        }
                    },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            return `
                                <div class="fw-bold"><i class="fas fa-user-edit text-muted"></i> ${row.teacher_admin_name}</div>
                                <div class="small text-muted mt-1 fst-italic">"${row.reason}"</div>
                            `;
                        }
                    },
                    { 
                        data: 'device_ip',
                        className: 'text-center align-middle',
                        render: function(data) {
                            return `<span class="badge bg-light text-dark border"><i class="fas fa-laptop"></i> ${data}</span>`;
                        }
                    }
                ],
                order: [[0, 'desc']], 
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
                }
            });
        });

        function formatStatus(status) {
            if(status === 'present') return '<i class="fas fa-check"></i> มา';
            if(status === 'late') return '<i class="fas fa-clock"></i> สาย';
            if(status === 'absent') return '<i class="fas fa-times"></i> ขาด';
            if(status === 'sick_leave') return '<i class="fas fa-bed"></i> ลาป่วย';
            if(status === 'business_leave') return '<i class="fas fa-briefcase"></i> ลากิจ';
            return status || 'ยังไม่เช็ก';
        }
    </script>
</body>
</html>
