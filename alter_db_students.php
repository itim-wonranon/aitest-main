<?php
require_once 'config/database.php';
try {
    $sql = "CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_code VARCHAR(20) NOT NULL UNIQUE,
        full_name VARCHAR(100) NOT NULL,
        class_level VARCHAR(50) NOT NULL,
        room VARCHAR(20) NOT NULL,
        parent_phone VARCHAR(20),
        profile_image VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Table 'students' created successfully.\n";

    // Insert dummy data
    $stmt = $conn->query("SELECT id FROM students LIMIT 1");
    if ($stmt->rowCount() == 0) {
        $dummyStudents = [
            ['S0001', 'เด็กชาย สมคิด ติดเรียน', 'ม.1', '1', '0891112222', null],
            ['S0002', 'เด็กหญิง สมใจ ใฝ่รู้', 'ม.1', '1', '0893334444', null],
            ['S0003', 'นาย กล้าหาญ ชาญชัย', 'ม.4', '2', '0812223333', null],
        ];
        
        $insertStmt = $conn->prepare("INSERT INTO students (student_code, full_name, class_level, room, parent_phone, profile_image) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($dummyStudents as $student) {
            $insertStmt->execute($student);
        }
        echo "Dummy data inserted.\n";
    } else {
        echo "Table 'students' already has data.\n";
    }

} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>
