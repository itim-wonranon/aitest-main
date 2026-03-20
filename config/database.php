<?php
// config/database.php
// Database configuration and PDO connection setup

$host = 'localhost';
$db_name = 'school_management_db';
$username = 'root'; // default XAMPP
$password = ''; // default XAMPP

try {
    // connect to database
    $conn = new PDO("mysql:host={$host};dbname={$db_name};charset=utf8mb4", $username, $password);
    
    // Set PDO error mode to exception for secure debugging and catching errors
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Security: Disable emulated prepared statements to ensure true prepared statements are used
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    // Fetch objects by default for easier syntax
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch(PDOException $exception) {
    // Do not output exact error to user for security purposes
    error_log("Connection error: " . $exception->getMessage());
    die("เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล กรุณาติดต่อผู้ดูแลระบบ");
}
?>
