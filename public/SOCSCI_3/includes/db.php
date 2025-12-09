<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1); // Log errors instead

$servername = "sql300.infinityfree.com";
$username = "if0_40630767";
$password = "BXdaEd010MtfF";
$dbname = "if0_40630767_socsci3_lms";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Return JSON error instead of die()
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");
?>
