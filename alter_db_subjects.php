<?php
require_once 'config/database.php';
try {
    $sql = "CREATE TABLE IF NOT EXISTS subjects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subject_code VARCHAR(20) NOT NULL UNIQUE,
        subject_name VARCHAR(100) NOT NULL,
        subject_type VARCHAR(50) NOT NULL,
        credit DECIMAL(3,1) NOT NULL,
        department VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Table 'subjects' created successfully.\n";

    // Insert dummy data
    $stmt = $conn->query("SELECT id FROM subjects LIMIT 1");
    if ($stmt->rowCount() == 0) {
        $dummySubjects = [
            ['ค21101', 'คณิตศาสตร์พื้นฐาน 1', 'รายวิชาพื้นฐาน', 1.5, 'คณิตศาสตร์'],
            ['ว21101', 'วิทยาศาสตร์ 1', 'รายวิชาพื้นฐาน', 1.5, 'วิทยาศาสตร์'],
            ['ท21101', 'ภาษาไทย 1', 'รายวิชาพื้นฐาน', 1.5, 'ภาษาไทย'],
            ['อ21201', 'ภาษาอังกฤษเพื่อการสื่อสาร', 'รายวิชาเพิ่มเติม', 1.0, 'ภาษาต่างประเทศ'],
            ['ศ21101', 'ทัศนศิลป์ 1', 'รายวิชาพื้นฐาน', 1.0, 'ศิลปะ'],
        ];
        
        $insertStmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name, subject_type, credit, department) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($dummySubjects as $subject) {
            $insertStmt->execute($subject);
        }
        echo "Dummy data inserted.\n";
    } else {
        echo "Table 'subjects' already has data.\n";
    }

} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>
