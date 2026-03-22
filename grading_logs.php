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
    <title>ประวัติการแก้ไขคะแนน - สาธิตวิทยา</title>
    
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
                    <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-history me-2"></i>ประวัติการแก้ไขคะแนน (Grade Correction Logs)</h2>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="logsTable" width="100%" cellspacing="0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="15%">วัน-เวลา</th>
                                        <th width="20%">ผู้ดำเนินการ</th>
                                        <th width="20%">นักเรียน & วิชา</th>
                                        <th width="25%">เหตุผลการแก้ไข</th>
                                        <th width="20%" class="text-center">ตรวจสอบข้อมูล</th>
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

    <!-- Data Modal -->
    <div class="modal fade" id="dataModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-bottom-0 pb-2">
                    <h5 class="modal-title fw-bold">ตรวจสอบข้อมูลการแก้ไขคะแนน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-danger fw-bold"><i class="fas fa-minus-circle me-1"></i>ข้อมูลเดิม (Old Data)</h6>
                            <div class="bg-light p-3 rounded" id="oldDataDisplay" style="font-family: monospace; white-space: pre-wrap; font-size: 0.85rem;"></div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success fw-bold"><i class="fas fa-plus-circle me-1"></i>ข้อมูลใหม่ (New Data)</h6>
                            <div class="bg-light p-3 rounded" id="newDataDisplay" style="font-family: monospace; white-space: pre-wrap; font-size: 0.85rem;"></div>
                        </div>
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
        $(document).ready(function() {
            $('#logsTable').DataTable({
                ajax: {
                    url: 'api/score_logs.php?action=read',
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
                    },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            return `<div class="fw-bold text-primary">${row.student_code}</div>
                                    <div class="small">${row.student_name}</div>
                                    <div class="small text-muted border-top mt-1 pt-1">${row.subject_code} ${row.subject_name}</div>`;
                        }
                    },
                    { 
                        data: 'reason',
                        render: function(data) {
                            return `<span class="text-danger fw-bold">${data || '-'}</span>`;
                        }
                    },
                    { 
                        data: null,
                        className: 'text-center align-middle',
                        render: function(data, type, row) {
                            // Escape JSON for button attribute
                            const oldJson = encodeURIComponent(row.old_data || '{}');
                            const newJson = encodeURIComponent(row.new_data || '{}');
                            return `<button class="btn btn-sm btn-outline-primary" onclick="viewData('${oldJson}', '${newJson}')"><i class="fas fa-search me-1"></i> ดูข้อมูลการแก้ไข</button>`;
                        }
                    }
                ],
                order: [[0, 'desc']], 
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
                }
            });
        });

        function viewData(oldJsonEnc, newJsonEnc) {
            try {
                const oldObj = JSON.parse(decodeURIComponent(oldJsonEnc));
                const newObj = JSON.parse(decodeURIComponent(newJsonEnc));
                $('#oldDataDisplay').text(JSON.stringify(oldObj, null, 2));
                $('#newDataDisplay').text(JSON.stringify(newObj, null, 2));
                $('#dataModal').modal('show');
            } catch (e) {
                alert('ข้อมูล JSON มีปัญหาในการแปล');
            }
        }
    </script>
</body>
</html>
