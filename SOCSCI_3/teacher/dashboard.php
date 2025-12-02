<?php
include '../includes/db.php';
include '../includes/teacher_header.php';

// Get counts
$student_count_res = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='student'");
$student_count = $student_count_res->fetch_assoc()['count'];

$activity_count_res = $conn->query("SELECT COUNT(*) as count FROM activities WHERE teacher_id=" . $_SESSION['user_id']);
$activity_count = $activity_count_res->fetch_assoc()['count'];

$resource_count_res = $conn->query("SELECT COUNT(*) as count FROM resources WHERE teacher_id=" . $_SESSION['user_id']);
$resource_count = $resource_count_res->fetch_assoc()['count'];

?>

<div class="dashboard-cards">
    <div class="card" onclick="location.href='students.php'" style="cursor:pointer; background: #fff3e0;">
        <h2>Total Students</h2>
        <p style="text-align:center; font-size: 2rem;"><?= $student_count ?></p>
    </div>
    
    <div class="card" onclick="location.href='activity.php'" style="cursor:pointer; background: #e0f2f1;">
        <h2>Activities</h2>
        <p style="text-align:center; font-size: 2rem;"><?= $activity_count ?></p>
    </div>

    <div class="card" onclick="location.href='resources.php'" style="cursor:pointer; background: #e8eaf6;">
        <h2>Resources</h2>
        <p style="text-align:center; font-size: 2rem;"><?= $resource_count ?></p>
    </div>
</div>

<?php include '../includes/teacher_footer.php'; ?>
