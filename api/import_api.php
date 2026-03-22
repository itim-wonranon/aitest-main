<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

$real_role = isset($_SESSION['impersonator_role']) ? $_SESSION['impersonator_role'] : ($_SESSION['role'] ?? '');

if (!isset($_SESSION['user_id']) || $real_role !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized Access']);
    exit();
}

$user_id = isset($_SESSION['impersonator_id']) ? $_SESSION['impersonator_id'] : $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'Upload failed. Error code: ' . $file['error']]);
        exit;
    }
    
    $mimes = ['text/csv', 'application/vnd.ms-excel', 'text/plain'];
    if (!in_array($file['type'], $mimes)) {
        // Some systems send it as application/octet-stream, so we also check extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            echo json_encode(['status' => 'error', 'message' => 'โปรดอัปโหลดไฟล์ CSV เท่านั้น']);
            exit;
        }
    }

    $handle = fopen($file['tmp_name'], "r");
    if (!$handle) {
        echo json_encode(['status' => 'error', 'message' => 'Cannot read file']);
        exit;
    }
    
    // Read header (assuming Column 1 = student_code, Column 2 = full_name, Column 3 = class_level, Column 4 = room)
    $header = fgetcsv($handle, 1000, ",");
    
    $success_count = 0;
    $errors = [];
    $line = 2; // Assuming line 1 was header
    
    $conn->beginTransaction();
    $stmt_check = $conn->prepare("SELECT id FROM students WHERE student_code = ?");
    $stmt_insert = $conn->prepare("INSERT INTO students (student_code, full_name, class_level, room) VALUES (?, ?, ?, ?)");
    
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        // Skip empty rows
        if (empty(array_filter($data))) {
            $line++;
            continue;
        }

        $student_code = trim($data[0] ?? '');
        $full_name = trim($data[1] ?? '');
        $class_level = trim($data[2] ?? '');
        $room = trim($data[3] ?? '');

        // Validation
        if (empty($student_code) || empty($full_name)) {
            $errors[] = "บรรทัดที่ $line: รหัสนักเรียนและชื่อ-สกุล ห้ามเว้นว่าง";
            $line++;
            continue;
        }

        $stmt_check->execute([$student_code]);
        if ($stmt_check->rowCount() > 0) {
            $errors[] = "บรรทัดที่ $line: รหัสนักเรียน '$student_code' ซ้ำในระบบ";
            $line++;
            continue;
        }

        try {
            $stmt_insert->execute([$student_code, $full_name, $class_level, $room]);
            $success_count++;
        } catch (PDOException $e) {
            $errors[] = "บรรทัดที่ $line: เกิดข้อผิดพลาดฐานข้อมูล - " . $e->getMessage();
        }

        $line++;
    }
    
    fclose($handle);
    
    if (empty($errors)) {
        $conn->commit();
        // Log Activity
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, entity, details, ip_address) VALUES (?, 'create', 'students', ?, ?)");
        $log_stmt->execute([$user_id, "Bulk imported $success_count students via CSV", $_SERVER['REMOTE_ADDR'] ?? '']);
        
        echo json_encode(['status' => 'success', 'message' => "นำเข้าข้อมูลสำเร็จ $success_count รายการ"]);
    } else {
        // Rollback all if there's any error? Requirements usually want either full success or ignore errors. 
        // Let's rollback so user fixes it and uploads a clean file. "ให้ตีกลับทันที"
        $conn->rollBack();
        echo json_encode(['status' => 'warning', 'message' => "พบข้อผิดพลาด กรุณาแก้ไขไฟล์แล้วอัปโหลดใหม่", 'errors' => $errors]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
}
?>
