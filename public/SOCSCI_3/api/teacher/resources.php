<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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

// GET - Fetch resources
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $resources_query = "SELECT * FROM resources WHERE teacher_id = $teacher_id ORDER BY created_at DESC";
    $result = $conn->query($resources_query);
    $resources = [];
    
    while ($row = $result->fetch_assoc()) {
        $resources[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'resources' => $resources
    ]);
}

// POST - Create resource
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (empty($subject)) {
        echo json_encode(['success' => false, 'error' => 'Subject is required']);
        exit();
    }
    
    $file_path = null;
    $original_filename = null;
    
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $upload_dir = '../../uploads/resources/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $original_filename = basename($_FILES['file']['name']);
        $file_path = $upload_dir . time() . '_' . $original_filename;
        
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
            echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
            exit();
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO resources (teacher_id, subject, description, file_path, original_filename) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $teacher_id, $subject, $description, $file_path, $original_filename);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Resource created successfully!',
            'resource_id' => $stmt->insert_id
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to create resource']);
    }
    
    $stmt->close();
}

// PUT - Update resource (via POST with _method=PUT)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'PUT') {
    $id = $_POST['resource_id'] ?? null;
    $subject = $_POST['subject'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Resource ID is required']);
        exit();
    }
    
    // Check if new file is uploaded
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        // Get old file to delete
        $old_resource = $conn->query("SELECT file_path FROM resources WHERE id=$id AND teacher_id=$teacher_id")->fetch_assoc();
        if ($old_resource && $old_resource['file_path'] && file_exists($old_resource['file_path'])) {
            unlink($old_resource['file_path']);
        }
        
        $upload_dir = '../../uploads/resources/';
        $original_filename = basename($_FILES['file']['name']);
        $file_path = $upload_dir . time() . '_' . $original_filename;
        
        if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
            $stmt = $conn->prepare("UPDATE resources SET subject=?, description=?, file_path=?, original_filename=? WHERE id=? AND teacher_id=?");
            $stmt->bind_param("ssssii", $subject, $description, $file_path, $original_filename, $id, $teacher_id);
        }
    } else {
        $stmt = $conn->prepare("UPDATE resources SET subject=?, description=? WHERE id=? AND teacher_id=?");
        $stmt->bind_param("ssii", $subject, $description, $id, $teacher_id);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Resource updated successfully!']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update resource']);
    }
    
    $stmt->close();
}

// DELETE
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'DELETE') {
    $id = $_POST['resource_id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Resource ID is required']);
        exit();
    }
    
    // Get file path to delete
    $resource = $conn->query("SELECT file_path FROM resources WHERE id=$id AND teacher_id=$teacher_id")->fetch_assoc();
    if ($resource && $resource['file_path'] && file_exists($resource['file_path'])) {
        unlink($resource['file_path']);
    }
    
    $conn->query("DELETE FROM resources WHERE id=$id AND teacher_id=$teacher_id");
    echo json_encode(['success' => true, 'message' => 'Resource deleted successfully!']);
}

$conn->close();
