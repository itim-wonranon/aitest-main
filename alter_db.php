<?php
require_once 'config/database.php';
try {
    // Add line_id column
    $conn->exec("ALTER TABLE teachers ADD COLUMN IF NOT EXISTS line_id VARCHAR(50) DEFAULT NULL AFTER phone;");
    // Add profile_image column
    $conn->exec("ALTER TABLE teachers ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) DEFAULT NULL AFTER line_id;");
    echo "Table 'teachers' altered successfully. Added line_id and profile_image.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Columns already exist.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
