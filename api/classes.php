<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// Only admin and teacher can manage class levels
if (!in_array($_SESSION['role'], ['admin', 'teacher'])) {
    echo json_encode(['status' => 'error', 'message' => 'Permission Denied']);
    exit();
}

$action = $_REQUEST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'read') {
        try {
            $stmt = $conn->query("SELECT * FROM class_levels ORDER BY id ASC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Only Admin can modify data
    if ($_SESSION['role'] !== 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'ไม่มีสิทธิ์ในการจัดการข้อมูล (Admin only)']);
        exit();
    }

    if ($action === 'create') {
        $level_name = trim($_POST['level_name'] ?? '');
        $level_description = trim($_POST['level_description'] ?? '');

        if (empty($level_name)) {
            echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกชื่อระดับชั้นเรียน']);
            exit();
        }

        try {
            $stmt = $conn->prepare("INSERT INTO class_levels (level_name, level_description) VALUES (:name, :desc)");
            $stmt->execute([
                ':name' => $level_name,
                ':desc' => $level_description
            ]);
            echo json_encode(['status' => 'success', 'message' => 'เพิ่มข้อมูลระดับชั้นเรียนสำเร็จ']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                echo json_encode(['status' => 'error', 'message' => 'ชื่อระดับชั้นเรียนนี้ซ้ำในระบบ']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
            }
        }
    } elseif ($action === 'update') {
        $id = $_POST['id'] ?? '';
        $level_name = trim($_POST['level_name'] ?? '');
        $level_description = trim($_POST['level_description'] ?? '');

        if (empty($id) || empty($level_name)) {
            echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
            exit();
        }

        try {
            $stmt = $conn->prepare("UPDATE class_levels SET level_name = :name, level_description = :desc WHERE id = :id");
            $stmt->execute([
                ':name' => $level_name,
                ':desc' => $level_description,
                ':id' => $id
            ]);
            echo json_encode(['status' => 'success', 'message' => 'อัปเดตข้อมูลระดับชั้นเรียนสำเร็จ']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo json_encode(['status' => 'error', 'message' => 'ชื่อระดับชั้นเรียนนี้ซ้ำในระบบ']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';

        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'ID ไม่ถูกต้อง']);
            exit();
        }

        try {
            $stmt = $conn->prepare("DELETE FROM class_levels WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['status' => 'success', 'message' => 'ลบข้อมูลระดับชั้นเรียนสำเร็จ']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
        }
    }
}
?>
