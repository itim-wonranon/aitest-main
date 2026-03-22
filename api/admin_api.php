<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Revert impersonate can be called by anyone currently impersonating
if (isset($_GET['action']) && $_GET['action'] === 'revert_impersonate') {
    if (isset($_SESSION['impersonator_id'])) {
        $_SESSION['user_id'] = $_SESSION['impersonator_id'];
        $_SESSION['role'] = $_SESSION['impersonator_role'];
        $_SESSION['username'] = $_SESSION['impersonator_username'];
        $_SESSION['full_name'] = $_SESSION['impersonator_name'];
        
        unset($_SESSION['impersonator_id']);
        unset($_SESSION['impersonator_role']);
        unset($_SESSION['impersonator_username']);
        unset($_SESSION['impersonator_name']);
        
        header("Location: ../index.php");
        exit;
    }
}

// All other actions require real Admin role
$real_role = isset($_SESSION['impersonator_role']) ? $_SESSION['impersonator_role'] : ($_SESSION['role'] ?? '');

if (!isset($_SESSION['user_id']) || $real_role !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized Access']);
    exit();
}

$action = $_REQUEST['action'] ?? '';
$user_id = isset($_SESSION['impersonator_id']) ? $_SESSION['impersonator_id'] : $_SESSION['user_id'];

function logActivity($conn, $user_id, $action, $entity, $details, $entity_id = null) {
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, entity, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $action, $entity, $entity_id, $details, $_SERVER['REMOTE_ADDR'] ?? '']);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        if ($action === 'get_users') {
            $stmt = $conn->query("SELECT id, username, first_name, last_name, role, status FROM users ORDER BY role, id ASC");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $users]);
        }
        
        elseif ($action === 'get_settings') {
            $stmt = $conn->query("SELECT setting_key, setting_value, description FROM system_settings");
            $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $parsed = [];
            foreach($settings as $s) {
                $parsed[$s['setting_key']] = $s;
            }
            echo json_encode(['status' => 'success', 'data' => $parsed]);
        }

        elseif ($action === 'get_announcements') {
            $stmt = $conn->query("SELECT a.*, u.full_name as author_name FROM announcements a LEFT JOIN users u ON a.author_id = u.id ORDER BY a.created_at DESC");
            $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $announcements]);
        }
        
        elseif ($action === 'get_login_history') {
            $stmt = $conn->query("SELECT l.*, u.username, u.full_name FROM login_history l JOIN users u ON l.user_id = u.id ORDER BY l.login_time DESC LIMIT 500");
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $logs]);
        }
        
        elseif ($action === 'get_activity_logs') {
            $stmt = $conn->query("SELECT a.*, u.username, u.full_name FROM activity_logs a JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 500");
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $logs]);
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        if ($action === 'update_user_status') {
            $target_id = $_POST['id'] ?? 0;
            $status = $_POST['status'] ?? '';
            
            if ($target_id == $user_id) {
                echo json_encode(['status' => 'error', 'message' => 'ระงับบัญชีตัวเองไม่ได้']);
                exit;
            }
            
            $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
            if ($stmt->execute([$status, $target_id])) {
                logActivity($conn, $user_id, 'update', 'users', "Changed status to $status", $target_id);
                echo json_encode(['status' => 'success']);
            }
        }
        
        elseif ($action === 'impersonate') {
            $target_id = $_POST['id'] ?? 0;
            
            // Check if user exists and is not admin
            $stmt = $conn->prepare("SELECT id, role, username, first_name, last_name, status FROM users WHERE id = ?");
            $stmt->execute([$target_id]);
            $target = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($target) {
                if ($target['role'] === 'admin') {
                    echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถ Impersonate แอดมินด้วยกันเองได้']);
                    exit;
                }
                if ($target['status'] === 'suspended') {
                    echo json_encode(['status' => 'error', 'message' => 'บัญชีนี้ถูกระงับอยู่ ไม่สามารถใช้งานได้']);
                    exit;
                }

                // Save Original 
                if (!isset($_SESSION['impersonator_id'])) {
                    $_SESSION['impersonator_id'] = $_SESSION['user_id'];
                    $_SESSION['impersonator_role'] = $_SESSION['role'];
                    $_SESSION['impersonator_username'] = $_SESSION['username'];
                    $_SESSION['impersonator_name'] = $_SESSION['full_name'];
                }

                // Load Target
                $_SESSION['user_id'] = $target['id'];
                $_SESSION['role'] = $target['role'];
                $_SESSION['username'] = $target['username'];
                $_SESSION['full_name'] = $target['first_name'] . ' ' . $target['last_name'];
                
                logActivity($conn, $_SESSION['impersonator_id'], 'login', 'impersonation', "Impersonated user {$target['username']}", $target['id']);

                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'User not found']);
            }
        }

        elseif ($action === 'save_settings') {
            $settings = $_POST['settings'] ?? [];
            $conn->beginTransaction();
            $stmt = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
            
            foreach ($settings as $key => $val) {
                $stmt->execute([$val, $key]);
            }
            $conn->commit();
            logActivity($conn, $user_id, 'update', 'system_settings', "Updated system settings");
            echo json_encode(['status' => 'success']);
        }
        
        elseif ($action === 'save_announcement') {
            $title = $_POST['title'] ?? '';
            $message = $_POST['message'] ?? '';
            $id = $_POST['id'] ?? null;
            $status = $_POST['status'] ?? 'active';
            
            if ($id) {
                $stmt = $conn->prepare("UPDATE announcements SET title = ?, message = ?, status = ? WHERE id = ?");
                $stmt->execute([$title, $message, $status, $id]);
                logActivity($conn, $user_id, 'update', 'announcements', "Updated announcement", $id);
            } else {
                $stmt = $conn->prepare("INSERT INTO announcements (title, message, author_id, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([$title, $message, $user_id, $status]);
                logActivity($conn, $user_id, 'create', 'announcements', "Created announcement", $conn->lastInsertId());
            }
            echo json_encode(['status' => 'success']);
        }

        elseif ($action === 'delete_announcement') {
            $id = $_POST['id'] ?? 0;
            $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
            if ($stmt->execute([$id])) {
                logActivity($conn, $user_id, 'delete', 'announcements', "Deleted announcement", $id);
                echo json_encode(['status' => 'success']);
            }
        }
    }
} catch (PDOException $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
}
?>
