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
    <title>ตั้งค่าระบบและประกาศ - ผู้ดูแลระบบ</title>
    
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
                    <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-cogs me-2"></i>ตั้งค่าระบบและประกาศข่าวสาร (System Config & CMS)</h2>
                </div>

                <ul class="nav nav-tabs mb-4 border-bottom-2" id="adminTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold text-dark" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab" aria-selected="true"><i class="fas fa-sliders-h me-1"></i> ตั้งค่าพื้นฐานระบบ</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-dark" id="announcements-tab" data-bs-toggle="tab" data-bs-target="#announcements" type="button" role="tab" aria-selected="false"><i class="fas fa-bullhorn me-1"></i> จัดการประกาศข่าวสาร</button>
                    </li>
                </ul>

                <div class="tab-content" id="adminTabsContent">
                    
                    <!-- System Settings Tab -->
                    <div class="tab-pane fade show active" id="settings" role="tabpanel" aria-labelledby="settings-tab">
                        <div class="card border-top-0 border-end-0 border-bottom-0 border-primary border-5 shadow-sm">
                            <div class="card-body p-4">
                                <form id="settingsForm">
                                    <div class="row g-4">
                                        
                                        <div class="col-md-6">
                                            <div class="alert alert-warning h-100 mb-0 border-0">
                                                <h5 class="fw-bold"><i class="fas fa-exclamation-triangle"></i> Maintenance Mode</h5>
                                                <p class="small mb-2">เมื่อเปิดโหมดนี้ ผู้ใช้ทุกคน (ยกเว้น Admin) จะถูกบังคับเตะออกจากระบบทันที เหมาะสำหรับเวลาอัปเดตฐานข้อมูลหรือปรับปรุงเซิร์ฟเวอร์</p>
                                                <div class="form-check form-switch fs-5">
                                                    <input class="form-check-input" type="checkbox" id="maintenance_mode" value="on">
                                                    <label class="form-check-label fw-bold" for="maintenance_mode">เปิดโหมดจำศีล (Maintenance)</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">ชื่อโรงเรียน</label>
                                            <input type="text" class="form-control" id="school_name" required>
                                            <div class="form-text">จะปรากฏที่หัวใบรายงาน ปพ. และ ปพ.5</div>

                                            <label class="form-label fw-bold mt-3">ชื่อผู้อำนวยการ</label>
                                            <input type="text" class="form-control" id="director_name" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">ปีการศึกษาปัจจุบัน</label>
                                            <input type="number" class="form-control" id="current_academic_year" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">ภาคเรียนปัจจุบัน</label>
                                            <select class="form-select" id="current_semester" required>
                                                <option value="1">ภาคเรียนที่ 1</option>
                                                <option value="2">ภาคเรียนที่ 2</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="text-end mt-4 pt-3 border-top">
                                        <button type="submit" class="btn btn-primary fw-bold px-4"><i class="fas fa-save me-2"></i> บันทึกการตั้งค่า</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Announcements Tab -->
                    <div class="tab-pane fade" id="announcements" role="tabpanel" aria-labelledby="announcements-tab">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold text-primary">รายการประกาศทั้งหมด</h5>
                                <button class="btn btn-success btn-sm fw-bold shadow-sm" onclick="openAnnouncementModal()"><i class="fas fa-plus"></i> สร้างประกาศใหม่</button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="announcementsTable" width="100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th>วันที่ประกาศ</th>
                                                <th>หัวข้อประกาศ</th>
                                                <th>ผู้ประกาศ</th>
                                                <th>สถานะ</th>
                                                <th>จัดการ</th>
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

    <!-- Announcement Modal -->
    <div class="modal fade" id="announcementModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <form id="announcementForm">
                    <div class="modal-header bg-primary text-white border-bottom-0 pb-2">
                        <h5 class="modal-title fw-bold" id="modalTitle">สร้างประกาศใหม่</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 bg-light">
                        <input type="hidden" id="announce_id">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">หัวข้อประกาศ (Title) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="announce_title" required placeholder="เช่น แจ้งหยุดเรียนกรณีพิเศษ...">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">เนื้อหาประกาศ (Message) <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="announce_message" rows="5" required placeholder="พิมพ์เนื้อหาที่ต้องการสื่อสาร..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">สถานะการแสดงผล</label>
                            <select class="form-select" id="announce_status">
                                <option value="active">🟢 แสดงผล (เผยแพร่)</option>
                                <option value="inactive">⚪ ซ่อน (ฉบับร่าง)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-save"></i> บันทึกประกาศ</button>
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
        let annTable;

        $(document).ready(function() {
            loadSettings();
            loadAnnouncements();

            $('#settingsForm').on('submit', function(e) {
                e.preventDefault();
                saveSettings();
            });

            $('#announcementForm').on('submit', function(e) {
                e.preventDefault();
                saveAnnouncement();
            });
        });

        function loadSettings() {
            $.get('api/admin_api.php?action=get_settings', function(res) {
                if(res.status === 'success') {
                    let s = res.data;
                    $('#school_name').val(s.school_name?.setting_value || '');
                    $('#director_name').val(s.director_name?.setting_value || '');
                    $('#current_academic_year').val(s.current_academic_year?.setting_value || '');
                    $('#current_semester').val(s.current_semester?.setting_value || '');
                    $('#maintenance_mode').prop('checked', s.maintenance_mode?.setting_value === 'on');
                }
            }, 'json');
        }

        function saveSettings() {
            let settings = {
                school_name: $('#school_name').val(),
                director_name: $('#director_name').val(),
                current_academic_year: $('#current_academic_year').val(),
                current_semester: $('#current_semester').val(),
                maintenance_mode: $('#maintenance_mode').is(':checked') ? 'on' : 'off'
            };

            const btn = $('#settingsForm button[type=submit]');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> บันทึก...');

            $.post('api/admin_api.php', { action: 'save_settings', settings: settings }, function(res) {
                if(res.status === 'success') {
                    alert('บันทึกการตั้งค่าระบบเรียบร้อยแล้ว');
                } else {
                    alert('Error: ' + res.message);
                }
                btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> บันทึกการตั้งค่า');
            }, 'json').fail(function() {
                alert('Connection error');
                btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> บันทึกการตั้งค่า');
            });
        }

        function loadAnnouncements() {
            if (annTable) {
                annTable.destroy();
            }

            annTable = $('#announcementsTable').DataTable({
                ajax: {
                    url: 'api/admin_api.php?action=get_announcements',
                    dataSrc: 'data'
                },
                columns: [
                    { 
                        data: 'created_at',
                        render: function(data) {
                            return `<div class="small text-muted">${new Date(data).toLocaleString('th-TH')}</div>`;
                        }
                    },
                    { 
                        data: 'title',
                        className: 'fw-bold text-dark'
                    },
                    { data: 'author_name' },
                    { 
                        data: 'status',
                        render: function(data) {
                            if(data === 'active') return '<span class="badge bg-success">เผยแพร่แล้ว</span>';
                            return '<span class="badge bg-secondary">ฉบับร่าง</span>';
                        }
                    },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            // escape quotes for standard json
                            let rstr = JSON.stringify(row).replace(/'/g, "&#39;").replace(/"/g, "&quot;");
                            return `
                                <button class="btn btn-sm btn-outline-primary" onclick="editAnnouncement(this)" data-row='${rstr}'><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteAnnouncement(${row.id})"><i class="fas fa-trash"></i></button>
                            `;
                        }
                    }
                ],
                order: [[0, 'desc']],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
                }
            });
        }

        function openAnnouncementModal() {
            $('#announcementForm')[0].reset();
            $('#announce_id').val('');
            $('#modalTitle').text('สร้างประกาศใหม่');
            $('#announcementModal').modal('show');
        }

        function editAnnouncement(btn) {
            let rowString = $(btn).attr('data-row');
            let row = JSON.parse(rowString);
            
            $('#announce_id').val(row.id);
            $('#announce_title').val(row.title);
            $('#announce_message').val(row.message);
            $('#announce_status').val(row.status);
            
            $('#modalTitle').text('แก้ไขประกาศ');
            $('#announcementModal').modal('show');
        }

        function saveAnnouncement() {
            let data = {
                action: 'save_announcement',
                id: $('#announce_id').val(),
                title: $('#announce_title').val(),
                message: $('#announce_message').val(),
                status: $('#announce_status').val()
            };

            $.post('api/admin_api.php', data, function(res) {
                if(res.status === 'success') {
                    $('#announcementModal').modal('hide');
                    annTable.ajax.reload(null, false);
                } else {
                    alert('Error: ' + res.message);
                }
            }, 'json');
        }

        function deleteAnnouncement(id) {
            if(confirm('ยืนยันการลบประกาศนี้?')) {
                $.post('api/admin_api.php', { action: 'delete_announcement', id: id }, function(res) {
                    if(res.status === 'success') {
                        annTable.ajax.reload(null, false);
                    } else {
                        alert('Error: ' + res.message);
                    }
                }, 'json');
            }
        }
    </script>
</body>
</html>
