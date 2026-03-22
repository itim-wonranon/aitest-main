<?php 
require_once 'includes/session_check.php'; 
// Only Admin
check_role(['admin']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกความปลอดภัย - ผู้ดูแลระบบ</title>
    
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
                    <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-shield-alt me-2"></i>ตรวจสอบความปลอดภัย (Security & Audit Logs)</h2>
                </div>

                <ul class="nav nav-tabs mb-4 border-bottom-2" id="logTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold text-dark" id="login-tab" data-bs-toggle="tab" data-bs-target="#login_logs" type="button" role="tab" aria-selected="true"><i class="fas fa-sign-in-alt me-1"></i> ประวัติการเข้าสู่ระบบ</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-dark" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity_logs" type="button" role="tab" aria-selected="false"><i class="fas fa-history me-1"></i> ประวัติความเคลื่อนไหวระบบ</button>
                    </li>
                </ul>

                <div class="tab-content" id="logTabsContent">
                    
                    <!-- Login Logs Tab -->
                    <div class="tab-pane fade show active" id="login_logs" role="tabpanel" aria-labelledby="login-tab">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle h6 font-monospace" id="loginTable" width="100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th>วัน-เวลา</th>
                                                <th>รหัสผู้ใช้</th>
                                                <th>ชื่อ-นามสกุล</th>
                                                <th>IP Address</th>
                                                <th>สถานะ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Dynamic Content -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Logs Tab -->
                    <div class="tab-pane fade" id="activity_logs" role="tabpanel" aria-labelledby="activity-tab">
                        <div class="card border-0 shadow-sm pb-4">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle h6" id="activityTable" width="100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th>วัน-เวลา</th>
                                                <th>ผู้ใช้งาน</th>
                                                <th>หมวดหมู่เป้าหมาย</th>
                                                <th>การกระทำ (Action)</th>
                                                <th>รายละเอียด</th>
                                                <th>IP Address</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Dynamic Content -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>

            <div id="footer-placeholder"></div>
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
            // Load Login History
            $('#loginTable').DataTable({
                ajax: {
                    url: 'api/admin_api.php?action=get_login_history',
                    dataSrc: 'data'
                },
                columns: [
                    { 
                        data: 'login_time',
                        render: function(data) {
                            return `<div class="small fw-bold text-muted">${new Date(data).toLocaleString('th-TH')}</div>`;
                        }
                    },
                    { data: 'username', className: 'text-primary fw-bold' },
                    { data: 'full_name' },
                    { data: 'ip_address' },
                    { 
                        data: 'status',
                        render: function(data) {
                            if(data === 'success') return '<span class="badge bg-success"><i class="fas fa-check"></i> สำเร็จ</span>';
                            return '<span class="badge bg-danger"><i class="fas fa-times"></i> ล้มเหลว</span>';
                        }
                    }
                ],
                order: [[0, 'desc']],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json' }
            });

            // Load Activity Logs
            $('#activityTable').DataTable({
                ajax: {
                    url: 'api/admin_api.php?action=get_activity_logs',
                    dataSrc: 'data'
                },
                columns: [
                    { 
                        data: 'created_at',
                        render: function(data) {
                            return `<div class="small fw-bold text-muted">${new Date(data).toLocaleString('th-TH')}</div>`;
                        }
                    },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            return `<div>${row.username}</div><div class="small text-muted">${row.full_name}</div>`;
                        }
                    },
                    { data: 'entity', className: 'fw-bold text-uppercase' },
                    { 
                        data: 'action',
                        render: function(data) {
                            let b = 'bg-secondary';
                            if(data === 'create') b = 'bg-success';
                            if(data === 'update') b = 'bg-warning text-dark';
                            if(data === 'delete') b = 'bg-danger';
                            if(data === 'login') b = 'bg-info text-dark';
                            if(data === 'export') b = 'bg-primary';
                            return `<span class="badge ${b}">${data}</span>`;
                        }
                    },
                    { data: 'details', className: 'small' },
                    { data: 'ip_address', className: 'font-monospace' }
                ],
                order: [[0, 'desc']],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json' }
            });
        });
    </script>
</body>
</html>
