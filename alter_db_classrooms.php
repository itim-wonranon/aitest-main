<?php
require_once 'config/database.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS classrooms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_level VARCHAR(50) NOT NULL,
        room_name VARCHAR(50) NOT NULL,
        homeroom_teacher VARCHAR(150),
        capacity INT DEFAULT 40,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY class_room (class_level, room_name)
    )";
    $conn->exec($sql);
    echo "Table 'classrooms' created successfully.\n";

    // Insert dummy data
    $stmt = $conn->query("SELECT id FROM classrooms LIMIT 1");
    if ($stmt->rowCount() == 0) {
        $dummyClassrooms = [
            ['ม.1', '1', 'สมหมาย ใจดี', 40],
            ['ม.1', '2', 'วิภาวดี มีสุข', 40],
            ['ม.4', '1', 'สมศักดิ์ รักเรียน', 35],
            ['ม.4', '2', 'นันทนา นำพา', 35]
        ];

        $insertStmt = $conn->prepare("INSERT INTO classrooms (class_level, room_name, homeroom_teacher, capacity) VALUES (?, ?, ?, ?)");

        foreach ($dummyClassrooms as $room) {
            $insertStmt->execute($room);
        }
        echo "Dummy data for classrooms inserted.\n";
    }
    else {
        echo "Table 'classrooms' already has data.\n";
    }

}
catch (PDOException $e) {
    echo "Error creating table classrooms: " . $e->getMessage();
}
?>
