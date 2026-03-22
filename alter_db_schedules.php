<?php
// alter_db_schedules.php
// Script to add tables for the Schedule Management System

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'school_management_db';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to database successfully.<br>";

    // 1. Create Physical Rooms table
    $sql_physical_rooms = "CREATE TABLE IF NOT EXISTS physical_rooms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        room_name VARCHAR(50) NOT NULL UNIQUE,
        room_type VARCHAR(50) NOT NULL,
        capacity INT DEFAULT 40,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->exec($sql_physical_rooms);
    echo "Table 'physical_rooms' created successfully.<br>";

    // Insert mock physical rooms
    $stmt = $conn->prepare("SELECT COUNT(*) FROM physical_rooms");
    $stmt->execute();
    if($stmt->fetchColumn() == 0) {
        $insert_room = $conn->prepare("INSERT INTO physical_rooms (room_name, room_type, capacity) VALUES (:name, :type, :cap)");
        $mock_rooms = [
            ['501', 'ห้องเรียนรวม', 40],
            ['502', 'ห้องเรียนรวม', 40],
            ['Lab วิทย์ 1', 'ห้องแล็บวิทย์', 30],
            ['Com 1', 'ห้องคอมพิวเตอร์', 40],
            ['สนาม 1', 'สนามกีฬา', 100]
        ];
        foreach($mock_rooms as $r) {
            $insert_room->execute([':name' => $r[0], ':type' => $r[1], ':cap' => $r[2]]);
        }
        echo "Mock physical rooms inserted successfully.<br>";
    }

    // 2. Create Schedules table
    // Note: Since we are referencing other tables, we'll store IDs
    $sql_schedules = "CREATE TABLE IF NOT EXISTS schedules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        classroom_id INT NOT NULL, /* References classrooms */
        subject_id INT NOT NULL,  /* References subjects */
        teacher_id INT NOT NULL,  /* References teachers */
        physical_room_id INT NOT NULL, /* References physical_rooms */
        day_of_week INT NOT NULL COMMENT '1=Mon, 2=Tue, 3=Wed, 4=Thu, 5=Fri, 6=Sat, 7=Sun',
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        academic_year VARCHAR(10) NOT NULL,
        semester VARCHAR(2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $conn->exec($sql_schedules);
    echo "Table 'schedules' created successfully.<br>";

    // 3. Create Substitutions table
    $sql_subs = "CREATE TABLE IF NOT EXISTS schedule_substitutions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        schedule_id INT NOT NULL,
        substitute_teacher_id INT NOT NULL,
        absence_date DATE NOT NULL,
        reason TEXT,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $conn->exec($sql_subs);
    echo "Table 'schedule_substitutions' created successfully.<br>";

    // 4. Create Schedule Logs table
    $sql_logs = "CREATE TABLE IF NOT EXISTS schedule_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        action_type VARCHAR(50) NOT NULL,
        details TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $conn->exec($sql_logs);
    echo "Table 'schedule_logs' created successfully.<br>";

    echo "<br><b>All migration steps completed successfully!</b>";

} catch (PDOException $e) {
    die("DB Setup Error: " . $e->getMessage());
}
?>
