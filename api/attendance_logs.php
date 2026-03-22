<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        $sql = "SELECT log.*, 
                       u.full_name as teacher_admin_name,
                       s.student_code, s.full_name as student_name, c.class_level, c.room_name as room,
                       sess.session_date, sess.device_ip,
                       sub.subject_code, sub.subject_name
                FROM attendance_logs log
                JOIN users u ON log.user_id = u.id
                JOIN attendances att ON log.attendance_id = att.id
                JOIN students s ON att.student_id = s.id
                JOIN attendance_sessions sess ON att.session_id = sess.id
                JOIN schedules sch ON sess.schedule_id = sch.id
                JOIN subjects sub ON sch.subject_id = sub.id
                JOIN classrooms c ON sch.classroom_id = c.id
                ORDER BY log.created_at DESC
                LIMIT 500";
                
        $stmt = $conn->query($sql);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $logs]);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
}
?>
