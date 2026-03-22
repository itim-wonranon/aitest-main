<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$action = $_REQUEST['action'] ?? '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        if ($action === 'get_dashboard_stats') {
            $class_level = $_GET['class_level'] ?? '';
            $room = $_GET['room'] ?? '';
            $subject_id = $_GET['subject_id'] ?? 0;
            $year = $_GET['academic_year'] ?? date('Y') + 543; // Thai year trick or from frontend
            
            if (!$class_level || !$room || !$subject_id) {
                echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
                exit;
            }

            // 1. Get classroom id
            $stmt = $conn->prepare("SELECT id FROM classrooms WHERE class_level = ? AND room_name = ?");
            $stmt->execute([$class_level, $room]);
            $classroom = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$classroom) {
                echo json_encode(['status' => 'error', 'message' => 'Classroom not found']);
                exit;
            }

            // 2. Get students
            $stmt = $conn->prepare("SELECT id, student_code, full_name FROM students WHERE class_level = ? AND room = ? ORDER BY student_code ASC");
            $stmt->execute([$class_level, $room]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 3. Get all sessions for this subject + classroom
            $stmt = $conn->prepare("SELECT s.id FROM attendance_sessions s JOIN schedules sch ON s.schedule_id = sch.id WHERE sch.subject_id = ? AND sch.classroom_id = ?");
            $stmt->execute([$subject_id, $classroom['id']]);
            $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $total_sessions = count($sessions);
            
            $session_ids = array_column($sessions, 'id');
            $attendances = [];
            
            if ($total_sessions > 0) {
                $placeholders = implode(',', array_fill(0, count($session_ids), '?'));
                $stmt = $conn->prepare("SELECT student_id, status FROM attendances WHERE session_id IN ($placeholders)");
                $stmt->execute($session_ids);
                
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $attendances[$row['student_id']][] = $row['status'];
                }
            }

            // 4. Calculate stats per student
            foreach ($students as &$st) {
                $st_id = $st['id'];
                $st_atts = $attendances[$st_id] ?? [];
                
                $counts = [
                    'present' => 0,
                    'late' => 0,
                    'absent' => 0,
                    'sick_leave' => 0,
                    'business_leave' => 0
                ];
                
                foreach ($st_atts as $status) {
                    if (isset($counts[$status])) $counts[$status]++;
                }
                
                // Rule Engine: 3 Lates = 1 Absent
                $penalty_absences = floor($counts['late'] / 3);
                $effective_absences = $counts['absent'] + $penalty_absences;
                
                // Note: Leaves usually don't count as absent in Thai schools for eligibility, but they do reduce "study hours".
                // We'll calculate Raw Attendance % = (Present + Late + Leaves) / Total * 100
                // Effective Attendance % = ((Total - Effective Absences) / Total) * 100 
                // However, exact formula depends on school. Let's use (Present + Late + Leaves) / Total
                
                $attended = $counts['present'] + $counts['late'] + $counts['sick_leave'] + $counts['business_leave'] - $penalty_absences;
                $percent = $total_sessions > 0 ? round(($attended / $total_sessions) * 100, 2) : 100;

                $st['stats'] = $counts;
                $st['total_sessions'] = $total_sessions;
                $st['attended_sessions'] = $attended;
                $st['attendance_percent'] = $percent;
                $st['is_flagged'] = $percent < 80; // Flag for มส. (หมดสิทธิ์สอบ)
            }

            echo json_encode(['status' => 'success', 'data' => $students, 'total_sessions' => $total_sessions]);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
}
?>
