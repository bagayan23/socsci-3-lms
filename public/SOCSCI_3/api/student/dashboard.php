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

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

include '../../includes/db.php';

$student_id = $_SESSION['user_id'];

// Get total activities count
$total_activities_res = $conn->query("SELECT COUNT(*) as count FROM activities");
$total_activities = $total_activities_res->fetch_assoc()['count'];

// Get submitted count
$submitted_count_res = $conn->query("
    SELECT COUNT(*) as count FROM submissions 
    WHERE student_id = $student_id
");
$submitted_count = $submitted_count_res->fetch_assoc()['count'];

// Get pending activities count
$pending_count = $total_activities - $submitted_count;

// Get graded submissions count
$graded_count_res = $conn->query("
    SELECT COUNT(*) as count FROM submissions s
    JOIN grades g ON s.id = g.submission_id
    WHERE s.student_id = $student_id
");
$graded_count = $graded_count_res->fetch_assoc()['count'];

// Get average grade
$avg_grade_res = $conn->query("
    SELECT AVG(g.grade) as avg_grade FROM grades g
    JOIN submissions s ON g.submission_id = s.id
    WHERE s.student_id = $student_id
");
$avg_grade = $avg_grade_res->fetch_assoc()['avg_grade'] ?? 0;

// Get total resources
$resources_count_res = $conn->query("SELECT COUNT(*) as count FROM resources");
$resources_count = $resources_count_res->fetch_assoc()['count'];

// Calculate completion rate
$completion_rate = $total_activities > 0 ? round(($submitted_count / $total_activities) * 100, 1) : 0;

// Get recent grades
$recent_grades_query = "
    SELECT a.title, a.total_score, g.grade, g.feedback, g.graded_at
    FROM grades g
    JOIN submissions s ON g.submission_id = s.id
    JOIN activities a ON s.activity_id = a.id
    WHERE s.student_id = $student_id
    ORDER BY g.graded_at DESC
    LIMIT 5
";
$recent_grades_result = $conn->query($recent_grades_query);
$recent_grades = [];
while ($row = $recent_grades_result->fetch_assoc()) {
    $recent_grades[] = $row;
}

// Get upcoming activities
$upcoming_activities_query = "
    SELECT a.id, a.title, a.description, a.due_date, a.type,
           CASE WHEN s.id IS NOT NULL THEN 'submitted' ELSE 'pending' END as status
    FROM activities a
    LEFT JOIN submissions s ON a.id = s.activity_id AND s.student_id = $student_id
    WHERE a.due_date >= CURDATE()
    ORDER BY a.due_date ASC
    LIMIT 5
";
$upcoming_activities_result = $conn->query($upcoming_activities_query);
$upcoming_activities = [];
while ($row = $upcoming_activities_result->fetch_assoc()) {
    $upcoming_activities[] = $row;
}

echo json_encode([
    'success' => true,
    'stats' => [
        'total_activities' => $total_activities,
        'submitted_count' => $submitted_count,
        'pending_count' => $pending_count,
        'graded_count' => $graded_count,
        'avg_grade' => round($avg_grade, 2),
        'resources_count' => $resources_count,
        'completion_rate' => $completion_rate
    ],
    'recent_grades' => $recent_grades,
    'upcoming_activities' => $upcoming_activities
]);

$conn->close();
