<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$action = $_REQUEST['action'] ?? '';
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// For testing purposes, simulated device IP
$device_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        // Fetch active schedules for the teacher for a specific date
        if ($action === 'get_teacher_schedules') {
            $date = $_GET['date'] ?? date('Y-m-d');
            $day_of_week = date('N', strtotime($date)); // 1 (Mon) to 7 (Sun)
            
            // If admin, can see all. If teacher, see only theirs.
            $whereClause = "WHERE sch.day_of_week = ?";
            $params = [$day_of_week];
            
            // If there's a specific teacher id filter (used by admin or teacher)
            $teacher_id = $_GET['teacher_id'] !== '' ? $_GET['teacher_id'] : ($role === 'teacher' ? $user_id : null);
            
            if ($teacher_id) {
                $whereClause .= " AND sch.teacher_id = ?";
                $params[] = $teacher_id;
            }

            $sql = "SELECT sch.*, c.class_level, c.room_name as room, sub.subject_code, sub.subject_name 
                    FROM schedules sch
                    JOIN classrooms c ON sch.classroom_id = c.id
                    JOIN subjects sub ON sch.subject_id = sub.id
                    $whereClause
                    ORDER BY sch.start_time ASC";
                    
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Also fetch if there are existing sessions for these schedules on the specified date
            $checked_schedule_ids = [];
            if (!empty($schedules)) {
                $sch_ids = array_column($schedules, 'id');
                $placeholders = implode(',', array_fill(0, count($sch_ids), '?'));
                $sess_params = array_merge($sch_ids, [$date]);
                
                $sess_stmt = $conn->prepare("SELECT schedule_id, id as session_id FROM attendance_sessions WHERE schedule_id IN ($placeholders) AND session_date = ?");
                $sess_stmt->execute($sess_params);
                while($row = $sess_stmt->fetch(PDO::FETCH_ASSOC)) {
                    $checked_schedule_ids[$row['schedule_id']] = $row['session_id'];
                }
            }

            foreach($schedules as &$sch) {
                $sch['is_checked'] = isset($checked_schedule_ids[$sch['id']]);
                $sch['session_id'] = $checked_schedule_ids[$sch['id']] ?? null;
            }

            echo json_encode(['status' => 'success', 'data' => $schedules]);
        }
        
        // Fetch students and their attendance status for a given schedule + date
        elseif ($action === 'get_attendance_list') {
            $schedule_id = $_GET['schedule_id'] ?? 0;
            $date = $_GET['date'] ?? date('Y-m-d');

            if (!$schedule_id) {
                echo json_encode(['status' => 'error', 'message' => 'Missing schedule_id']);
                exit;
            }

            // 1. Get Schedule Info
            $stmt = $conn->prepare("SELECT sch.*, c.class_level, c.room_name as room FROM schedules sch JOIN classrooms c ON sch.classroom_id = c.id WHERE sch.id = ?");
            $stmt->execute([$schedule_id]);
            $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$schedule) {
                echo json_encode(['status' => 'error', 'message' => 'Schedule not found']);
                exit;
            }

            // 2. See if there is an existing session
            $stmt = $conn->prepare("SELECT id FROM attendance_sessions WHERE schedule_id = ? AND session_date = ?");
            $stmt->execute([$schedule_id, $date]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);
            $session_id = $session ? $session['id'] : null;

            // 3. Fetch all students in that classroom
            $stmt = $conn->prepare("SELECT id, student_code, full_name FROM students WHERE class_level = ? AND room = ? ORDER BY student_code ASC");
            $stmt->execute([$schedule['class_level'], $schedule['room']]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 4. Fetch Approved Leave Requests overlapping with this date
            $stmt = $conn->prepare("SELECT student_id, leave_type FROM leave_requests WHERE status = 'approved' AND ? BETWEEN start_date AND end_date");
            $stmt->execute([$date]);
            $leaves = [];
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $leaves[$row['student_id']] = $row['leave_type'];
            }

            // 5. Fetch existing attendances if session exists
            $attendances = [];
            if ($session_id) {
                $stmt = $conn->prepare("SELECT student_id, status, note FROM attendances WHERE session_id = ?");
                $stmt->execute([$session_id]);
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $attendances[$row['student_id']] = $row;
                }
            }

            // 6. Map everything together
            foreach ($students as &$student) {
                if (isset($attendances[$student['id']])) {
                    // Already recorded
                    $student['attendance_status'] = $attendances[$student['id']]['status'];
                    $student['note'] = $attendances[$student['id']]['note'];
                } else {
                    // Not recorded yet, set Default
                    if (isset($leaves[$student['id']])) {
                        // Inherit from Leave Request
                        $lname = strtolower($leaves[$student['id']]);
                        $student['attendance_status'] = strpos($lname, 'sick') !== false ? 'sick_leave' : 'business_leave';
                        $student['note'] = 'ดึงข้อมูลจากใบลา';
                    } else {
                        // Default to Present
                        $student['attendance_status'] = 'present';
                        $student['note'] = '';
                    }
                }
            }

            echo json_encode([
                'status' => 'success', 
                'session_id' => $session_id,
                'schedule_info' => $schedule,
                'data' => $students
            ]);
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        if ($action === 'save_bulk_attendance') {
            $schedule_id = $_POST['schedule_id'] ?? 0;
            $date = $_POST['date'] ?? date('Y-m-d');
            $students_data = isset($_POST['students_data']) && is_array($_POST['students_data']) ? $_POST['students_data'] : [];

            if (!$schedule_id || empty($students_data)) {
                echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
                exit;
            }

            $conn->beginTransaction();

            // 1. Get or Create Session
            $stmt = $conn->prepare("SELECT id FROM attendance_sessions WHERE schedule_id = ? AND session_date = ?");
            $stmt->execute([$schedule_id, $date]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($session) {
                $session_id = $session['id'];
                // Update device IP and recorded_at could be updated but leaving it to reflect first check-in time
            } else {
                $stmt = $conn->prepare("INSERT INTO attendance_sessions (schedule_id, session_date, teacher_id, device_ip) VALUES (?, ?, ?, ?)");
                $stmt->execute([$schedule_id, $date, $user_id, $device_ip]);
                $session_id = $conn->lastInsertId();
            }

            // 2. Prepare statements for Attendance
            $stmt_check = $conn->prepare("SELECT id, status FROM attendances WHERE session_id = ? AND student_id = ?");
            $stmt_insert = $conn->prepare("INSERT INTO attendances (session_id, student_id, status, note) VALUES (?, ?, ?, ?)");
            $stmt_update = $conn->prepare("UPDATE attendances SET status = ?, note = ? WHERE id = ?");
            $stmt_log = $conn->prepare("INSERT INTO attendance_logs (attendance_id, user_id, old_status, new_status, reason) VALUES (?, ?, ?, ?, 'Admin/Teacher Override via Bulk')");

            foreach ($students_data as $student_id => $data) {
                $status = $data['status'] ?? 'present';
                $note = $data['note'] ?? '';

                $stmt_check->execute([$session_id, $student_id]);
                $existing = $stmt_check->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    if ($existing['status'] !== $status) {
                        $stmt_update->execute([$status, $note, $existing['id']]);
                        $stmt_log->execute([$existing['id'], $user_id, $existing['status'], $status]);
                    } else {
                        // Just update note if changed
                        $stmt_update->execute([$status, $note, $existing['id']]);
                    }
                } else {
                    $stmt_insert->execute([$session_id, $student_id, $status, $note]);
                }
            }

            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'บันทึกเวลาเรียนสำเร็จ']);
        }
    }
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
}
?>
