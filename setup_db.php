<?php
// setup_db.php
// Script to automatically create database, tables, and mock data for testing

$host = 'localhost';
$username = 'root';
$password = '';

try {
    // 1. Connect without DB name to create DB
    $conn = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Create Database
    $conn->exec("CREATE DATABASE IF NOT EXISTS school_management_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database created successfully.<br>";

    // 3. Connect to created Database
    $conn->exec("USE school_management_db");

    // 4. Create Users table (Module 4)
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'teacher', 'student') NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $conn->exec($sql_users);
    echo "Users table created successfully.<br>";

    // 5. Insert mock data with secure passwords
    // Passwords should be hashed using bcrypt algorithm (PASSWORD_DEFAULT in PHP)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert_stmt = $conn->prepare("INSERT INTO users (username, password, role, first_name, last_name) VALUES (:username, :password, :role, :first_name, :last_name)");

        $mock_users = [
            ['admin', password_hash('admin123', PASSWORD_DEFAULT), 'admin', 'ผู้ดูแลระบบ', 'สููงสุด'],
            ['teacher1', password_hash('teacher123', PASSWORD_DEFAULT), 'teacher', 'สมหมาย', 'ใจดี'],
            ['student1', password_hash('student123', PASSWORD_DEFAULT), 'student', 'สมชาย', 'ขยันเรียน']
        ];

        foreach ($mock_users as $user) {
            $insert_stmt->execute([
                ':username' => $user[0],
                ':password' => $user[1],
                ':role' => $user[2],
                ':first_name' => $user[3],
                ':last_name' => $user[4]
            ]);
        }
        echo "Mock users inserted successfully.<br>";
        echo "<br><b>Accounts created:</b><br>";
        echo "Admin: admin / admin123<br>";
        echo "Teacher: teacher1 / teacher123<br>";
        echo "Student: student1 / student123<br>";
    }
    else {
        echo "Users already exist in database.<br>";
    }

    // 6. Create Teachers table (Module 1)
    $sql_teachers = "CREATE TABLE IF NOT EXISTS teachers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        teacher_code VARCHAR(20) NOT NULL UNIQUE,
        full_name VARCHAR(150) NOT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        department VARCHAR(100) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->exec($sql_teachers);
    echo "Teachers table created successfully.<br>";
    
    // Insert mock teachers data
    $stmt = $conn->prepare("SELECT COUNT(*) FROM teachers");
    $stmt->execute();
    if($stmt->fetchColumn() == 0) {
        $insert_teacher = $conn->prepare("INSERT INTO teachers (teacher_code, full_name, phone, department) VALUES (:code, :name, :phone, :dept)");
        $mock_teachers = [
            ['T001', 'สมหมาย ใจดี', '081-111-1111', 'คณิตศาสตร์'],
            ['T002', 'วิภาวดี มีสุข', '082-222-2222', 'วิทยาศาสตร์'],
            ['T003', 'สมศักดิ์ รักเรียน', '083-333-3333', 'ภาษาไทย'],
            ['T004', 'นันทนา นำพา', '084-444-4444', 'ภาษาต่างประเทศ']
        ];
        foreach($mock_teachers as $t) {
            $insert_teacher->execute([':code' => $t[0], ':name' => $t[1], ':phone' => $t[2], ':dept' => $t[3]]);
        }
        echo "Mock teachers inserted successfully.<br>";
    }

    echo "<br><a href='index.php'>Go to Login</a>";

}
catch (PDOException $e) {
    die("DB Setup Error: " . $e->getMessage());
}
?>
