<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

$resources_query = "
    SELECT r.*, u.first_name, u.last_name 
    FROM resources r 
    JOIN users u ON r.teacher_id = u.id 
    ORDER BY r.created_at DESC
";

$result = $conn->query($resources_query);
$resources = [];

while ($row = $result->fetch_assoc()) {
    $resources[] = $row;
}

echo json_encode([
    'success' => true,
    'resources' => $resources
]);

$conn->close();
