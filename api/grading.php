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

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        // 1. Get Grading Config
        if ($action === 'get_config') {
            $subject_id = $_GET['subject_id'] ?? 0;
            $year = $_GET['academic_year'] ?? '';
            $semester = $_GET['semester'] ?? '';

            if (!$subject_id || !$year || !$semester) {
                echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
                exit;
            }

            $stmt = $conn->prepare("SELECT * FROM grading_configs WHERE subject_id = ? AND academic_year = ? AND semester = ?");
            $stmt->execute([$subject_id, $year, $semester]);
            $config = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($config) {
                $config['weight_criteria'] = json_decode($config['weight_criteria'], true);
                $config['grade_thresholds'] = json_decode($config['grade_thresholds'], true);
            }

            echo json_encode(['status' => 'success', 'data' => $config]);

        } 
        
        // 2. Get Students and their Scores for Bulk Entry
        elseif ($action === 'get_students_scores') {
            $class_level = $_GET['class_level'] ?? '';
            $room = $_GET['room'] ?? '';
            $subject_id = $_GET['subject_id'] ?? 0;
            $year = $_GET['academic_year'] ?? '';
            $semester = $_GET['semester'] ?? '';

            if (!$class_level || !$room || !$subject_id || !$year || !$semester) {
                echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
                exit;
            }

            // Fetch students in the classroom
            $stmt = $conn->prepare("SELECT id, student_code, full_name FROM students WHERE class_level = ? AND room = ? ORDER BY student_code ASC");
            $stmt->execute([$class_level, $room]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch their existing scores
            $student_ids = array_column($students, 'id');
            $scores_map = [];
            
            if (!empty($student_ids)) {
                $placeholders = implode(',', array_fill(0, count($student_ids), '?'));
                $params = array_merge($student_ids, [$subject_id, $year, $semester]);
                
                $sql = "SELECT * FROM student_scores WHERE student_id IN ($placeholders) AND subject_id = ? AND academic_year = ? AND semester = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($scores as $s) {
                    $s['scores_data'] = json_decode($s['scores_data'], true);
                    $scores_map[$s['student_id']] = $s;
                }
            }

            // Combine
            $result = array_map(function($student) use ($scores_map) {
                if (isset($scores_map[$student['id']])) {
                    return array_merge($student, ['score_record' => $scores_map[$student['id']]]);
                } else {
                    return array_merge($student, ['score_record' => null]);
                }
            }, $students);

            echo json_encode(['status' => 'success', 'data' => $result]);
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        if (!in_array($_SESSION['role'], ['admin', 'teacher'])) {
            echo json_encode(['status' => 'error', 'message' => 'Permission Denied']);
            exit();
        }

        // 3. Save Grading Config
        if ($action === 'save_config') {
            $subject_id = $_POST['subject_id'] ?? 0;
            $year = $_POST['academic_year'] ?? '';
            $semester = $_POST['semester'] ?? '';
            $weight_criteria = $_POST['weight_criteria'] ?? '{}'; // Expects JSON string
            $grade_thresholds = $_POST['grade_thresholds'] ?? '{}'; // Expects JSON string

            if (!$subject_id || !$year || !$semester) {
                echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
                exit;
            }

            // Upsert (Insert or Update)
            $sql = "INSERT INTO grading_configs (subject_id, academic_year, semester, weight_criteria, grade_thresholds, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    weight_criteria = VALUES(weight_criteria), grade_thresholds = VALUES(grade_thresholds), updated_at = CURRENT_TIMESTAMP";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$subject_id, $year, $semester, $weight_criteria, $grade_thresholds, $user_id]);

            echo json_encode(['status' => 'success', 'message' => 'บันทึกโครงสร้างคะแนนสำเร็จ']);
        }

        // 4. Save Student Scores (Bulk)
        elseif ($action === 'save_scores') {
            $subject_id = $_POST['subject_id'] ?? 0;
            $year = $_POST['academic_year'] ?? '';
            $semester = $_POST['semester'] ?? '';
            $is_published = isset($_POST['is_published']) ? (int)$_POST['is_published'] : 0;
            $log_reason = $_POST['log_reason'] ?? 'Update Score'; // In case of editing a published score

            // Array of student scores: [student_id => ['scores_data' => {...}, 'total_score' => 80, 'grade' => '4']]
            $students_data = isset($_POST['students_data']) && is_array($_POST['students_data']) ? $_POST['students_data'] : [];

            if (!$subject_id || !$year || !$semester || empty($students_data)) {
                echo json_encode(['status' => 'error', 'message' => 'Missing required data']);
                exit;
            }

            $conn->beginTransaction();

            $stmt_check = $conn->prepare("SELECT id, is_published, scores_data, total_score, grade FROM student_scores WHERE student_id = ? AND subject_id = ? AND academic_year = ? AND semester = ?");
            
            $stmt_insert = $conn->prepare("INSERT INTO student_scores (student_id, subject_id, academic_year, semester, scores_data, total_score, grade, is_published, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt_update = $conn->prepare("UPDATE student_scores SET scores_data = ?, total_score = ?, grade = ?, is_published = ?, updated_by = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            
            $stmt_log = $conn->prepare("INSERT INTO score_logs (student_score_id, user_id, action_type, old_data, new_data, reason) VALUES (?, ?, 'UPDATE', ?, ?, ?)");

            foreach ($students_data as $student_id => $data) {
                // Ensure scores_data is JSON string
                $scores_json = is_array($data['scores_data']) ? json_encode($data['scores_data']) : $data['scores_data'];
                $total_score = $data['total_score'] !== '' ? $data['total_score'] : null;
                $grade = $data['grade'] !== '' ? $data['grade'] : null;

                $stmt_check->execute([$student_id, $subject_id, $year, $semester]);
                $existing = $stmt_check->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    // Force boolean/int comparison properly
                    $was_published = (int)$existing['is_published'] === 1;

                    $stmt_update->execute([$scores_json, $total_score, $grade, $is_published, $user_id, $existing['id']]);

                    // If it was already published, and we are updating it, log it.
                    if ($was_published) {
                        $old_data_json = json_encode([
                            'scores_data' => json_decode($existing['scores_data'], true),
                            'total_score' => $existing['total_score'],
                            'grade' => $existing['grade']
                        ]);
                        $new_data_json = json_encode([
                            'scores_data' => is_array($data['scores_data']) ? $data['scores_data'] : json_decode($data['scores_data'], true),
                            'total_score' => $total_score,
                            'grade' => $grade
                        ]);
                        
                        // Only log if data actually changed
                        if ($old_data_json !== $new_data_json || $is_published !== (int)$existing['is_published']) {
                            $stmt_log->execute([$existing['id'], $user_id, $old_data_json, $new_data_json, $log_reason]);
                        }
                    }
                } else {
                    $stmt_insert->execute([$student_id, $subject_id, $year, $semester, $scores_json, $total_score, $grade, $is_published, $user_id]);
                }
            }

            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'บันทึกคะแนนเรียบร้อยแล้ว']);
        }
    }
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
}
?>
