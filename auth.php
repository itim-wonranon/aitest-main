<?php
session_start();
require_once 'config/database.php';

// auth.php
// Back-end Authentication Logic

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. CSRF Protection Check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_msg'] = "เซสชั่นไม่ถูกต้อง หรือหมดอายุ กรุณาลองใหม่ (CSRF Failed)";
        header("Location: login.php");
        exit();
    }

    // 2. Sanitize and Validate Inputs
    // We trim whitespaces. htmlspecialchars is not strictly needed for DB querying with PDO, but good for tracking.
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['error_msg'] = "กรุณากรอกชื่อผู้ใช้และรหัสผ่านให้ครบถ้วน";
        header("Location: login.php");
        exit();
    }

    try {
        // 3. Prevent SQL Injection using PDO Prepared Statements
        $stmt = $conn->prepare("SELECT id, username, password, role, first_name, last_name FROM users WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();

            // 4. Verify password against hash
            if (password_verify($password, $user['password'])) {
                
                // Login Success!
                // Best practice: regenerate session ID to prevent session fixation post-login
                session_regenerate_id(true);

                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['logged_in'] = true;

                // Log entry could be added here
                
                header("Location: index.php");
                exit();

            } else {
                // Invalid Password
                // Note: Keep error message ambiguous to prevent enumerating users
                $_SESSION['error_msg'] = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
                header("Location: login.php");
                exit();
            }
        } else {
            // Invalid Username
            $_SESSION['error_msg'] = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
            header("Location: login.php");
            exit();
        }

    } catch (PDOException $e) {
        // Log the error securely
        error_log("Login DB Error: " . $e->getMessage());
        $_SESSION['error_msg'] = "ระบบฐานข้อมูลขัดข้องชั่วคราว กรุณาลองใหม่ภายหลัง";
        header("Location: login.php");
        exit();
    }
} else {
    // If accessed directly via GET, redirect back
    header("Location: login.php");
    exit();
}
?>
