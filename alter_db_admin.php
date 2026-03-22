<?php
// alter_db_admin.php
// Script to create tables for the System Administrator Module

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'school_management_db';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to database successfully.<br>\n";

    // 1. Create `system_settings`
    $sql_settings = "CREATE TABLE IF NOT EXISTS system_settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT,
        description VARCHAR(255) DEFAULT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->exec($sql_settings);
    echo "Table 'system_settings' created successfully.<br>\n";

    // Insert default settings if they don't exist
    $defaults = [
        ['current_academic_year', date('Y') + 543, 'ปีการศึกษาปัจจุบัน'],
        ['current_semester', '1', 'ภาคเรียนปัจจุบัน (1 หรือ 2)'],
        ['school_name', 'โรงเรียนสาธิตวิทยา', 'ชื่อโรงเรียน (แสดงหัวเว็บ/รายงาน)'],
        ['director_name', 'นายสมชาย รักเรียน', 'ชื่อผู้อำนวยการ (เซ็นเอกสาร)'],
        ['maintenance_mode', 'off', 'โหมดปิดปรุงระบบ (on/off)']
    ];
    $stmt = $conn->prepare("INSERT IGNORE INTO system_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
    foreach ($defaults as $d) {
        $stmt->execute($d);
    }
    echo "Default settings inserted.<br>\n";

    // 2. Create `announcements`
    $sql_announcements = "CREATE TABLE IF NOT EXISTS announcements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        author_id INT NOT NULL,
        status VARCHAR(20) DEFAULT 'active' COMMENT 'active, inactive',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $conn->exec($sql_announcements);
    echo "Table 'announcements' created successfully.<br>\n";

    // 3. Create `login_history`
    $sql_login = "CREATE TABLE IF NOT EXISTS login_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        user_agent VARCHAR(255) DEFAULT NULL,
        status VARCHAR(20) DEFAULT 'success' COMMENT 'success, failed',
        login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $conn->exec($sql_login);
    echo "Table 'login_history' created successfully.<br>\n";

    // 4. Create `activity_logs`
    $sql_activity = "CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        action VARCHAR(50) NOT NULL COMMENT 'create, update, delete, login, export',
        entity VARCHAR(50) NOT NULL COMMENT 'table name or module',
        entity_id INT DEFAULT NULL,
        details TEXT DEFAULT NULL,
        ip_address VARCHAR(45) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $conn->exec($sql_activity);
    echo "Table 'activity_logs' created successfully.<br>\n";

    // 5. Alter `users` table to add `status` column specifically for Active/Suspend Lifecycle
    // First check if column exists
    $check_users = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
    if ($check_users->rowCount() == 0) {
        $conn->exec("ALTER TABLE users ADD COLUMN status VARCHAR(20) DEFAULT 'active' COMMENT 'active, suspended' AFTER role");
        echo "Column 'status' added to 'users' table.<br>\n";
    } else {
        echo "Column 'status' already exists in 'users' table.<br>\n";
    }

} catch (PDOException $e) {
    die("Error setting up admin database: " . $e->getMessage());
}
?>
