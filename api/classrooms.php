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
            $stmt = $conn->query("SELECT * FROM classrooms ORDER BY class_level ASC, CAST(room_name AS UNSIGNED) ASC");
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
        $class_level = trim($_POST['class_level'] ?? '');
        $room_name = trim($_POST['room_name'] ?? '');
        $program = trim($_POST['program'] ?? '');
        $homeroom_teacher = trim($_POST['homeroom_teacher'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 40);

        if (empty($class_level) || empty($room_name)) {
            echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกระดับชั้นและห้องเรียนให้ครบถ้วน']);
            exit();
        }

        try {
            $stmt = $conn->prepare("INSERT INTO classrooms (class_level, room_name, program, homeroom_teacher, capacity) VALUES (:level, :room, :program, :teacher, :cap)");
            $stmt->execute([
                ':level' => $class_level,
                ':room' => $room_name,
                ':program' => $program,
                ':teacher' => $homeroom_teacher,
                ':cap' => $capacity
            ]);
            echo json_encode(['status' => 'success', 'message' => 'เพิ่มข้อมูลห้องเรียนสำเร็จ']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { 
                echo json_encode(['status' => 'error', 'message' => 'ห้องเรียนนี้ซ้ำในระบบ (มีอยู่แล้ว)']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
            }
        }
    } elseif ($action === 'update') {
        $id = $_POST['id'] ?? '';
        $class_level = trim($_POST['class_level'] ?? '');
        $room_name = trim($_POST['room_name'] ?? '');
        $program = trim($_POST['program'] ?? '');
        $homeroom_teacher = trim($_POST['homeroom_teacher'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 40);

        if (empty($id) || empty($class_level) || empty($room_name)) {
            echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
            exit();
        }

        try {
            $stmt = $conn->prepare("UPDATE classrooms SET class_level = :level, room_name = :room, program = :program, homeroom_teacher = :teacher, capacity = :cap WHERE id = :id");
            $stmt->execute([
                ':level' => $class_level,
                ':room' => $room_name,
                ':program' => $program,
                ':teacher' => $homeroom_teacher,
                ':cap' => $capacity,
                ':id' => $id
            ]);
            echo json_encode(['status' => 'success', 'message' => 'อัปเดตข้อมูลห้องเรียนสำเร็จ']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo json_encode(['status' => 'error', 'message' => 'ห้องเรียนนี้ซ้ำในระบบ (มีอยู่แล้ว)']);
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
            $stmt = $conn->prepare("DELETE FROM classrooms WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['status' => 'success', 'message' => 'ลบข้อมูลห้องเรียนสำเร็จ']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
        }
    }
}
?>
