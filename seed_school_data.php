<?php
// seed_school_data.php
// Run this file via terminal (php seed_school_data.php) or web browser to populate the database.
set_time_limit(0); 
require_once 'config/database.php';

try {
    echo "Cleaning up old data...\n<br>";

    // Delete existing schedules and tracking
    $conn->exec("DELETE FROM schedules");
    // Delete existing schedules and tracking
    $conn->exec("DELETE FROM schedules");
    
    // Delete master data
    $conn->exec("DELETE FROM students");
    $conn->exec("DELETE FROM teachers");
    $conn->exec("DELETE FROM subjects");
    $conn->exec("DELETE FROM classrooms");
    $conn->exec("DELETE FROM physical_rooms");
    
    // Delete users EXCEPT admin
    $conn->exec("DELETE FROM users WHERE role IN ('student', 'teacher')");
    
    // Reset Auto Increments
    $tables = ['schedules', 'students', 'teachers', 'subjects', 'classrooms', 'physical_rooms', 'users'];
    foreach($tables as $t) {
        $conn->exec("ALTER TABLE $t AUTO_INCREMENT = 1");
    }

    $default_password = password_hash('123456', PASSWORD_DEFAULT);

    echo "1. Generating Physical Rooms... \n<br>";
    $room_types = ['ห้องเรียนปกติ', 'ห้องปฏิบัติการวิทยาศาสตร์', 'ห้องคอมพิวเตอร์', 'ห้องปฏิบัติการภาษา', 'ห้องดนตรี', 'ห้องศิลปะ'];
    $physical_room_ids = [];
    for ($i = 1; $i <= 50; $i++) {
        $floor = ceil($i / 10);
        $num = pad($i % 10 == 0 ? 10 : $i % 10);
        $name = "ห้อง $floor$num";
        $type = $room_types[array_rand($room_types)];
        
        $stmt = $conn->prepare("INSERT INTO physical_rooms (room_name, room_type, capacity, status) VALUES (?, ?, 40, 'active')");
        $stmt->execute([$name, $type]);
        $physical_room_ids[] = $conn->lastInsertId();
    }

    echo "3. Generating Subjects... \n<br>";
    $subjects = [
        ['TH101', 'ภาษาไทยพื้นฐาน 1', 'พื้นฐาน', 1.5, 'หมวดภาษาไทย'],
        ['TH102', 'ภาษาไทยพื้นฐาน 2', 'พื้นฐาน', 1.5, 'หมวดภาษาไทย'],
        ['TH201', 'วรรณกรรมไทย', 'เพิ่มเติม', 1.0, 'หมวดภาษาไทย'],
        ['MA101', 'คณิตศาสตร์พื้นฐาน 1', 'พื้นฐาน', 1.5, 'หมวดคณิตศาสตร์'],
        ['MA102', 'คณิตศาสตร์พื้นฐาน 2', 'พื้นฐาน', 1.5, 'หมวดคณิตศาสตร์'],
        ['MA201', 'คณิตศาสตร์เพิ่มเติม', 'เพิ่มเติม', 2.0, 'หมวดคณิตศาสตร์'],
        ['SC101', 'วิทยาศาสตร์พื้นฐาน 1', 'พื้นฐาน', 1.5, 'หมวดวิทยาศาสตร์'],
        ['SC102', 'วิทยาศาสตร์พื้นฐาน 2', 'พื้นฐาน', 1.5, 'หมวดวิทยาศาสตร์'],
        ['SC201', 'ฟิสิกส์ 1', 'เพิ่มเติม', 1.5, 'หมวดวิทยาศาสตร์'],
        ['SC202', 'เคมี 1', 'เพิ่มเติม', 1.5, 'หมวดวิทยาศาสตร์'],
        ['SC203', 'ชีววิทยา 1', 'เพิ่มเติม', 1.5, 'หมวดวิทยาศาสตร์'],
        ['SO101', 'สังคมศึกษา 1', 'พื้นฐาน', 1.5, 'หมวดสังคมศึกษา'],
        ['SO102', 'ประวัติศาสตร์', 'พื้นฐาน', 0.5, 'หมวดสังคมศึกษา'],
        ['SO201', 'หน้าที่พลเมือง', 'เพิ่มเติม', 0.5, 'หมวดสังคมศึกษา'],
        ['EN101', 'ภาษาอังกฤษพื้นฐาน 1', 'พื้นฐาน', 1.5, 'หมวดภาษาต่างประเทศ'],
        ['EN102', 'ภาษาอังกฤษพื้นฐาน 2', 'พื้นฐาน', 1.5, 'หมวดภาษาต่างประเทศ'],
        ['EN201', 'ภาษาอังกฤษเพื่อการสื่อสาร', 'เพิ่มเติม', 1.0, 'หมวดภาษาต่างประเทศ'],
        ['EN202', 'ภาษาจีนเบื้องต้น', 'เพิ่มเติม', 1.0, 'หมวดภาษาต่างประเทศ'],
        ['HE101', 'สุขศึกษาและพลศึกษา', 'พื้นฐาน', 1.0, 'หมวดสุขศึกษาฯ'],
        ['HE201', 'กีฬาสากล', 'เพิ่มเติม', 0.5, 'หมวดสุขศึกษาฯ'],
        ['AR101', 'ทัศนศิลป์', 'พื้นฐาน', 1.0, 'หมวดศิลปะ'],
        ['AR102', 'ดนตรีสากล', 'พื้นฐาน', 1.0, 'หมวดศิลปะ'],
        ['CA101', 'การงานอาชีพ', 'พื้นฐาน', 1.0, 'หมวดการงานฯ'],
        ['CO101', 'วิทยาการคำนวณ 1', 'พื้นฐาน', 1.0, 'หมวดวิทยาศาสตร์'],
        ['CO201', 'การเขียนโปรแกรมเบื้องต้น', 'เพิ่มเติม', 1.0, 'หมวดวิทยาศาสตร์'],
    ];
    $subject_ids = [];
    foreach ($subjects as $s) {
        $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name, subject_type, credit, department) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute($s);
        $subject_ids[] = $conn->lastInsertId();
    }

    echo "4. Generating Teachers... \n<br>";
    $thai_names_first = ['สมชาย', 'สมหญิง', 'อำนาจ', 'ปรีชา', 'สุดา', 'มานี', 'ชูใจ', 'วีระ', 'นารี', 'กนกวรรณ', 'ณัฐพล', 'ธนชัย', 'วิยาดา', 'พรพรรณ', 'ศักดิ์ชัย', 'อลงกรณ์', 'จิราภรณ์', 'ศศิธร', 'อรรถพล', 'อนุชา', 'พัชรี', 'วันเฉลิม', 'วิรัช', 'วรุฒ', 'กิตติ', 'อุไร', 'ทวีศักดิ์', 'โสภณ', 'มณีรัตน์', 'ดวงดาว'];
    $thai_names_last = ['รักดี', 'ใจงาม', 'มีสุข', 'บุญมา', 'ชาวนา', 'เปี่ยมทรัพย์', 'วิริยะ', 'ทรงกลด', 'สิงห์คำ', 'เจริญรัตน์', 'ทรัพย์มณี', 'วงษ์สุวรรณ', 'ทองประเสริฐ', 'กลิ่นหอม', 'แสงสว่าง', 'ชาวไทย', 'ยอดดอย', 'ชูเกียรติ', 'รุ่งเรือง', 'มงคล'];
    
    $teacher_ids = [];
    $teacher_depts = ['หมวดภาษาไทย', 'หมวดคณิตศาสตร์', 'หมวดวิทยาศาสตร์', 'หมวดสังคมศึกษา', 'หมวดภาษาต่างประเทศ', 'หมวดสุขศึกษาฯ', 'หมวดศิลปะ', 'หมวดการงานฯ'];
    
    for ($i = 1; $i <= 50; $i++) {
        $code = 'T' . str_pad($i, 3, '0', STR_PAD_LEFT);
        $fname = $thai_names_first[array_rand($thai_names_first)];
        $lname = $thai_names_last[array_rand($thai_names_last)];
        $fullname = "$fname $lname";
        $dept = $teacher_depts[array_rand($teacher_depts)];
        
        // Insert User
        $stmt_user = $conn->prepare("INSERT INTO users (username, password, role, status, first_name, last_name) VALUES (?, ?, 'teacher', 'active', ?, ?)");
        $stmt_user->execute([$code, $default_password, $fname, $lname]);
        $user_id = $conn->lastInsertId();
        
        // Insert Teacher
        $stmt = $conn->prepare("INSERT INTO teachers (teacher_code, full_name, phone, department) VALUES (?, ?, ?, ?)");
        $stmt->execute([$code, $fullname, '08'.rand(10000000, 99999999), $dept]);
        $teacher_ids[] = $conn->lastInsertId();
    }

    echo "5. Generating Classrooms & Students (36 Rooms, 1080 Students)... \n<br>";
    $programs = ['วิทย์-คณิต', 'ศิลป์-คำนวณ', 'ศิลป์-ภาษา', 'ทั่วไป'];
    $classroom_ids = [];
    $student_seq = 60000;
    $levels = ['ป.1', 'ป.2', 'ป.3', 'ป.4', 'ป.5', 'ป.6', 'ม.1', 'ม.2', 'ม.3', 'ม.4', 'ม.5', 'ม.6'];
    
    foreach ($levels as $l) {
        for ($room = 1; $room <= 3; $room++) {
            $room_name = "$l/$room";
            $prog = ($room == 1) ? 'วิทย์-คณิต' : $programs[array_rand($programs)];
            $ht = $teacher_ids[array_rand($teacher_ids)]; // random homeroom teacher id
            
            // Insert Classroom
            $stmt = $conn->prepare("INSERT INTO classrooms (class_level, room_name, program, homeroom_teacher, capacity) VALUES (?, ?, ?, ?, 35)");
            $stmt->execute([$l, $room_name, $prog, $ht]); // Storing ID in homeroom_teacher column for simplicity as per existing loose relation
            $cid = $conn->lastInsertId();
            $classroom_ids[] = $cid;
            
            // Generate Students for this room
            for ($s = 1; $s <= 30; $s++) {
                $scode = (string)$student_seq++;
                $fname = 'ด.' . (rand(0,1) ? 'ช.' : 'ญ.') . ' ' . $thai_names_first[array_rand($thai_names_first)];
                $lname = $thai_names_last[array_rand($thai_names_last)];
                $fullname = "$fname $lname";
                
                // User
                $stmt_user = $conn->prepare("INSERT INTO users (username, password, role, status, first_name, last_name) VALUES (?, ?, 'student', 'active', ?, ?)");
                $stmt_user->execute([$scode, $default_password, $fname, $lname]);
                
                // Student
                $stmt_stud = $conn->prepare("INSERT INTO students (student_code, full_name, class_level, room, parent_phone) VALUES (?, ?, ?, ?, ?)");
                $stmt_stud->execute([$scode, $fullname, $l, $room_name, '09'.rand(10000000, 99999999)]);
            }
        }
    }

    echo "6. Generating Schedules (Master Timetable)... \n<br>";
    $days = [1, 2, 3, 4, 5]; // Mon-Fri
    $periods = [
        ['08:30:00', '09:20:00'],
        ['09:20:00', '10:10:00'],
        ['10:10:00', '11:00:00'],
        ['11:00:00', '11:50:00'], // Lunch is 11:50 - 12:50, skip this
        ['12:50:00', '13:40:00'],
        ['13:40:00', '14:30:00'],
        ['14:30:00', '15:20:00'],
    ];
    $academic_year = "2568";
    $semester = "1";
    
    // Track teacher schedules to prevent double booking. Format: teacher_day_time => true
    $teacher_schedule_map = []; 
    // Track room schedules. Format: room_day_time => true
    $room_schedule_map = [];

    $schedule_insert = $conn->prepare("INSERT INTO schedules (classroom_id, subject_id, teacher_id, physical_room_id, day_of_week, start_time, end_time, academic_year, semester) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($classroom_ids as $c_id) {
        foreach ($days as $day) {
            foreach ($periods as $p_idx => $ptimes) {
                // Try up to 50 times to find a free teacher and room
                $found = false;
                for ($tries = 0; $tries < 50; $tries++) {
                    $t_id = $teacher_ids[array_rand($teacher_ids)];
                    $r_id = $physical_room_ids[array_rand($physical_room_ids)];
                    $s_id = $subject_ids[array_rand($subject_ids)];
                    
                    $t_key = "{$t_id}_{$day}_{$p_idx}";
                    $r_key = "{$r_id}_{$day}_{$p_idx}";
                    
                    if (!isset($teacher_schedule_map[$t_key]) && !isset($room_schedule_map[$r_key])) {
                        // Free!
                        $teacher_schedule_map[$t_key] = true;
                        $room_schedule_map[$r_key] = true;
                        
                        $schedule_insert->execute([$c_id, $s_id, $t_id, $r_id, $day, $ptimes[0], $ptimes[1], $academic_year, $semester]);
                        $found = true;
                        break;
                    }
                }
            }
        }
    }

    echo "<h3>จำลองข้อมูลเสร็จสมบูรณ์!</h3><p>นักเรียน 1,080 คน, ห้องเรียน 36 ห้อง, ครู 50 ท่าน, และการจัดตารางสอนเรียบร้อย รหัสผ่านผู้ใช้ทุกคนคือ: <b>123456</b></p>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

function pad($num) { return str_pad($num, 2, '0', STR_PAD_LEFT); }
?>
