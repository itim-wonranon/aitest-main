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
            $sql = "SELECT ss.*, 
                    t.full_name as substitute_teacher_name,
                    s.day_of_week, s.start_time, s.end_time,
                    sub.subject_name,
                    c.class_level, c.room_name as class_room_name,
                    orig_t.full_name as original_teacher_name
                    FROM schedule_substitutions ss
                    JOIN schedules s ON ss.schedule_id = s.id
                    LEFT JOIN teachers t ON ss.substitute_teacher_id = t.id
                    LEFT JOIN subjects sub ON s.subject_id = sub.id
                    LEFT JOIN teachers orig_t ON s.teacher_id = orig_t.id
                    LEFT JOIN classrooms c ON s.classroom_id = c.id
                    ORDER BY ss.absence_date DESC";
                    
            $stmt = $conn->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Both Admin and Teachers can create substitutions (e.g. they requested a sub)
    if ($action === 'create') {
        $schedule_id = $_POST['schedule_id'] ?? '';
        $substitute_teacher_id = $_POST['substitute_teacher_id'] ?? '';
        $absence_date = $_POST['absence_date'] ?? '';
        $reason = $_POST['reason'] ?? '';

        if (empty($schedule_id) || empty($substitute_teacher_id) || empty($absence_date)) {
            echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
            exit();
        }

        try {
            // Check if substitution already exists for that schedule on that date
            $chk = $conn->prepare("SELECT id FROM schedule_substitutions WHERE schedule_id = :s_id AND absence_date = :date");
            $chk->execute([':s_id' => $schedule_id, ':date' => $absence_date]);
            if ($chk->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'มีการจัดครูสอนแทนในรายวิชาและวันนี้แล้ว']);
                exit();
            }

            $stmt = $conn->prepare("INSERT INTO schedule_substitutions (schedule_id, substitute_teacher_id, absence_date, reason, created_by) VALUES (:s_id, :t_id, :date, :reason, :c_by)");
            $stmt->execute([
                ':s_id' => $schedule_id,
                ':t_id' => $substitute_teacher_id,
                ':date' => $absence_date,
                ':reason' => $reason,
                ':c_by' => $_SESSION['user_id']
            ]);
            
            // Log
            $log_stmt = $conn->prepare("INSERT INTO schedule_logs (user_id, action_type, details) VALUES (?, ?, ?)");
            $log_stmt->execute([$_SESSION['user_id'], 'SUBSTITUTION', "Created substitution for schedule $schedule_id on $absence_date"]);

            echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลครูสอนแทนสำเร็จ']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
        }
    } elseif ($action === 'delete') {
        if ($_SESSION['role'] !== 'admin') {
            echo json_encode(['status' => 'error', 'message' => 'Admin เท่านั้นลบได้']);
            exit();
        }

        $id = $_POST['id'] ?? '';

        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'ID ไม่ถูกต้อง']);
            exit();
        }

        try {
            $stmt = $conn->prepare("DELETE FROM schedule_substitutions WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            // Log
            $log_stmt = $conn->prepare("INSERT INTO schedule_logs (user_id, action_type, details) VALUES (?, ?, ?)");
            $log_stmt->execute([$_SESSION['user_id'], 'DELETE_SUB', "Deleted substitution ID $id"]);

            echo json_encode(['status' => 'success', 'message' => 'ลบข้อมูลสำเร็จ']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
        }
    }
}
?>
