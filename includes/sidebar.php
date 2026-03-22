<?php 
if(session_status() !== PHP_SESSION_ACTIVE) session_start(); 
$current_page = isset($_GET['page']) ? $_GET['page'] : basename($_SERVER['PHP_SELF']); 
?>
<!-- Sidebar Component -->
<nav id="sidebar">
    <div class="sidebar-header">
        <h4 class="brand-text"><img src="images/favicon.svg" alt="Logo" width="32" height="32"> สาธิตวิทยา</h4>
    </div>

    <ul class="list-unstyled components">
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <li class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
            <a href="index.php"><i class="fas fa-tachometer-alt fa-fw"></i> แดชบอร์ด</a>
        </li>
        <?php endif; ?>

        <?php if (in_array($_SESSION['role'], ['admin', 'teacher'])): ?>
        <!-- Module 1: Master Data Management -->
        <?php 
            $master_pages = ['teachers.php', 'students.php', 'subjects.php', 'classes.php', 'classrooms.php', 'physical_rooms.php'];
            $is_master_active = in_array($current_page, $master_pages); 
        ?>
        <li class="<?php echo $is_master_active ? 'active' : ''; ?>">
            <a href="#masterDataSubmenu" data-bs-toggle="collapse" aria-expanded="<?php echo $is_master_active ? 'true' : 'false'; ?>" class="dropdown-toggle <?php echo $is_master_active ? '' : 'collapsed'; ?>">
                <i class="fas fa-database fa-fw"></i> จัดการข้อมูลพื้นฐาน
            </a>
            <ul class="collapse list-unstyled <?php echo $is_master_active ? 'show' : ''; ?>" id="masterDataSubmenu">
                <li class="<?php echo ($current_page == 'teachers.php') ? 'active' : ''; ?>">
                    <a href="teachers.php"><i class="fas fa-angle-right fa-fw" style="font-size: 0.8em; opacity: 0.5;"></i> ข้อมูลบุคลากรครู</a>
                </li>
                <li class="<?php echo ($current_page == 'students.php') ? 'active' : ''; ?>">
                    <a href="students.php"><i class="fas fa-angle-right fa-fw" style="font-size: 0.8em; opacity: 0.5;"></i> ข้อมูลนักเรียน</a>
                </li>
                <li class="<?php echo ($current_page == 'subjects.php') ? 'active' : ''; ?>">
                    <a href="subjects.php"><i class="fas fa-angle-right fa-fw" style="font-size: 0.8em; opacity: 0.5;"></i> ข้อมูลรายวิชา</a>
                </li>
                <li class="<?php echo ($current_page == 'classes.php') ? 'active' : ''; ?>">
                    <a href="classes.php"><i class="fas fa-angle-right fa-fw" style="font-size: 0.8em; opacity: 0.5;"></i> ข้อมูลระดับชั้นเรียน</a>
                </li>
                <li class="<?php echo ($current_page == 'classrooms.php') ? 'active' : ''; ?>">
                    <a href="classrooms.php"><i class="fas fa-angle-right fa-fw" style="font-size: 0.8em; opacity: 0.5;"></i> ข้อมูลห้องเรียน</a>
                </li>
                <li class="<?php echo ($current_page == 'physical_rooms.php') ? 'active' : ''; ?>">
                    <a href="physical_rooms.php"><i class="fas fa-angle-right fa-fw" style="font-size: 0.8em; opacity: 0.5;"></i> สถานที่เรียน (Physical)</a>
                </li>
            </ul>
        </li>
        <?php endif; ?>

        <?php if (in_array($_SESSION['role'], ['admin', 'teacher'])): ?>
        <!-- Module 2: Schedule Management -->
        <?php 
            $schedule_pages = ['schedule_manage.php', 'schedule_substitutions.php', 'schedule_logs_view.php'];
            $is_schedule_active = in_array($current_page, $schedule_pages); 
        ?>
        <li class="<?php echo $is_schedule_active ? 'active' : ''; ?>">
            <a href="#scheduleSubmenu" data-bs-toggle="collapse" aria-expanded="<?php echo $is_schedule_active ? 'true' : 'false'; ?>" class="dropdown-toggle <?php echo $is_schedule_active ? '' : 'collapsed'; ?>">
                <i class="fas fa-calendar-alt fa-fw"></i> จัดการตารางเรียน
            </a>
            <ul class="collapse list-unstyled <?php echo $is_schedule_active ? 'show' : ''; ?>" id="scheduleSubmenu">
                <li class="<?php echo ($current_page == 'schedule_manage.php') ? 'active' : ''; ?>">
                    <a href="schedule_manage.php"><i class="fas fa-angle-right fa-fw" style="font-size: 0.8em; opacity: 0.5;"></i> จัดตารางเรียน</a>
                </li>
                <li class="<?php echo ($current_page == 'schedule_substitutions.php') ? 'active' : ''; ?>">
                    <a href="schedule_substitutions.php"><i class="fas fa-angle-right fa-fw" style="font-size: 0.8em; opacity: 0.5;"></i> จัดการครูสอนแทน</a>
                </li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <li class="<?php echo ($current_page == 'schedule_logs_view.php') ? 'active' : ''; ?>">
                    <a href="schedule_logs_view.php"><i class="fas fa-angle-right fa-fw" style="font-size: 0.8em; opacity: 0.5;"></i> ประวัติการแก้ไข</a>
                </li>
                <?php endif; ?>
            </ul>
        </li>

        <!-- Module 3: Grading System -->
        <?php 
            $grading_pages = ['grading_config.php', 'grading_entry.php', 'grading_report.php', 'grading_logs.php'];
            $is_grading_active = in_array($current_page, $grading_pages); 
        ?>
        <li class="<?php echo $is_grading_active ? 'active' : ''; ?>">
            <a href="#gradingSubmenu" data-bs-toggle="collapse" aria-expanded="<?php echo $is_grading_active ? 'true' : 'false'; ?>" class="dropdown-toggle <?php echo $is_grading_active ? '' : 'collapsed'; ?>">
                <i class="fas fa-star fa-fw"></i> บันทึกผลการเรียน
            </a>
            <ul class="collapse list-unstyled <?php echo $is_grading_active ? 'show' : ''; ?>" id="gradingSubmenu">
                <li class="<?php echo ($current_page == 'grading_config.php') ? 'active' : ''; ?>">
                    <a href="grading_config.php"><i class="fas fa-angle-right fa-fw" style="font-size: 0.8em; opacity: 0.5;"></i> กำหนดโครงสร้างคะแนน</a>
                </li>
                <li class="<?php echo ($current_page == 'grading_entry.php') ? 'active' : ''; ?>">
                    <a href="grading_entry.php"><i class="fas fa-angle-right fa-fw" style="font-size: 0.8em; opacity: 0.5;"></i> บันทึกผลและตัดเกรด</a>
                </li>
                <li class="<?php echo ($current_page == 'grading_report.php') ? 'active' : ''; ?>">
                    <a href="grading_report.php"><i class="fas fa-angle-right fa-fw" style="font-size: 0.8em; opacity: 0.5;"></i> รายงานและสถิติ</a>
                </li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <li class="<?php echo ($current_page == 'grading_logs.php') ? 'active' : ''; ?>">
                    <a href="grading_logs.php"><i class="fas fa-angle-right fa-fw" style="font-size: 0.8em; opacity: 0.5;"></i> ประวัติการแก้เกรด</a>
                </li>
                <?php endif; ?>
            </ul>
        </li>

        <!-- Module 5: Attendance Tracking System -->
        <?php 
            $attendance_pages = ['attendance_entry.php', 'leave_requests.php', 'attendance_report.php', 'attendance_audit.php'];
            $is_attendance_active = in_array($current_page, $attendance_pages); 
        ?>
        <li class="<?php echo $is_attendance_active ? 'active' : ''; ?>">
            <a href="#attendanceSubmenu" data-bs-toggle="collapse" aria-expanded="<?php echo $is_attendance_active ? 'true' : 'false'; ?>" class="dropdown-toggle <?php echo $is_attendance_active ? '' : 'collapsed'; ?>">
                <i class="fas fa-user-check fa-fw"></i> บันทึกเวลาเรียน
            </a>
            <ul class="collapse list-unstyled <?php echo $is_attendance_active ? 'show' : ''; ?>" id="attendanceSubmenu">
                <li class="<?php echo ($current_page == 'attendance_entry.php') ? 'active' : ''; ?>">
                    <a href="attendance_entry.php"><i class="fas fa-angle-right fa-fw" style="font-size: 0.8em; opacity: 0.5;"></i> เช็กชื่อรายคาบ</a>
                </li>
                <li class="<?php echo ($current_page == 'leave_requests.php') ? 'active' : ''; ?>">
                    <a href="leave_requests.php"><i class="fas fa-angle-right fa-fw" style="font-size: 0.8em; opacity: 0.5;"></i> จัดการใบลา</a>
                </li>
                <li class="<?php echo ($current_page == 'attendance_report.php') ? 'active' : ''; ?>">
                    <a href="attendance_report.php"><i class="fas fa-angle-right fa-fw" style="font-size: 0.8em; opacity: 0.5;"></i> สถิติและแจ้งเตือน มส.</a>
                </li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <li class="<?php echo ($current_page == 'attendance_audit.php') ? 'active' : ''; ?>">
                    <a href="attendance_audit.php"><i class="fas fa-angle-right fa-fw" style="font-size: 0.8em; opacity: 0.5;"></i> ประวัติการแก้เวลาเรียน (Audit)</a>
                </li>
                <?php endif; ?>
            </ul>
        </li>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'student'): ?>
        <!-- Student Schedule -->
        <li class="<?php echo ($current_page == 'student_schedule.php') ? 'active' : ''; ?>">
            <a href="student_schedule.php"><i class="fas fa-calendar-alt fa-fw"></i> ตารางเรียน (Student)</a>
        </li>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'admin'): ?>
        <!-- Module 4: Auth & Access Control (Admin Settings) -->
        <?php 
            $admin_pages = ['admin_settings.php', 'admin_users.php', 'admin_data.php', 'admin_logs.php'];
            $is_admin_active = in_array($current_page, $admin_pages); 
        ?>
        <li class="<?php echo $is_admin_active ? 'active' : ''; ?>">
            <a href="#adminSubmenu" data-bs-toggle="collapse" aria-expanded="<?php echo $is_admin_active ? 'true' : 'false'; ?>" class="dropdown-toggle <?php echo $is_admin_active ? '' : 'collapsed'; ?>">
                <i class="fas fa-layer-group fa-fw"></i> ผู้ดูแลระบบ (Admin)
            </a>
            <ul class="collapse list-unstyled <?php echo $is_admin_active ? 'show' : ''; ?>" id="adminSubmenu">
                <li class="<?php echo ($current_page == 'admin_users.php') ? 'active' : ''; ?>">
                    <a href="admin_users.php"><i class="fas fa-angle-right fa-fw" style="font-size: 0.8em; opacity: 0.5;"></i> จัดการผู้ใช้สิทธิ์ (RBAC)</a>
                </li>
                <li class="<?php echo ($current_page == 'admin_settings.php') ? 'active' : ''; ?>">
                    <a href="admin_settings.php"><i class="fas fa-angle-right fa-fw" style="font-size: 0.8em; opacity: 0.5;"></i> ตั้งค่าและประกาศ (CMS)</a>
                </li>
                <li class="<?php echo ($current_page == 'admin_data.php') ? 'active' : ''; ?>">
                    <a href="admin_data.php"><i class="fas fa-angle-right fa-fw" style="font-size: 0.8em; opacity: 0.5;"></i> นำเข้าข้อมูลและสำรอง DB</a>
                </li>
                <li class="<?php echo ($current_page == 'admin_logs.php') ? 'active' : ''; ?>">
                    <a href="admin_logs.php"><i class="fas fa-angle-right fa-fw" style="font-size: 0.8em; opacity: 0.5;"></i> บันทึกความปลอดภัย (Audit)</a>
                </li>
            </ul>
        </li>
        <?php endif; ?>
    </ul>

    <div class="p-3 text-center" style="opacity: 0.6; font-size: 0.8rem;">
        v1.0.0 School Management
    </div>
</nav>