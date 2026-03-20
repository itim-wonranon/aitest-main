<?php
require_once 'config/database.php';

try {
    $sql = "ALTER TABLE classrooms ADD COLUMN IF NOT EXISTS program VARCHAR(100) DEFAULT NULL AFTER room_name";
    $conn->exec($sql);
    echo "Column 'program' added successfully to classrooms table.\n";
    
    // Optional: Update dummy data
    $conn->exec("UPDATE classrooms SET program = 'วิทย์-คณิต' WHERE class_level = 'ม.4' AND room_name = '1'");
    $conn->exec("UPDATE classrooms SET program = 'ศิลป์-คำนวณ' WHERE class_level = 'ม.4' AND room_name = '2'");
    $conn->exec("UPDATE classrooms SET program = 'ห้องเรียนปกติ' WHERE class_level = 'ม.1'");
    echo "Dummy program data updated.\n";
} catch (PDOException $e) {
    echo "Error updating table: " . $e->getMessage();
}
?>
