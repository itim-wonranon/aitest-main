<?php 
require_once 'includes/session_check.php'; 
check_role(['admin']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการแก้ไขตารางเรียน - สาธิตวิทยา</title>
    
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
                    <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-history me-2"></i>ประวัติการแก้ไขตารางเรียน (Schedule Logs)</h2>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="logsTable" width="100%" cellspacing="0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="15%">วัน-เวลาบันทึก</th>
                                        <th width="15%">การกระทำ</th>
                                        <th width="50%">รายละเอียด</th>
                                        <th width="20%">ผู้ดำเนินการ</th>
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

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Popper.js and Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- Custom Layout Loader -->
    <script src="js/script.js"></script>

    <script>
        $(document).ready(function() {
            $('#logsTable').DataTable({
                ajax: {
                    url: 'api/schedule_logs.php?action=read',
                    dataSrc: 'data'
                },
                columns: [
                    { 
                        data: 'created_at',
                        render: function(data) {
                            const d = new Date(data);
                            return d.toLocaleString('th-TH');
                        }
                    },
                    { 
                        data: 'action_type',
                        render: function(data) {
                            if(data === 'CREATE') return '<span class="badge bg-success">เพิ่มข้อมูล</span>';
                            if(data === 'UPDATE') return '<span class="badge bg-warning text-dark">แก้ไขข้อมูล</span>';
                            if(data === 'DELETE') return '<span class="badge bg-danger">ลบข้อมูล</span>';
                            if(data === 'SUBSTITUTION') return '<span class="badge bg-info text-dark">เพิ่มการสอนแทน</span>';
                            if(data === 'DELETE_SUB') return '<span class="badge bg-secondary">ยกเลิกดารสอนแทน</span>';
                            return '<span class="badge bg-dark">' + data + '</span>';
                        }
                    },
                    { data: 'details' },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            return `<div class="d-flex align-items-center">
                                        <i class="fas fa-user-circle text-muted fs-4 me-2"></i>
                                        <div>
                                            <div class="fw-bold fs-7">${row.first_name} ${row.last_name}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">@${row.username}</div>
                                        </div>
                                    </div>`;
                        }
                    }
                ],
                order: [[0, 'desc']], // Sort by date descending
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
                }
            });
        });
    </script>
</body>
</html>
