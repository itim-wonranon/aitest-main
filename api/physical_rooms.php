<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if (!in_array($_SESSION['role'], ['admin', 'teacher'])) {
    echo json_encode(['status' => 'error', 'message' => 'Permission Denied']);
    exit();
}

$action = $_REQUEST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'read') {
        try {
            $stmt = $conn->query("SELECT * FROM physical_rooms ORDER BY CAST(room_name AS UNSIGNED) ASC, room_name ASC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['role'] !== 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'ไม่มีสิทธิ์ในการจัดการข้อมูล (Admin only)']);
        exit();
    }

    if ($action === 'create') {
        $room_name = trim($_POST['room_name'] ?? '');
        $room_type = trim($_POST['room_type'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 40);
        $status = trim($_POST['status'] ?? 'active');

        if (empty($room_name) || empty($room_type)) {
            echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกชื่อห้องและประเภทห้องให้ครบถ้วน']);
            exit();
        }

        try {
            $stmt = $conn->prepare("INSERT INTO physical_rooms (room_name, room_type, capacity, status) VALUES (:name, :type, :cap, :status)");
            $stmt->execute([
                ':name' => $room_name,
                ':type' => $room_type,
                ':cap' => $capacity,
                ':status' => $status
            ]);
            echo json_encode(['status' => 'success', 'message' => 'เพิ่มข้อมูลสถานที่เรียนสำเร็จ']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { 
                echo json_encode(['status' => 'error', 'message' => 'ชื่อห้องนี้ซ้ำในระบบ (มีอยู่แล้ว)']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
            }
        }
    } elseif ($action === 'update') {
        $id = $_POST['id'] ?? '';
        $room_name = trim($_POST['room_name'] ?? '');
        $room_type = trim($_POST['room_type'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 40);
        $status = trim($_POST['status'] ?? 'active');

        if (empty($id) || empty($room_name) || empty($room_type)) {
            echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
            exit();
        }

        try {
            $stmt = $conn->prepare("UPDATE physical_rooms SET room_name = :name, room_type = :type, capacity = :cap, status = :status WHERE id = :id");
            $stmt->execute([
                ':name' => $room_name,
                ':type' => $room_type,
                ':cap' => $capacity,
                ':status' => $status,
                ':id' => $id
            ]);
            echo json_encode(['status' => 'success', 'message' => 'อัปเดตข้อมูลสถานที่เรียนสำเร็จ']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo json_encode(['status' => 'error', 'message' => 'ชื่อห้องนี้ซ้ำในระบบ (มีอยู่แล้ว)']);
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
            $stmt = $conn->prepare("DELETE FROM physical_rooms WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['status' => 'success', 'message' => 'ลบข้อมูลสถานที่เรียนสำเร็จ']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
        }
    }
}
?>
