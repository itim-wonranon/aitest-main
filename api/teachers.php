<?php
// api/teachers.php
// REST API for Teacher CRUD operations

require_once '../config/database.php';
// We should check session for API too
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? ($_GET['action'] ?? '');

try {
    switch ($action) {
        case 'read':
            $stmt = $conn->prepare("SELECT id, teacher_code, full_name, phone, line_id, profile_image, department FROM teachers ORDER BY id DESC");
            $stmt->execute();
            $teachers = $stmt->fetchAll();
            echo json_encode(["data" => $teachers]);
            break;

        case 'create':
            // Check admin role maybe? For now allow if logged in, but best practice is RBAC
            $teacher_code = trim($_POST['teacher_code'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $line_id = trim($_POST['line_id'] ?? '');
            $department = trim($_POST['department'] ?? '');

            if (empty($teacher_code) || empty($full_name)) {
                echo json_encode(["status" => "error", "message" => "กรุณากรอกรหัสและชื่อครู"]);
                exit();
            }

            // Check duplicate code
            $stmt = $conn->prepare("SELECT id FROM teachers WHERE teacher_code = ?");
            $stmt->execute([$teacher_code]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(["status" => "error", "message" => "รหัสครูนี้มีในระบบแล้ว"]);
                exit();
            }

            // Handle Profile Image Upload
            $profile_image = null;
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['profile_image']['tmp_name'];
                $fileName = $_FILES['profile_image']['name'];
                $fileSize = $_FILES['profile_image']['size'];
                $fileType = $_FILES['profile_image']['type'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));

                // Allowed extensions
                $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

                if (in_array($fileExtension, $allowedfileExtensions) && in_array($fileType, $allowedMimeTypes)) {
                    // Maximum size 2MB
                    if ($fileSize <= 2 * 1024 * 1024) {
                        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                        $uploadFileDir = '../images/profiles/';
                        $dest_path = $uploadFileDir . $newFileName;
                        
                        if(move_uploaded_file($fileTmpPath, $dest_path)) {
                            $profile_image = $newFileName;
                        }
                    } else {
                        echo json_encode(["status" => "error", "message" => "ขนาดรูปภาพต้องไม่เกิน 2MB"]);
                        exit();
                    }
                } else {
                    echo json_encode(["status" => "error", "message" => "อนุญาตเฉพาะไฟล์รูปภาพ (JPG, PNG, WEBP) เท่านั้น"]);
                    exit();
                }
            }

            $stmt = $conn->prepare("INSERT INTO teachers (teacher_code, full_name, phone, line_id, department, profile_image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$teacher_code, $full_name, $phone, $line_id, $department, $profile_image]);
            
            echo json_encode(["status" => "success", "message" => "เพิ่มข้อมูลครูเรียบร้อยแล้ว"]);
            break;

        case 'update':
            $id = intval($_POST['id'] ?? 0);
            $teacher_code = trim($_POST['teacher_code'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $line_id = trim($_POST['line_id'] ?? '');
            $department = trim($_POST['department'] ?? '');

            if (empty($id) || empty($teacher_code) || empty($full_name)) {
                echo json_encode(["status" => "error", "message" => "ข้อมูลไม่ครบถ้วน"]);
                exit();
            }

            // Check duplicate code for other IDs
            $stmt = $conn->prepare("SELECT id, profile_image FROM teachers WHERE teacher_code = ? AND id != ?");
            $stmt->execute([$teacher_code, $id]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(["status" => "error", "message" => "รหัสครูนี้ซ้ำกับข้อมูลอื่น"]);
                exit();
            }

            // Original image fetch inside the specific target
            $stmt_img = $conn->prepare("SELECT profile_image FROM teachers WHERE id = ?");
            $stmt_img->execute([$id]);
            $current_image = $stmt_img->fetchColumn();
            
            $profile_image = $current_image;

            // Handle Profile Image Upload
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['profile_image']['tmp_name'];
                $fileName = $_FILES['profile_image']['name'];
                $fileSize = $_FILES['profile_image']['size'];
                $fileType = $_FILES['profile_image']['type'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));

                // Allowed extensions
                $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

                if (in_array($fileExtension, $allowedfileExtensions) && in_array($fileType, $allowedMimeTypes)) {
                    // Maximum size 2MB
                    if ($fileSize <= 2 * 1024 * 1024) {
                        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                        $uploadFileDir = '../images/profiles/';
                        $dest_path = $uploadFileDir . $newFileName;
                        
                        if(move_uploaded_file($fileTmpPath, $dest_path)) {
                            $profile_image = $newFileName;
                            // Remove old image to save space
                            if ($current_image && file_exists($uploadFileDir . $current_image)) {
                                unlink($uploadFileDir . $current_image);
                            }
                        }
                    } else {
                        echo json_encode(["status" => "error", "message" => "ขนาดรูปภาพต้องไม่เกิน 2MB"]);
                        exit();
                    }
                } else {
                    echo json_encode(["status" => "error", "message" => "อนุญาตเฉพาะไฟล์รูปภาพ (JPG, PNG, WEBP) เท่านั้น"]);
                    exit();
                }
            }

            $stmt = $conn->prepare("UPDATE teachers SET teacher_code=?, full_name=?, phone=?, line_id=?, department=?, profile_image=? WHERE id=?");
            $stmt->execute([$teacher_code, $full_name, $phone, $line_id, $department, $profile_image, $id]);

            echo json_encode(["status" => "success", "message" => "แก้ไขข้อมูลสำเร็จ"]);
            break;

        case 'delete':
            $id = intval($_POST['id'] ?? 0);
            if (empty($id)) {
                echo json_encode(["status" => "error", "message" => "ไม่พบข้อมูลที่ต้องการลบ"]);
                exit();
            }

            // Fetch image to delete file
            $stmt_img = $conn->prepare("SELECT profile_image FROM teachers WHERE id = ?");
            $stmt_img->execute([$id]);
            $current_image = $stmt_img->fetchColumn();

            if ($current_image) {
                $imgPath = '../images/profiles/' . $current_image;
                if (file_exists($imgPath)) {
                    unlink($imgPath);
                }
            }

            $stmt = $conn->prepare("DELETE FROM teachers WHERE id=?");
            $stmt->execute([$id]);

            echo json_encode(["status" => "success", "message" => "ลบข้อมูลสำเร็จ"]);
            break;

        default:
            echo json_encode(["status" => "error", "message" => "Invalid action"]);
            break;
    }
}
catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database Error: " . $e->getMessage()]);
}
?>
