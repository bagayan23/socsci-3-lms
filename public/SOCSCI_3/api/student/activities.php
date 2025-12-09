<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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

// GET - Fetch activities
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $activities_query = "
        SELECT a.*, u.first_name, u.last_name, 
               s.id as submission_id, s.submitted_at, s.text_submission, s.file_path as submission_file,
               g.grade, g.feedback, g.graded_at
        FROM activities a 
        JOIN users u ON a.teacher_id = u.id 
        LEFT JOIN submissions s ON a.id = s.activity_id AND s.student_id = $student_id
        LEFT JOIN grades g ON s.id = g.submission_id
        ORDER BY a.created_at DESC
    ";
    
    $result = $conn->query($activities_query);
    $activities = [];
    
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'activities' => $activities
    ]);
}

// POST - Submit activity
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $activity_id = $_POST['activity_id'] ?? null;
    $text_submission = $_POST['text_submission'] ?? '';
    
    if (!$activity_id) {
        echo json_encode(['success' => false, 'error' => 'Activity ID is required']);
        exit();
    }
    
    $file_path = null;
    $original_filename = null;
    
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $upload_dir = '../../uploads/submissions/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $original_filename = basename($_FILES['file']['name']);
        $file_path = $upload_dir . time() . '_' . $student_id . '_' . $original_filename;
        
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
            echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
            exit();
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO submissions (activity_id, student_id, file_path, text_submission, original_filename) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $activity_id, $student_id, $file_path, $text_submission, $original_filename);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Submitted successfully!',
            'submission_id' => $stmt->insert_id
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error submitting work: ' . $stmt->error]);
    }
    
    $stmt->close();
}

$conn->close();
