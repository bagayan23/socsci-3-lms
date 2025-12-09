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

$method = $_SERVER['REQUEST_METHOD'];

// Check for method override
if ($method === 'POST' && isset($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
}

try {
    switch ($method) {
        case 'GET':
            handleGet($conn);
            break;
        case 'POST':
            handlePost($conn);
            break;
        case 'PUT':
            handlePut($conn);
            break;
        case 'DELETE':
            handleDelete($conn);
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function handleGet($conn) {
    $sql = "SELECT c.*, COUNT(DISTINCT u.id) as student_count
            FROM courses c
            LEFT JOIN users u ON c.name = u.program AND u.role = 'student'
            GROUP BY c.id
            ORDER BY c.name";
    
    $result = $conn->query($sql);
    
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    
    echo json_encode(['success' => true, 'courses' => $courses]);
}

function handlePost($conn) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($name)) {
        echo json_encode(['success' => false, 'error' => 'Course name is required']);
        return;
    }
    
    // Check if course already exists
    $check_sql = "SELECT id FROM courses WHERE name = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $name);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Course already exists']);
        return;
    }
    
    $sql = "INSERT INTO courses (name, description, created_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $name, $description);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Course added successfully', 'course_id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add course']);
    }
}

function handlePut($conn) {
    $course_id = $_POST['course_id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($course_id) || empty($name)) {
        echo json_encode(['success' => false, 'error' => 'Course ID and name are required']);
        return;
    }
    
    // Check if another course with this name exists
    $check_sql = "SELECT id FROM courses WHERE name = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $name, $course_id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Another course with this name already exists']);
        return;
    }
    
    $sql = "UPDATE courses SET name = ?, description = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $name, $description, $course_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Course updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update course']);
    }
}

function handleDelete($conn) {
    $course_id = $_POST['course_id'] ?? null;
    
    if (empty($course_id)) {
        echo json_encode(['success' => false, 'error' => 'Course ID is required']);
        return;
    }
    
    $sql = "DELETE FROM courses WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Course deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete course']);
    }
}
?>
