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
    <title>จัดการผู้ใช้งานและสิทธิ์ - ผู้ดูแลระบบ</title>
    
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
                    <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-users-cog me-2"></i>ระบบจัดการผู้ใช้งานและสิทธิ์ (RBAC)</h2>
                </div>

                <div class="alert alert-info border-0 shadow-sm mb-4">
                    <i class="fas fa-lightbulb me-2"></i> <strong>โหมด Impersonation:</strong> คุณสามารถกดปุ่ม <i class="fas fa-mask"></i> เพื่อจำลองการล็อกอินเป็นผู้ใช้นั้นๆ (Login As) เพื่อตรวจสอบปัญหาหน้าจอเสมือนจริงโดยไม่ต้องขอรหัสผ่าน
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle text-center" id="usersTable" width="100%" cellspacing="0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>ชื่อผู้ใช้ (Username)</th>
                                        <th>ชื่อ-สกุล</th>
                                        <th>บทบาท (Role)</th>
                                        <th>สถานะ (Status)</th>
                                        <th style="width: 250px;">การจัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dynamic Content -->
                                </tbody>
                            </table>
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
        let usersTable;

        $(document).ready(function() {
            loadUsers();
        });

        function loadUsers() {
            if (usersTable) {
                usersTable.destroy();
            }

            usersTable = $('#usersTable').DataTable({
                ajax: {
                    url: 'api/admin_api.php?action=get_users',
                    dataSrc: 'data'
                },
                columns: [
                    { data: 'id' },
                    { 
                        data: 'username',
                        className: 'fw-bold text-primary text-start'
                    },
                    { 
                        data: null,
                        className: 'text-start',
                        render: function(data) {
                            return data.first_name + ' ' + data.last_name;
                        }
                    },
                    { 
                        data: 'role',
                        render: function(data) {
                            let badge = 'bg-secondary';
                            if(data === 'admin') badge = 'bg-danger';
                            if(data === 'teacher') badge = 'bg-success';
                            if(data === 'student') badge = 'bg-info';
                            return `<span class="badge ${badge} text-uppercase">${data}</span>`;
                        }
                    },
                    { 
                        data: 'status',
                        render: function(data) {
                            if(data === 'suspended') return '<span class="badge bg-secondary"><i class="fas fa-ban"></i> ระงับการใช้งาน</span>';
                            return '<span class="badge bg-primary"><i class="fas fa-check-circle"></i> ใช้งานปกติ</span>';
                        }
                    },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            let buttons = '';
                            
                            // Suspend/Active Toggle
                            if(row.role !== 'admin' && row.id != <?php echo $_SESSION['user_id']; ?>) {
                                if(row.status === 'active') {
                                    buttons += `<button class="btn btn-sm btn-outline-danger me-1" onclick="updateStatus(${row.id}, 'suspended')" title="ระงับบัญชี"><i class="fas fa-ban"></i></button>`;
                                } else {
                                    buttons += `<button class="btn btn-sm btn-outline-success me-1" onclick="updateStatus(${row.id}, 'active')" title="เปิดใช้งาน"><i class="fas fa-check"></i></button>`;
                                }
                                
                                // Impersonate Button
                                if(row.status === 'active') {
                                    buttons += `<button class="btn btn-sm btn-warning fw-bold text-dark shadow-sm" onclick="impersonate(${row.id}, '${row.first_name}')">
                                        <i class="fas fa-mask"></i> Login As
                                    </button>`;
                                }
                            }
                            
                            return buttons || '-';
                        }
                    }
                ],
                order: [[3, 'asc'], [0, 'asc']],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
                }
            });
        }

        function updateStatus(id, status) {
            let msg = status === 'suspended' ? 'คุณแน่ใจหรือไม่ที่จะ "ระงับ" การใช้งานบัญชีนี้?' : 'คุณแน่ใจหรือไม่ที่จะ "เปิด" การใช้งานบัญชีนี้?';
            if(confirm(msg)) {
                $.post('api/admin_api.php', { action: 'update_user_status', id: id, status: status }, function(res) {
                    if (res.status === 'success') {
                        usersTable.ajax.reload(null, false);
                    } else {
                        alert('Error: ' + res.message);
                    }
                }, 'json');
            }
        }

        function impersonate(id, name) {
            if(confirm(`คุณต้องการเข้าสู่ระบบ (Impersonate) ในนามของคุณ ${name} ใช่หรือไม่?\n\n* โค้ดทั้งหมดจะทำงานเสมือนคุณคือเขาจริงๆ\n* หากต้องการกลับสู่ร่างเดิม ให้กดปุ่ม "Revert" ด้านบนสุด`)) {
                $.post('api/admin_api.php', { action: 'impersonate', id: id }, function(res) {
                    if(res.status === 'success') {
                        // Reload entire page to apply session changes globally
                        window.location.href = 'index.php';
                    } else {
                        alert('ไม่สามารถ Impersonate ได้: ' + res.message);
                    }
                }, 'json');
            }
        }
    </script>
</body>
</html>
