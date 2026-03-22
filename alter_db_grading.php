<?php
// alter_db_grading.php
// Script to create tables for the Grading System

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'school_management_db';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to database successfully.<br>\n";

    // 1. Create `grading_configs`
    $sql_configs = "CREATE TABLE IF NOT EXISTS grading_configs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subject_id INT NOT NULL,
        academic_year VARCHAR(4) NOT NULL,
        semester VARCHAR(1) NOT NULL,
        weight_criteria JSON NOT NULL,
        grade_thresholds JSON NOT NULL,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_subject_term (subject_id, academic_year, semester)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->exec($sql_configs);
    echo "Table 'grading_configs' created successfully.<br>\n";

    // 2. Create `student_scores`
    $sql_scores = "CREATE TABLE IF NOT EXISTS student_scores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        subject_id INT NOT NULL,
        academic_year VARCHAR(4) NOT NULL,
        semester VARCHAR(1) NOT NULL,
        scores_data JSON DEFAULT NULL,
        total_score DECIMAL(5,2) DEFAULT NULL,
        grade VARCHAR(5) DEFAULT NULL,
        is_published TINYINT(1) DEFAULT 0,
        updated_by INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_student_subject_term (student_id, subject_id, academic_year, semester)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $conn->exec($sql_scores);
    echo "Table 'student_scores' created successfully.<br>\n";

    // 3. Create `score_logs`
    $sql_logs = "CREATE TABLE IF NOT EXISTS score_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_score_id INT NOT NULL,
        user_id INT NOT NULL,
        action_type VARCHAR(50) NOT NULL,
        old_data JSON DEFAULT NULL,
        new_data JSON DEFAULT NULL,
        reason VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $conn->exec($sql_logs);
    echo "Table 'score_logs' created successfully.<br>\n";

} catch (PDOException $e) {
    die("Error setting up grading database: " . $e->getMessage());
}
?>
