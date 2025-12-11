<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include '../includes/db.php';

$courses_query = $conn->query("SELECT * FROM courses ORDER BY code ASC");

$courses = [];
if ($courses_query && $courses_query->num_rows > 0) {
    while ($course = $courses_query->fetch_assoc()) {
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
