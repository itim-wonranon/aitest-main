<?php
session_start();
require_once '../config/database.php';

// Only Admin can backup
$real_role = isset($_SESSION['impersonator_role']) ? $_SESSION['impersonator_role'] : ($_SESSION['role'] ?? '');
if (!isset($_SESSION['user_id']) || $real_role !== 'admin') {
    die('Unauthorized Access');
}

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'school_management_db';

// Using mysqldump if available, otherwise fallback is complex.
// For XAMPP environment, mysqldump is typically at C:\xampp\mysql\bin\mysqldump
$mysqldump_path = "C:\\xampp\\mysql\\bin\\mysqldump.exe";
if (!file_exists($mysqldump_path)) {
    // Fallback to standard command if in PATH
    $mysqldump_path = "mysqldump";
}

$backup_file = "../backups/db_backup_" . date("Y-m-d_H-i-s") . ".sql";

// Create backups directory if not exists
if (!is_dir("../backups")) {
    mkdir("../backups", 0777, true);
}

// Ensure password is safe for CLI
$pass_str = empty($password) ? "" : "-p\"$password\"";

// Execute mysqldump
$command = "\"$mysqldump_path\" -h $host -u $username $pass_str $dbname > \"$backup_file\" 2>&1";
exec($command, $output, $return_var);

if ($return_var === 0 && file_exists($backup_file)) {
    // Log activity
    $user_id = isset($_SESSION['impersonator_id']) ? $_SESSION['impersonator_id'] : $_SESSION['user_id'];
    $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, entity, details, ip_address) VALUES (?, 'export', 'database', 'Exported database backup', ?)");
    $log_stmt->execute([$user_id, $_SERVER['REMOTE_ADDR'] ?? '']);

    // Force download
    header('Content-Description: File Transfer');
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="'.basename($backup_file).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($backup_file));
    readfile($backup_file);
    
    // Optional: Delete after download to save space
    @unlink($backup_file);
    exit;
} else {
    echo "Backup failed. Command: $command <br>";
    echo "Error output: " . implode("\n", $output);
}
?>
