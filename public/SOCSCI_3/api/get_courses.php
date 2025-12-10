<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include '../includes/db.php';

// PDO query
$stmt = $conn->query("SELECT * FROM courses ORDER BY code ASC");
$courses = [];

if ($stmt) {
    while ($course = $stmt->fetch()) {
        $courses[] = [
            'code' => $course['code'],
            'name' => $course['name']
        ];
    }
}

echo json_encode([
    'success' => true,
    'courses' => $courses
]);
?>
