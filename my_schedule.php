<?php 
require_once 'includes/session_check.php'; 

// Fetch current user details
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$username = $_SESSION['username'];
$full_name = $_SESSION['full_name'];

// Get mapping
$target_id = 0;
$view_type = ''; // 'student' or 'teacher'
$schedule_data = [];

$days = [1=>'จันทร์', 2=>'อังคาร', 3=>'พุธ', 4=>'พฤหัสบดี', 5=>'ศุกร์'];
$periods = [
    ['time' => '08:30 - 09:20'],
    ['time' => '09:20 - 10:10'],
    ['time' => '10:10 - 11:00'],
    ['time' => '11:00 - 11:50'],
    ['time' => '11:50 - 12:50', 'is_break' => true], // Lunch
    ['time' => '12:50 - 13:40'],
    ['time' => '13:40 - 14:30'],
    ['time' => '14:30 - 15:20']
];

try {
    if ($role === 'student') {
        $view_type = 'student';
        // Get classroom ID by matching username with student_code
        $stmt = $conn->prepare("SELECT c.id, c.room_name FROM students s JOIN classrooms c ON s.room = c.room_name WHERE s.student_code = ? LIMIT 1");
        $stmt->execute([$username]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($room) {
            $target_id = $room['id'];
            // Fetch Schedule for this classroom
            $sql = "SELECT s.*, sub.subject_name, sub.subject_code, sub.credit, t.full_name as teacher_name, pr.room_name as physical_room 
                    FROM schedules s
                    LEFT JOIN subjects sub ON s.subject_id = sub.id
                    LEFT JOIN teachers t ON s.teacher_id = t.id
                    LEFT JOIN physical_rooms pr ON s.physical_room_id = pr.id
                    WHERE s.classroom_id = ? ORDER BY s.day_of_week, s.start_time";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$target_id]);
            $schedule_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } elseif ($role === 'teacher') {
        $view_type = 'teacher';
        // Get teacher id
        $stmt = $conn->prepare("SELECT id FROM teachers WHERE teacher_code = ? LIMIT 1");
        $stmt->execute([$username]);
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($teacher) {
            $target_id = $teacher['id'];
            $sql = "SELECT s.*, sub.subject_name, sub.subject_code, c.room_name as tgt_room, pr.room_name as physical_room 
                    FROM schedules s
                    LEFT JOIN subjects sub ON s.subject_id = sub.id
                    LEFT JOIN classrooms c ON s.classroom_id = c.id
                    LEFT JOIN physical_rooms pr ON s.physical_room_id = pr.id
                    WHERE s.teacher_id = ? ORDER BY s.day_of_week, s.start_time";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$target_id]);
            $schedule_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (PDOException $e) {
    die("Error fetching schedule: " . $e->getMessage());
}

// Reorganize data into multi-dimensional array [day][time]
$timetable = [];
foreach ($days as $d_num => $d_name) {
    $timetable[$d_num] = [];
}
foreach ($schedule_data as $row) {
    $time_formatted = date('H:i', strtotime($row['start_time'])) . ' - ' . date('H:i', strtotime($row['end_time']));
    $timetable[$row['day_of_week']][$time_formatted] = $row;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตารางของฉัน - ข้อมูลส่วนตัว</title>
    
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/style.css">
    <style>
        .timetable th, .timetable td { border: 1px solid #e3e6f0; text-align: center; vertical-align: middle; padding: 10px; }
        .timetable th { background-color: #f8f9fc; color: #4e73df; font-weight: bold; }
        .timetable td.period-cell { height: 100px; min-width: 120px; transition: all 0.2s; }
        .timetable td.period-cell:hover { background-color: #f1f3f9; cursor: pointer; transform: scale(1.02); box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15); z-index: 2; position: relative;}
        .day-header { min-width: 100px; background-color: #4e73df !important; color: white !important; }
        .break-time { background-color: #eaecf4; font-size: 1.2rem; font-weight: bold; color: #858796; letter-spacing: 5px;}
        .subject-box { background-color: white; border-radius: 8px; padding: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-left: 4px solid #4e73df; height: 100%; display: flex; flex-direction: column; justify-content: center;}
        .subject-code { font-size: 0.8rem; font-weight: bold; color: #4e73df; }
        .subject-name { font-size: 0.9rem; font-weight: 500; color: #3a3b45; margin: 4px 0; line-height: 1.2; }
        .subject-detail { font-size: 0.75rem; color: #858796; }
        
        .dayColor-1 { border-left-color: #f6c23e !important; } /* จันทร์ เหลือง */
        .dayColor-2 { border-left-color: #e83e8c !important; } /* อังคาร ชมพู */
        .dayColor-3 { border-left-color: #1cc88a !important; } /* พุธ เขียว */
        .dayColor-4 { border-left-color: #fd7e14 !important; } /* พฤหัส ส้ม */
        .dayColor-5 { border-left-color: #36b9cc !important; } /* ศุกร์ ฟ้า */
    </style>
</head>
<body>

    <div class="wrapper">
        <div id="sidebar-placeholder"></div>

        <div id="content">
            <div id="header-placeholder"></div>

            <main class="container-fluid px-4 pb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="h4 mb-1 text-dark fw-bold"><i class="fas fa-calendar-alt me-2 text-primary"></i>ตารางเรียนตารางสอนส่วนตัว (My Timetable)</h2>
                        <p class="text-muted small mb-0">ล็อกอินในนาม: <b><?= htmlspecialchars($full_name) ?></b> (<?= strtoupper($role) ?>)</p>
                    </div>
                    <?php if ($role === 'student' && isset($room['room_name'])): ?>
                        <div class="badge bg-primary fs-6 p-2 rounded-pill shadow-sm"><i class="fas fa-users me-1"></i> ห้องเรียน: <?= $room['room_name'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="card shadow-sm border-0 rounded-3 mb-4 overflow-hidden">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table timetable mb-0">
                                <thead>
                                    <tr>
                                        <th class="border-0 bg-white"></th>
                                        <?php foreach ($periods as $p): ?>
                                            <th><?= $p['time'] ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($days as $d_num => $d_name): ?>
                                        <tr>
                                            <th class="day-header align-middle"><?= $d_name ?></th>
                                            <?php foreach ($periods as $p): ?>
                                                <?php if (isset($p['is_break']) && $p['is_break']): ?>
                                                    <?php if ($d_num == 1): ?>
                                                        <td rowspan="5" class="break-time align-middle" style="writing-mode: vertical-rl; text-orientation: upright;">พักกลางวัน</td>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <td class="period-cell bg-light">
                                                        <?php if (isset($timetable[$d_num][$p['time']])): 
                                                            $cell = $timetable[$d_num][$p['time']];
                                                        ?>
                                                            <div class="subject-box dayColor-<?= $d_num ?>">
                                                                <div class="subject-code"><?= htmlspecialchars($cell['subject_code']) ?></div>
                                                                <div class="subject-name" title="<?= htmlspecialchars($cell['subject_name']) ?>">
                                                                    <?= htmlspecialchars(mb_strimwidth($cell['subject_name'], 0, 25, '...')) ?>
                                                                </div>
                                                                <div class="subject-detail mt-auto">
                                                                    <?php if ($view_type === 'student'): ?>
                                                                        <i class="fas fa-chalkboard-teacher text-info"></i> <?= htmlspecialchars(explode(' ', $cell['teacher_name'])[0]) ?><br>
                                                                        <i class="fas fa-map-marker-alt text-danger"></i> <?= htmlspecialchars($cell['physical_room']) ?>
                                                                    <?php else: ?>
                                                                        <i class="fas fa-users text-primary"></i> ห้อง <?= htmlspecialchars($cell['tgt_room']) ?><br>
                                                                        <i class="fas fa-map-marker-alt text-danger"></i> <?= htmlspecialchars($cell['physical_room']) ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted small" style="opacity: 0.3;">ว่าง</span>
                                                        <?php endif; ?>
                                                    </td>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </main>

            <div id="footer-placeholder"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="js/script.js"></script>

</body>
</html>
