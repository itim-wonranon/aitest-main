<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Admin Access Only']);
    exit();
}

$action = $_REQUEST['action'] ?? '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'read') {
        
        $sql = "SELECT l.*, 
                       u.first_name, u.last_name, u.username,
                       s.student_code, s.full_name as student_name,
                       sub.subject_code, sub.subject_name
                FROM score_logs l
                LEFT JOIN users u ON l.user_id = u.id
                LEFT JOIN student_scores sc ON l.student_score_id = sc.id
                LEFT JOIN students s ON sc.student_id = s.id
                LEFT JOIN subjects sub ON sc.subject_id = sub.id
                ORDER BY l.created_at DESC";
                
        $stmt = $conn->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $data]);
        
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>
