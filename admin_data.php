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
    <title>นำเข้าและส่งออกข้อมูล - ผู้ดูแลระบบ</title>
    
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="wrapper">
        <div id="sidebar-placeholder"></div>

        <div id="content">
            <div id="header-placeholder"></div>

            <main class="container-fluid px-4 pb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-database me-2"></i>จัดการข้อมูลปริมาณมาก (Bulk Data Operations)</h2>
                </div>

                <div class="row">

                    <!-- Import Students -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white fw-bold py-3 text-primary">
                                <i class="fas fa-file-upload me-2 border p-2 rounded bg-light"></i> นำเข้ารายชื่อนักเรียน (CSV)
                            </div>
                            <div class="card-body">
                                <p class="small text-muted mb-4">
                                    อัปโหลดไฟล์ <code>.csv</code> เพื่อเพิ่มข้อมูลนักเรียนทีละหลายร้อยคนเข้าสู่ระบบโดยอัตโนมัติ 
                                    ระบบจะทำการ <strong>Validation</strong> ตรวจสอบรหัสซ้ำ หรือข้อมูลที่ขาดหาย และจะรายงานข้อผิดพลาดเป็นรายบรรทัด
                                </p>
                                
                                <div class="bg-light p-3 rounded mb-4 border">
                                    <h6 class="fw-bold mb-2"><i class="fas fa-info-circle text-info"></i> รูปแบบคอลัมน์ในไฟล์ CSV (ไล่จากซ้ายไปขวา)</h6>
                                    <ol class="small mb-0 text-muted">
                                        <li><strong>รหัสนักเรียน</strong> (เช่น 65001) <span class="text-danger">*</span></li>
                                        <li><strong>ชื่อ-นามสกุล</strong> (เช่น ด.ช. รักเรียน ขยันยิ่ง) <span class="text-danger">*</span></li>
                                        <li><strong>ระดับชั้น</strong> (ตัวเลข 1, 2, 3...)</li>
                                        <li><strong>ห้อง</strong> (ตัวเลข 1, 2, 3...)</li>
                                    </ol>
                                </div>

                                <form id="importForm" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <input class="form-control" type="file" id="csv_file" accept=".csv" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary fw-bold w-100"><i class="fas fa-cloud-upload-alt me-2"></i> เริ่มนำเข้าข้อมูล</button>
                                </form>

                                <div id="importResult" class="mt-4" style="display:none;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Database Backup -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white fw-bold py-3 text-success">
                                <i class="fas fa-download me-2 border p-2 rounded bg-light"></i> สำรองฐานข้อมูล (Database Backup)
                            </div>
                            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                                <div class="mb-4">
                                    <i class="fas fa-hdd fa-4x text-gray-300 mb-3 text-opacity-50"></i>
                                    <h5 class="fw-bold text-dark">ระบบสำรองข้อมูลฉุกเฉิน</h5>
                                    <p class="small text-muted px-4">ระบบนี้จะทำการสร้างไฟล์ <code>.sql</code> ซึ่งประกอบไปด้วย โครงสร้างและข้อมูลทั้งหมด (Data Dump) แบบ Real-time</p>
                                </div>
                                <a href="api/backup.php" class="btn btn-success btn-lg fw-bold shadow-sm px-5 prompt-font">
                                    <i class="fas fa-save me-2"></i> ดาวน์โหลด Backup Now
                                </a>
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

    <script src="js/script.js"></script>

    <script>
        $(document).ready(function() {
            $('#importForm').on('submit', function(e) {
                e.preventDefault();
                
                let fileSelect = document.getElementById('csv_file');
                if (!fileSelect.files || fileSelect.files.length === 0) {
                    alert('กรุณาเลือกไฟล์');
                    return;
                }
                
                let formData = new FormData();
                formData.append('csv_file', fileSelect.files[0]);

                let btn = $(this).find('button[type=submit]');
                let origHtml = btn.html();
                btn.html('<i class="fas fa-spinner fa-spin me-2"></i> กำลังประมวลผล...').prop('disabled', true);
                
                $('#importResult').hide().html('');

                $.ajax({
                    url: 'api/import_api.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        btn.html(origHtml).prop('disabled', false);
                        $('#importResult').show();
                        
                        if (res.status === 'success') {
                            $('#importResult').html(`
                                <div class="alert alert-success border-0 shadow-sm">
                                    <i class="fas fa-check-circle me-2"></i> <strong>สำเร็จ!</strong> ${res.message}
                                </div>
                            `);
                            $('#importForm')[0].reset();
                        } else if (res.status === 'warning') {
                            let errs = res.errors.map(e => `<li>${e}</li>`).join('');
                            $('#importResult').html(`
                                <div class="alert alert-warning border-0 shadow-sm">
                                    <i class="fas fa-exclamation-triangle me-2"></i> <strong>${res.message}</strong>
                                    <hr>
                                    <ul class="mb-0 small text-danger text-start">${errs}</ul>
                                </div>
                            `);
                        } else {
                            $('#importResult').html(`
                                <div class="alert alert-danger border-0 shadow-sm">
                                    <i class="fas fa-times-circle me-2"></i> <strong>Error:</strong> ${res.message}
                                </div>
                            `);
                        }
                    },
                    error: function() {
                        btn.html(origHtml).prop('disabled', false);
                        alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                    }
                });
            });
        });
    </script>
</body>
</html>
