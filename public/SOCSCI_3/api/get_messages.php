<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get URL parameters
$error = isset($_GET['error']) ? $_GET['error'] : null;
$success = isset($_GET['success']) ? $_GET['success'] : null;

echo json_encode([
    'error' => $error,
    'success' => $success,
    'date' => date('F d, Y'),
    'year' => date('Y')
]);
