<?php
$c = new PDO('mysql:host=localhost;dbname=school_management_db', 'root', '');
$tables = ['users', 'classes', 'classrooms', 'subjects', 'teachers', 'students', 'physical_rooms', 'schedules', 'student_schedules'];
foreach($tables as $t) {
    echo "\n--- $t ---\n";
    try {
        $s=$c->query("DESCRIBE $t");
        if($s) {
            while($r=$s->fetch(PDO::FETCH_ASSOC)) {
                echo $r['Field'].' ('.$r['Type'].")\n";
            }
        }
    } catch(Exception $e) {
        echo "Not found.\n";
    }
}
?>
