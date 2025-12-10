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

try {
    // Get total activities count
    $stmt = $conn->query("SELECT COUNT(*) as count FROM activities");
    $total_activities = $stmt->fetch()['count'];

    // Get submitted count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM submissions WHERE student_id = :student_id");
    $stmt->execute(['student_id' => $student_id]);
    $submitted_count = $stmt->fetch()['count'];

    // Get pending activities count
    $pending_count = $total_activities - $submitted_count;

    // Get graded submissions count (grade field is now in submissions table)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count FROM submissions 
        WHERE student_id = :student_id AND grade IS NOT NULL
    ");
    $stmt->execute(['student_id' => $student_id]);
    $graded_count = $stmt->fetch()['count'];

    // Get average grade (numeric grades only)
    $stmt = $conn->prepare("
        SELECT AVG(CAST(grade AS NUMERIC)) as avg_grade 
        FROM submissions 
        WHERE student_id = :student_id AND grade ~ '^[0-9]+\.?[0-9]*$'
    ");
    $stmt->execute(['student_id' => $student_id]);
    $avg_grade = $stmt->fetch()['avg_grade'] ?? 0;

    // Get total resources
    $stmt = $conn->query("SELECT COUNT(*) as count FROM resources");
    $resources_count = $stmt->fetch()['count'];

    // Calculate completion rate
    $completion_rate = $total_activities > 0 ? round(($submitted_count / $total_activities) * 100, 1) : 0;

    // Get recent grades
    $stmt = $conn->prepare("
        SELECT a.title, a.total_score, s.grade, s.feedback, s.graded_at
        FROM submissions s
        JOIN activities a ON s.activity_id = a.id
        WHERE s.student_id = :student_id AND s.grade IS NOT NULL
        ORDER BY s.graded_at DESC
        LIMIT 5
    ");
    $stmt->execute(['student_id' => $student_id]);
    $recent_grades = $stmt->fetchAll();

    // Get upcoming activities (without due_date for now, showing all pending)
    $stmt = $conn->prepare("
        SELECT a.id, a.title, a.description, a.type, a.created_at,
               CASE WHEN s.id IS NOT NULL THEN 'submitted' ELSE 'pending' END as status
        FROM activities a
        LEFT JOIN submissions s ON a.id = s.activity_id AND s.student_id = :student_id
        WHERE s.id IS NULL
        ORDER BY a.created_at DESC
        LIMIT 5
    ");
    $stmt->execute(['student_id' => $student_id]);
    $upcoming_activities = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_activities' => (int)$total_activities,
            'submitted_count' => (int)$submitted_count,
            'pending_count' => (int)$pending_count,
            'graded_count' => (int)$graded_count,
            'avg_grade' => round($avg_grade, 2),
            'resources_count' => (int)$resources_count,
            'completion_rate' => $completion_rate
        ],
        'recent_grades' => $recent_grades,
        'upcoming_activities' => $upcoming_activities
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
