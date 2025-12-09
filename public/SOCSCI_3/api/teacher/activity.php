<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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

$teacher_id = $_SESSION['user_id'];

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

// Check for method override
if ($method === 'POST' && isset($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
}

try {
    switch ($method) {
        case 'GET':
            handleGet($conn, $teacher_id);
            break;
        case 'POST':
            handlePost($conn, $teacher_id);
            break;
        case 'PUT':
            handlePut($conn, $teacher_id);
            break;
        case 'DELETE':
            handleDelete($conn, $teacher_id);
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function handleGet($conn, $teacher_id) {
    // If activity_id is provided, get submissions for that activity
    if (isset($_GET['activity_id'])) {
        getSubmissions($conn, $teacher_id, $_GET['activity_id']);
        return;
    }
    
    // Otherwise, get all activities for this teacher
    $sql = "SELECT a.*, 
            COUNT(DISTINCT s.id) as total_submissions,
            COUNT(DISTINCT CASE WHEN s.grade IS NULL THEN s.id END) as pending_grading
            FROM activities a
            LEFT JOIN submissions s ON a.id = s.activity_id
            WHERE a.teacher_id = ?
            GROUP BY a.id
            ORDER BY a.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    
    echo json_encode(['success' => true, 'activities' => $activities]);
}

function getSubmissions($conn, $teacher_id, $activity_id) {
    // Verify activity belongs to teacher
    $check_sql = "SELECT id FROM activities WHERE id = ? AND teacher_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $activity_id, $teacher_id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Activity not found']);
        return;
    }
    
    // Get submissions with student info
    $sql = "SELECT s.*, 
            u.first_name, u.last_name, u.student_id, u.email,
            a.title as activity_title
            FROM submissions s
            JOIN users u ON s.student_id = u.id
            JOIN activities a ON s.activity_id = a.id
            WHERE s.activity_id = ?
            ORDER BY s.submitted_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $activity_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $submissions = [];
    while ($row = $result->fetch_assoc()) {
        $submissions[] = $row;
    }
    
    echo json_encode(['success' => true, 'submissions' => $submissions]);
}

function handlePost($conn, $teacher_id) {
    // Check if this is a grading request
    if (isset($_POST['submission_id']) && isset($_POST['grade'])) {
        gradeSubmission($conn, $teacher_id, $_POST['submission_id'], $_POST['grade'], $_POST['feedback'] ?? '');
        return;
    }
    
    // Otherwise, create new activity
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type = trim($_POST['type'] ?? 'assignment');
    
    if (empty($title) || empty($description)) {
        echo json_encode(['success' => false, 'error' => 'Title and description are required']);
        return;
    }
    
    $file_path = null;
    $original_filename = null;
    
    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/activities/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $original_filename = basename($_FILES['file']['name']);
        $extension = pathinfo($original_filename, PATHINFO_EXTENSION);
        $new_filename = uniqid() . '_' . time() . '.' . $extension;
        $file_path = $upload_dir . $new_filename;
        
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
            echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
            return;
        }
        
        // Store relative path
        $file_path = '/SOCSCI_3/uploads/activities/' . $new_filename;
    }
    
    $sql = "INSERT INTO activities (teacher_id, title, description, type, file_path, original_filename, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $teacher_id, $title, $description, $type, $file_path, $original_filename);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Activity created successfully', 'activity_id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to create activity']);
    }
}

function gradeSubmission($conn, $teacher_id, $submission_id, $grade, $feedback) {
    // Verify submission belongs to teacher's activity
    $check_sql = "SELECT s.id FROM submissions s
                  JOIN activities a ON s.activity_id = a.id
                  WHERE s.id = ? AND a.teacher_id = ?";
    
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $submission_id, $teacher_id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Submission not found']);
        return;
    }
    
    $sql = "UPDATE submissions SET grade = ?, feedback = ?, graded_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $grade, $feedback, $submission_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Submission graded successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to grade submission']);
    }
}

function handlePut($conn, $teacher_id) {
    $activity_id = $_POST['activity_id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type = trim($_POST['type'] ?? 'assignment');
    
    if (empty($activity_id) || empty($title) || empty($description)) {
        echo json_encode(['success' => false, 'error' => 'Activity ID, title and description are required']);
        return;
    }
    
    // Verify activity belongs to teacher
    $check_sql = "SELECT file_path FROM activities WHERE id = ? AND teacher_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $activity_id, $teacher_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Activity not found']);
        return;
    }
    
    $activity = $result->fetch_assoc();
    $file_path = $activity['file_path'];
    $original_filename = null;
    
    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        // Delete old file if exists
        if (!empty($file_path) && file_exists('../../' . $file_path)) {
            unlink('../../' . $file_path);
        }
        
        $upload_dir = '../../uploads/activities/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $original_filename = basename($_FILES['file']['name']);
        $extension = pathinfo($original_filename, PATHINFO_EXTENSION);
        $new_filename = uniqid() . '_' . time() . '.' . $extension;
        $file_path = $upload_dir . $new_filename;
        
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
            echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
            return;
        }
        
        $file_path = '/SOCSCI_3/uploads/activities/' . $new_filename;
    }
    
    if ($original_filename !== null) {
        $sql = "UPDATE activities SET title = ?, description = ?, type = ?, file_path = ?, original_filename = ? WHERE id = ? AND teacher_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssii", $title, $description, $type, $file_path, $original_filename, $activity_id, $teacher_id);
    } else {
        $sql = "UPDATE activities SET title = ?, description = ?, type = ? WHERE id = ? AND teacher_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $title, $description, $type, $activity_id, $teacher_id);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Activity updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update activity']);
    }
}

function handleDelete($conn, $teacher_id) {
    $activity_id = $_POST['activity_id'] ?? null;
    
    if (empty($activity_id)) {
        echo json_encode(['success' => false, 'error' => 'Activity ID is required']);
        return;
    }
    
    // Get activity file path before deleting
    $check_sql = "SELECT file_path FROM activities WHERE id = ? AND teacher_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $activity_id, $teacher_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Activity not found']);
        return;
    }
    
    $activity = $result->fetch_assoc();
    
    // Delete activity (cascade will delete submissions)
    $sql = "DELETE FROM activities WHERE id = ? AND teacher_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $activity_id, $teacher_id);
    
    if ($stmt->execute()) {
        // Delete file if exists
        if (!empty($activity['file_path']) && file_exists('../../' . $activity['file_path'])) {
            unlink('../../' . $activity['file_path']);
        }
        
        echo json_encode(['success' => true, 'message' => 'Activity deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete activity']);
    }
}
?>
