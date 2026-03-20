<?php
require_once '../includes/session_check.php';
require_once '../config/database.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'read':
            $stmt = $conn->prepare("SELECT id, student_code, full_name, class_level, room, parent_phone, profile_image FROM students ORDER BY id DESC");
            $stmt->execute();
            $students = $stmt->fetchAll();
            echo json_encode(["data" => $students]);
            break;

        case 'create':
            $student_code = trim($_POST['student_code'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $class_level = trim($_POST['class_level'] ?? '');
            $room = trim($_POST['room'] ?? '');
            $parent_phone = trim($_POST['parent_phone'] ?? '');

            if (empty($student_code) || empty($full_name)) {
                echo json_encode(["status" => "error", "message" => "กรุณากรอกรหัสและชื่อนักเรียน"]);
                exit();
            }

            // Check duplicate code
            $stmt = $conn->prepare("SELECT id FROM students WHERE student_code = ?");
            $stmt->execute([$student_code]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(["status" => "error", "message" => "รหัสนักเรียนนี้มีในระบบแล้ว"]);
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

                $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

                if (in_array($fileExtension, $allowedfileExtensions) && in_array($fileType, $allowedMimeTypes)) {
                    if ($fileSize <= 2 * 1024 * 1024) {
                        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                        $uploadFileDir = '../images/profiles/';
                        if (!is_dir($uploadFileDir)) {
                            mkdir($uploadFileDir, 0755, true);
                        }
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

            $stmt = $conn->prepare("INSERT INTO students (student_code, full_name, class_level, room, parent_phone, profile_image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$student_code, $full_name, $class_level, $room, $parent_phone, $profile_image]);
            
            echo json_encode(["status" => "success", "message" => "เพิ่มข้อมูลนักเรียนเรียบร้อยแล้ว"]);
            break;

        case 'update':
            $id = intval($_POST['id'] ?? 0);
            $student_code = trim($_POST['student_code'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $class_level = trim($_POST['class_level'] ?? '');
            $room = trim($_POST['room'] ?? '');
            $parent_phone = trim($_POST['parent_phone'] ?? '');

            if (empty($id) || empty($student_code) || empty($full_name)) {
                echo json_encode(["status" => "error", "message" => "ข้อมูลไม่ครบถ้วน"]);
                exit();
            }

            // Check duplicate code for other IDs
            $stmt = $conn->prepare("SELECT id, profile_image FROM students WHERE student_code = ? AND id != ?");
            $stmt->execute([$student_code, $id]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(["status" => "error", "message" => "รหัสนักเรียนนี้ซ้ำกับข้อมูลอื่น"]);
                exit();
            }

            // Original image fetch
            $stmt_img = $conn->prepare("SELECT profile_image FROM students WHERE id = ?");
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

                $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

                if (in_array($fileExtension, $allowedfileExtensions) && in_array($fileType, $allowedMimeTypes)) {
                    if ($fileSize <= 2 * 1024 * 1024) {
                        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                        $uploadFileDir = '../images/profiles/';
                        if (!is_dir($uploadFileDir)) {
                            mkdir($uploadFileDir, 0755, true);
                        }
                        $dest_path = $uploadFileDir . $newFileName;
                        
                        if(move_uploaded_file($fileTmpPath, $dest_path)) {
                            $profile_image = $newFileName;
                            // Remove old image
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

            $stmt = $conn->prepare("UPDATE students SET student_code=?, full_name=?, class_level=?, room=?, parent_phone=?, profile_image=? WHERE id=?");
            $stmt->execute([$student_code, $full_name, $class_level, $room, $parent_phone, $profile_image, $id]);

            echo json_encode(["status" => "success", "message" => "แก้ไขข้อมูลสำเร็จ"]);
            break;

        case 'delete':
            $id = intval($_POST['id'] ?? 0);
            if (empty($id)) {
                echo json_encode(["status" => "error", "message" => "ไม่พบข้อมูลที่ต้องการลบ"]);
                exit();
            }

            // Fetch image to delete file
            $stmt_img = $conn->prepare("SELECT profile_image FROM students WHERE id = ?");
            $stmt_img->execute([$id]);
            $current_image = $stmt_img->fetchColumn();

            if ($current_image) {
                $imgPath = '../images/profiles/' . $current_image;
                if (file_exists($imgPath)) {
                    unlink($imgPath);
                }
            }

            $stmt = $conn->prepare("DELETE FROM students WHERE id=?");
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
