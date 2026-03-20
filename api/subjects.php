<?php
require_once '../includes/session_check.php';
require_once '../config/database.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'read':
            $stmt = $conn->prepare("SELECT id, subject_code, subject_name, subject_type, credit, department FROM subjects ORDER BY id DESC");
            $stmt->execute();
            $subjects = $stmt->fetchAll();
            echo json_encode(["data" => $subjects]);
            break;

        case 'create':
            $subject_code = trim($_POST['subject_code'] ?? '');
            $subject_name = trim($_POST['subject_name'] ?? '');
            $subject_type = trim($_POST['subject_type'] ?? '');
            $credit = floatval($_POST['credit'] ?? 0);
            $department = trim($_POST['department'] ?? '');

            if (empty($subject_code) || empty($subject_name) || empty($subject_type) || empty($department) || empty($credit)) {
                echo json_encode(["status" => "error", "message" => "กรุณากรอกข้อมูลให้ครบถ้วน"]);
                exit();
            }

            // Check duplicate code
            $stmt = $conn->prepare("SELECT id FROM subjects WHERE subject_code = ?");
            $stmt->execute([$subject_code]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(["status" => "error", "message" => "รหัสวิชานี้มีในระบบแล้ว"]);
                exit();
            }

            $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name, subject_type, credit, department) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$subject_code, $subject_name, $subject_type, $credit, $department]);
            
            echo json_encode(["status" => "success", "message" => "เพิ่มข้อมูลวิชาเรียบร้อยแล้ว"]);
            break;

        case 'update':
            $id = intval($_POST['id'] ?? 0);
            $subject_code = trim($_POST['subject_code'] ?? '');
            $subject_name = trim($_POST['subject_name'] ?? '');
            $subject_type = trim($_POST['subject_type'] ?? '');
            $credit = floatval($_POST['credit'] ?? 0);
            $department = trim($_POST['department'] ?? '');

            if (empty($id) || empty($subject_code) || empty($subject_name) || empty($subject_type) || empty($department) || empty($credit)) {
                echo json_encode(["status" => "error", "message" => "ข้อมูลไม่ครบถ้วน"]);
                exit();
            }

            // Check duplicate code for other IDs
            $stmt = $conn->prepare("SELECT id FROM subjects WHERE subject_code = ? AND id != ?");
            $stmt->execute([$subject_code, $id]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(["status" => "error", "message" => "รหัสวิชานี้ซ้ำกับข้อมูลอื่น"]);
                exit();
            }

            $stmt = $conn->prepare("UPDATE subjects SET subject_code=?, subject_name=?, subject_type=?, credit=?, department=? WHERE id=?");
            $stmt->execute([$subject_code, $subject_name, $subject_type, $credit, $department, $id]);

            echo json_encode(["status" => "success", "message" => "แก้ไขข้อมูลสำเร็จ"]);
            break;

        case 'delete':
            $id = intval($_POST['id'] ?? 0);
            if (empty($id)) {
                echo json_encode(["status" => "error", "message" => "ไม่พบข้อมูลที่ต้องการลบ"]);
                exit();
            }

            $stmt = $conn->prepare("DELETE FROM subjects WHERE id=?");
            $stmt->execute([$id]);

            echo json_encode(["status" => "success", "message" => "ลบข้อมูลสำเร็จ"]);
            break;

        default:
            echo json_encode(["status" => "error", "message" => "Invalid action"]);
            break;
    }
}
catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database Error: " . $e->getMessage()]);
}
?>
