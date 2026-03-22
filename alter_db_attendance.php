<?php
// alter_db_attendance.php
// Script to create tables for the Attendance Tracking System

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'school_management_db';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to database successfully.<br>\n";

    // 1. Create `attendance_sessions`
    $sql_sessions = "CREATE TABLE IF NOT EXISTS attendance_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        schedule_id INT NOT NULL,
        session_date DATE NOT NULL,
        teacher_id INT NOT NULL,
        recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        device_ip VARCHAR(45) DEFAULT NULL,
        UNIQUE KEY unique_schedule_date (schedule_id, session_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->exec($sql_sessions);
    echo "Table 'attendance_sessions' created successfully.<br>\n";

    // 2. Create `attendances`
    $sql_attendances = "CREATE TABLE IF NOT EXISTS attendances (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id INT NOT NULL,
        student_id INT NOT NULL,
        status VARCHAR(20) NOT NULL COMMENT 'present, late, absent, sick_leave, business_leave',
        note VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_session_student (session_id, student_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $conn->exec($sql_attendances);
    echo "Table 'attendances' created successfully.<br>\n";

    // 3. Create `leave_requests`
    $sql_leaves = "CREATE TABLE IF NOT EXISTS leave_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        leave_type VARCHAR(20) NOT NULL COMMENT 'sick, business',
        reason TEXT NOT NULL,
        status VARCHAR(20) DEFAULT 'pending' COMMENT 'pending, approved, rejected',
        approved_by INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $conn->exec($sql_leaves);
    echo "Table 'leave_requests' created successfully.<br>\n";

    // 4. Create `attendance_logs`
    $sql_logs = "CREATE TABLE IF NOT EXISTS attendance_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        attendance_id INT NOT NULL,
        user_id INT NOT NULL,
        old_status VARCHAR(20) DEFAULT NULL,
        new_status VARCHAR(20) NOT NULL,
        reason VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $conn->exec($sql_logs);
    echo "Table 'attendance_logs' created successfully.<br>\n";

} catch (PDOException $e) {
    die("Error setting up attendance database: " . $e->getMessage());
}
?>
