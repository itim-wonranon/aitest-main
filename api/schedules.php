<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$action = $_REQUEST['action'] ?? '';

// Helper function to check conflicts
function checkConflict($conn, $day, $start, $end, $teacher_id, $room_id, $class_id, $exclude_id = null) {
    $sql = "SELECT s.*, 
            t.full_name as teacher_name, 
            pr.room_name as room_name, 
            c.class_level, c.room_name as class_room_name 
            FROM schedules s
            LEFT JOIN teachers t ON s.teacher_id = t.id
            LEFT JOIN physical_rooms pr ON s.physical_room_id = pr.id
            LEFT JOIN classrooms c ON s.classroom_id = c.id
            WHERE s.day_of_week = :day
            AND s.start_time < :end 
            AND s.end_time > :start";
            
    $params = [
        ':day' => $day,
        ':start' => $start,
        ':end' => $end
    ];

    if ($exclude_id !== null) {
        $sql .= " AND s.id != :exclude_id";
        $params[':exclude_id'] = $exclude_id;
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $overlaps = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($overlaps as $overlap) {
        if ($overlap['teacher_id'] == $teacher_id) {
            return "เวลาซ้อนทับ: ครู " . $overlap['teacher_name'] . " มีสอนแล้วในช่วงเวลานี้ (ตาราง ID: " . $overlap['id'] . ")";
        }
        if ($overlap['physical_room_id'] == $room_id) {
            return "เวลาซ้อนทับ: ห้อง " . $overlap['room_name'] . " ถูกใช้งานแล้วในช่วงเวลานี้ (ตาราง ID: " . $overlap['id'] . ")";
        }
        if ($overlap['classroom_id'] == $class_id) {
            return "เวลาซ้อนทับ: นักเรียนชั้น " . $overlap['class_level'] . "/" . $overlap['class_room_name'] . " มีเรียนวิชาอื่นแล้วในช่วงเวลานี้ (ตาราง ID: " . $overlap['id'] . ")";
        }
    }
    return false;
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'get_options') {
        try {
            // Fetch multiple data sets for UI mapping
            
            // 1. Subjects
            $stmt1 = $conn->query("SELECT id, subject_code, subject_name FROM subjects ORDER BY subject_name ASC");
            $subjects = $stmt1->fetchAll(PDO::FETCH_ASSOC);
            
            // 2. Teachers
            $stmt2 = $conn->query("SELECT id, full_name, profile_image FROM teachers ORDER BY full_name ASC");
            $teachers = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            
            // 3. Physical Rooms
            $stmt3 = $conn->query("SELECT id, room_name, room_type FROM physical_rooms WHERE status='active' ORDER BY room_name ASC");
            $rooms = $stmt3->fetchAll(PDO::FETCH_ASSOC);
            
            // 4. Classrooms (Student Groups)
            $stmt4 = $conn->query("SELECT id, class_level, room_name FROM classrooms ORDER BY class_level ASC, CAST(room_name AS UNSIGNED) ASC");
            $classes = $stmt4->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'subjects' => $subjects,
                    'teachers' => $teachers,
                    'physical_rooms' => $rooms,
                    'classrooms' => $classes
                ]
            ]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
        }
    }
    elseif ($action === 'read') {
        $term = $_GET['term'] ?? ''; // Format e.g., '1/2569'
        
        try {
            $sql = "SELECT s.*, 
                    sub.subject_code, sub.subject_name,
                    t.full_name as teacher_name, t.profile_image,
                    pr.room_name as physical_room_name,
                    c.class_level, c.room_name as class_room_name
                    FROM schedules s
                    LEFT JOIN subjects sub ON s.subject_id = sub.id
                    LEFT JOIN teachers t ON s.teacher_id = t.id
                    LEFT JOIN physical_rooms pr ON s.physical_room_id = pr.id
                    LEFT JOIN classrooms c ON s.classroom_id = c.id";
            
            $params = [];
            
            if (!empty($term)) {
                $term_parts = explode('/', $term);
                if (count($term_parts) == 2) {
                    $sql .= " WHERE s.semester = :semester AND s.academic_year = :year";
                    $params[':semester'] = $term_parts[0];
                    $params[':year'] = $term_parts[1];
                }
            }
            
            $sql .= " ORDER BY s.day_of_week ASC, s.start_time ASC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['status' => 'success', 'data' => $data]);
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
        $classroom_id = $_POST['classroom_id'] ?? '';
        $subject_id = $_POST['subject_id'] ?? '';
        $teacher_id = $_POST['teacher_id'] ?? '';
        $physical_room_id = $_POST['physical_room_id'] ?? '';
        $day_of_week = $_POST['day_of_week'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $academic_year = $_POST['academic_year'] ?? '';
        $semester = $_POST['semester'] ?? '';

        if (empty($classroom_id) || empty($subject_id) || empty($teacher_id) || empty($physical_room_id) || empty($day_of_week) || empty($start_time) || empty($end_time) || empty($academic_year) || empty($semester)) {
            echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
            exit();
        }
        
        // Ensure start time is before end time
        if (strtotime($start_time) >= strtotime($end_time)) {
            echo json_encode(['status' => 'error', 'message' => 'เวลาเริ่มต้องมาก่อนเวลาเลิกเรียน']);
            exit();
        }

        try {
            // Check Conflict
            $conflictMsg = checkConflict($conn, $day_of_week, $start_time, $end_time, $teacher_id, $physical_room_id, $classroom_id);
            if ($conflictMsg) {
                echo json_encode(['status' => 'error', 'message' => $conflictMsg, 'conflict' => true]);
                exit();
            }

            $stmt = $conn->prepare("INSERT INTO schedules (classroom_id, subject_id, teacher_id, physical_room_id, day_of_week, start_time, end_time, academic_year, semester) VALUES (:c_id, :s_id, :t_id, :p_id, :day, :start, :end, :year, :sem)");
            $stmt->execute([
                ':c_id' => $classroom_id,
                ':s_id' => $subject_id,
                ':t_id' => $teacher_id,
                ':p_id' => $physical_room_id,
                ':day' => $day_of_week,
                ':start' => $start_time,
                ':end' => $end_time,
                ':year' => $academic_year,
                ':sem' => $semester
            ]);
            
            $new_id = $conn->lastInsertId();
            
            // Log action
            $log_stmt = $conn->prepare("INSERT INTO schedule_logs (user_id, action_type, details) VALUES (?, ?, ?)");
            $log_stmt->execute([$_SESSION['user_id'], 'CREATE', "Created schedule ID $new_id for classroom $classroom_id"]);
            
            echo json_encode(['status' => 'success', 'message' => 'เพิ่มตารางเรียนสำเร็จ', 'id' => $new_id]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
        }
    } elseif ($action === 'update') {
        $id = $_POST['id'] ?? '';
        $classroom_id = $_POST['classroom_id'] ?? '';
        $subject_id = $_POST['subject_id'] ?? '';
        $teacher_id = $_POST['teacher_id'] ?? '';
        $physical_room_id = $_POST['physical_room_id'] ?? '';
        $day_of_week = $_POST['day_of_week'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';

        if (empty($id) || empty($classroom_id) || empty($subject_id) || empty($teacher_id) || empty($physical_room_id) || empty($day_of_week) || empty($start_time) || empty($end_time)) {
            echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
            exit();
        }

        if (strtotime($start_time) >= strtotime($end_time)) {
            echo json_encode(['status' => 'error', 'message' => 'เวลาเริ่มต้องมาก่อนเวลาเลิกเรียน']);
            exit();
        }

        try {
            // Check Conflict
            $conflictMsg = checkConflict($conn, $day_of_week, $start_time, $end_time, $teacher_id, $physical_room_id, $classroom_id, $id);
            if ($conflictMsg) {
                echo json_encode(['status' => 'error', 'message' => $conflictMsg, 'conflict' => true]);
                exit();
            }

            $stmt = $conn->prepare("UPDATE schedules SET classroom_id=:c_id, subject_id=:s_id, teacher_id=:t_id, physical_room_id=:p_id, day_of_week=:day, start_time=:start, end_time=:end WHERE id=:id");
            $stmt->execute([
                ':c_id' => $classroom_id,
                ':s_id' => $subject_id,
                ':t_id' => $teacher_id,
                ':p_id' => $physical_room_id,
                ':day' => $day_of_week,
                ':start' => $start_time,
                ':end' => $end_time,
                ':id' => $id
            ]);
            
            // Log action
            $log_stmt = $conn->prepare("INSERT INTO schedule_logs (user_id, action_type, details) VALUES (?, ?, ?)");
            $log_stmt->execute([$_SESSION['user_id'], 'UPDATE', "Updated schedule ID $id"]);

            echo json_encode(['status' => 'success', 'message' => 'อัปเดตตารางเรียนสำเร็จ']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';

        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'ID ไม่ถูกต้อง']);
            exit();
        }

        try {
            $stmt = $conn->prepare("DELETE FROM schedules WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            // Log action
            $log_stmt = $conn->prepare("INSERT INTO schedule_logs (user_id, action_type, details) VALUES (?, ?, ?)");
            $log_stmt->execute([$_SESSION['user_id'], 'DELETE', "Deleted schedule ID $id"]);

            echo json_encode(['status' => 'success', 'message' => 'ลบข้อมูลสำเร็จ']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
        }
    }
}
?>
