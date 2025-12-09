<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../../includes/db.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    // If student_id is provided, get detailed info for that student
    if (isset($_GET['student_id'])) {
        getStudentDetails($conn, $_GET['student_id']);
    } else {
        // Otherwise, get list of all students with stats
        getStudentsList($conn);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function getStudentsList($conn) {
    $sql = "SELECT 
            u.id, u.student_id, u.first_name, u.last_name, u.email, u.program, u.year_level,
            COUNT(DISTINCT s.id) as total_submissions,
            COUNT(DISTINCT CASE WHEN s.grade IS NOT NULL THEN s.id END) as graded_submissions,
            AVG(CASE WHEN s.grade REGEXP '^[0-9]+$' THEN CAST(s.grade AS DECIMAL(5,2)) END) as avg_grade
            FROM users u
            LEFT JOIN submissions s ON u.id = s.student_id
            WHERE u.role = 'student'
            GROUP BY u.id
            ORDER BY u.last_name, u.first_name";
    
    $result = $conn->query($sql);
    
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    
    echo json_encode(['success' => true, 'students' => $students]);
}

function getStudentDetails($conn, $student_id) {
    // Get student basic info
    $sql = "SELECT id, student_id, first_name, last_name, email, program, year_level, created_at 
            FROM users 
            WHERE id = ? AND role = 'student'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Student not found']);
        return;
    }
    
    $student = $result->fetch_assoc();
    
    // Get submissions with activity details
    $sql = "SELECT s.*, a.title as activity_title, a.type as activity_type,
            u.first_name as teacher_first_name, u.last_name as teacher_last_name
            FROM submissions s
            JOIN activities a ON s.activity_id = a.id
            JOIN users u ON a.teacher_id = u.id
            WHERE s.student_id = ?
            ORDER BY s.submitted_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $submissions = [];
    while ($row = $result->fetch_assoc()) {
        $submissions[] = $row;
    }
    
    // Calculate statistics
    $total_submissions = count($submissions);
    $graded = array_filter($submissions, function($s) { return $s['grade'] !== null; });
    $graded_count = count($graded);
    $pending_count = $total_submissions - $graded_count;
    
    $numeric_grades = array_filter($graded, function($s) { 
        return is_numeric($s['grade']); 
    });
    $avg_grade = count($numeric_grades) > 0 
        ? array_sum(array_map(function($s) { return floatval($s['grade']); }, $numeric_grades)) / count($numeric_grades)
        : null;
    
    echo json_encode([
        'success' => true,
        'student' => $student,
        'submissions' => $submissions,
        'stats' => [
            'total_submissions' => $total_submissions,
            'graded_count' => $graded_count,
            'pending_count' => $pending_count,
            'avg_grade' => $avg_grade ? round($avg_grade, 2) : null
        ]
    ]);
}
?>
