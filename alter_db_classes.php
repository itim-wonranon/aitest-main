<?php
require_once 'config/database.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS class_levels (
        id INT AUTO_INCREMENT PRIMARY KEY,
        level_name VARCHAR(50) NOT NULL UNIQUE,
        level_description VARCHAR(150),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Table 'class_levels' created successfully.\n";

    // Insert dummy data
    $stmt = $conn->query("SELECT id FROM class_levels LIMIT 1");
    if ($stmt->rowCount() == 0) {
        $dummyClasses = [
            ['ม.1', 'มัธยมศึกษาปีที่ 1'],
            ['ม.2', 'มัธยมศึกษาปีที่ 2'],
            ['ม.3', 'มัธยมศึกษาปีที่ 3'],
            ['ม.4', 'มัธยมศึกษาปีที่ 4'],
            ['ม.5', 'มัธยมศึกษาปีที่ 5'],
            ['ม.6', 'มัธยมศึกษาปีที่ 6']
        ];
        
        $insertStmt = $conn->prepare("INSERT INTO class_levels (level_name, level_description) VALUES (?, ?)");
        
        foreach ($dummyClasses as $class) {
            $insertStmt->execute($class);
        }
        echo "Dummy data for class_levels inserted.\n";
    } else {
        echo "Table 'class_levels' already has data.\n";
    }

} catch (PDOException $e) {
    echo "Error creating table class_levels: " . $e->getMessage();
}
?>
