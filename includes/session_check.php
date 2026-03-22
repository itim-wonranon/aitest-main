<?php
session_start();
// session_check.php
// Middleware to ensure user is logged in
// Include this at the TOP of every protected page

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';
global $conn;

// Check Maintenance Mode
try {
    $stmt = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'maintenance_mode'");
    $maintenance = $stmt->fetchColumn();
    // Allow 'admin' to bypass, but what if admin is impersonating a teacher? We check true role or impersonated?
    // Impersonator is the real admin.
    $real_role = isset($_SESSION['impersonator_role']) ? $_SESSION['impersonator_role'] : $_SESSION['role'];
    
    if ($maintenance === 'on' && $real_role !== 'admin') {
        session_destroy();
        session_start();
        $_SESSION['error_msg'] = "ระบบอยู่ในโหมดปิดปรับปรุง กรุณาเข้าใช้งานใหม่ภายหลัง (Maintenance Mode)";
        header("Location: login.php");
        exit();
    }
    
    // Check if user is suspended mid-session
    $user_id_check = isset($_SESSION['impersonator_id']) ? $_SESSION['impersonator_id'] : $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->execute([$user_id_check]);
    $user_status = $stmt->fetchColumn();
    
    if ($user_status === 'suspended') {
        session_destroy();
        session_start();
        $_SESSION['error_msg'] = "บัญชีนี้ถูกระงับการใช้งาน กรุณาติดต่อแอดมิน";
        header("Location: login.php");
        exit();
    }
} catch (PDOException $e) {
    // If table doesn't exist yet (during setup), ignore
}

// Impersonation Banner
if (isset($_SESSION['impersonator_id'])) {
    echo '<div style="background-color: #e74a3b; color: white; text-align: center; padding: 10px; font-weight: bold; position: fixed; top: 0; left: 0; width: 100%; z-index: 9999; box-shadow: 0 4px 6px rgba(0,0,0,0.3);">
            ⚠️ แจ้งเตือน: คุณกำลังใช้งานในโหมด Impersonation (สวมรอยเป็น: ' . htmlspecialchars($_SESSION['full_name']) . ' - ' . $_SESSION['role'] . ') 
            <a href="api/admin_api.php?action=revert_impersonate" class="btn btn-sm btn-light ms-3 fw-bold text-dark">กลับคืนร่างเดิม (Revert)</a>
          </div>
          <style>body { padding-top: 50px !important; }</style>';
}

// Optional: regenerate session ID
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Helper function for Role-Based Access Control
function check_role($allowed_roles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        if (isset($_SESSION['role'])) {
            if ($_SESSION['role'] === 'student') header("Location: student_schedule.php");
            elseif ($_SESSION['role'] === 'teacher') header("Location: teachers.php");
            else header("Location: index.php");
        } else {
            header("Location: login.php");
        }
        exit();
    }
}
?>
