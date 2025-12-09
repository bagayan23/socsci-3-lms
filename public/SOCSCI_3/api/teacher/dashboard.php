<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

include '../../includes/db.php';

$teacher_id = $_SESSION['user_id'];

// Get counts
$student_count_res = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='student'");
$student_count = $student_count_res->fetch_assoc()['count'];

$activity_count_res = $conn->query("SELECT COUNT(*) as count FROM activities WHERE teacher_id=$teacher_id");
$activity_count = $activity_count_res->fetch_assoc()['count'];

$resource_count_res = $conn->query("SELECT COUNT(*) as count FROM resources WHERE teacher_id=$teacher_id");
$resource_count = $resource_count_res->fetch_assoc()['count'];

// Get submission statistics
$total_submissions_res = $conn->query("
    SELECT COUNT(*) as count FROM submissions s
    JOIN activities a ON s.activity_id = a.id
    WHERE a.teacher_id = $teacher_id
");
$total_submissions = $total_submissions_res->fetch_assoc()['count'];

// Get pending grading count
$pending_grading_res = $conn->query("
    SELECT COUNT(*) as count FROM submissions s
    JOIN activities a ON s.activity_id = a.id
    LEFT JOIN grades g ON s.id = g.submission_id
    WHERE a.teacher_id = $teacher_id AND g.id IS NULL
");
$pending_grading = $pending_grading_res->fetch_assoc()['count'];

// Get graded count
$graded_count = $total_submissions - $pending_grading;

// Get average grade
$avg_grade_res = $conn->query("
    SELECT AVG(g.grade) as avg_grade FROM grades g
    JOIN submissions s ON g.submission_id = s.id
    JOIN activities a ON s.activity_id = a.id
    WHERE a.teacher_id = $teacher_id
");
$avg_grade = $avg_grade_res->fetch_assoc()['avg_grade'] ?? 0;

// Get activity type distribution
$activity_types_result = $conn->query("
    SELECT type, COUNT(*) as count FROM activities 
    WHERE teacher_id = $teacher_id
    GROUP BY type
");
$activity_types = [];
while ($row = $activity_types_result->fetch_assoc()) {
    $activity_types[] = $row;
}

// Get recent submissions
$recent_submissions_query = "
    SELECT s.id, s.submitted_at, a.title as activity_title,
           u.first_name, u.last_name,
           CASE WHEN g.id IS NOT NULL THEN 'graded' ELSE 'pending' END as status
    FROM submissions s
    JOIN activities a ON s.activity_id = a.id
    JOIN users u ON s.student_id = u.id
    LEFT JOIN grades g ON s.id = g.submission_id
    WHERE a.teacher_id = $teacher_id
    ORDER BY s.submitted_at DESC
    LIMIT 10
";
$recent_submissions_result = $conn->query($recent_submissions_query);
$recent_submissions = [];
while ($row = $recent_submissions_result->fetch_assoc()) {
    $recent_submissions[] = $row;
}

echo json_encode([
    'success' => true,
    'stats' => [
        'student_count' => $student_count,
        'activity_count' => $activity_count,
        'resource_count' => $resource_count,
        'total_submissions' => $total_submissions,
        'pending_grading' => $pending_grading,
        'graded_count' => $graded_count,
        'avg_grade' => round($avg_grade, 2)
    ],
    'activity_types' => $activity_types,
    'recent_submissions' => $recent_submissions
]);

$conn->close();
