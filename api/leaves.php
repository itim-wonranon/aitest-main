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

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        if ($action === 'read_requests') {
            // Admin/Teacher can see all or filter. Student can see only theirs.
            // For this app, let's assume students log in with their own student profile OR it's managed via admin.
            // Since our system currently mainly has Admin and Teacher roles tested, we'll allow admin/teachers to see all and create/approve.
            
            $sql = "SELECT l.*, s.student_code, s.full_name as student_name, c.class_level, c.room_name 
                    FROM leave_requests l
                    JOIN students s ON l.student_id = s.id
                    LEFT JOIN classrooms c ON s.class_level = c.class_level AND s.room = c.room_name
                    ORDER BY l.created_at DESC";
            $stmt = $conn->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['status' => 'success', 'data' => $data]);
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        if ($action === 'create_request') {
            $student_id = $_POST['student_id'] ?? 0;
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';
            $leave_type = $_POST['leave_type'] ?? '';
            $reason = $_POST['reason'] ?? '';
            
            // Auto approve if Admin/Teacher is the one submitting it on behalf of the student
            $status = in_array($role, ['admin', 'teacher']) ? 'approved' : 'pending';
            $approved_by = in_array($role, ['admin', 'teacher']) ? $user_id : null;

            if (!$student_id || !$start_date || !$end_date || !$leave_type || !$reason) {
                echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
                exit;
            }

            $stmt = $conn->prepare("INSERT INTO leave_requests (student_id, start_date, end_date, leave_type, reason, status, approved_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$student_id, $start_date, $end_date, $leave_type, $reason, $status, $approved_by]);

            echo json_encode(['status' => 'success', 'message' => 'บันทึกใบลาสำเร็จ']);
        }

        elseif ($action === 'update_status') {
            if (!in_array($role, ['admin', 'teacher'])) {
                echo json_encode(['status' => 'error', 'message' => 'Permission Denied']);
                exit;
            }

            $id = $_POST['id'] ?? 0;
            $status = $_POST['status'] ?? '';

            if (!$id || !$status) {
                echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
                exit;
            }

            $stmt = $conn->prepare("UPDATE leave_requests SET status = ?, approved_by = ? WHERE id = ?");
            $stmt->execute([$status, $user_id, $id]);

            echo json_encode(['status' => 'success', 'message' => 'อัปเดตสถานะใบลาเรียบร้อยแล้ว']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
}
?>
