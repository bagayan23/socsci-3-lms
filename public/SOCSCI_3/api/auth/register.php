<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

session_start();
include '../../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// Extract fields
$role = $input['role'] ?? '';
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';
$first_name = $input['first_name'] ?? '';
$last_name = $input['last_name'] ?? '';
$middle_name = $input['middle_name'] ?? '';
$extension_name = $input['extension_name'] ?? '';
$birthday = $input['birthday'] ?? '';
$contact_number = $input['contact_number'] ?? '';
$region = $input['region'] ?? '';
$province = $input['province'] ?? '';
$city = $input['city'] ?? '';
$barangay = $input['barangay'] ?? '';
$street = $input['street'] ?? '';
$student_id = !empty($input['student_id']) ? $input['student_id'] : null;
$year = !empty($input['year']) ? $input['year'] : null;
$program = !empty($input['program']) ? $input['program'] : null;
$section = !empty($input['section']) ? $input['section'] : null;

// Validation
if (empty($role) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
    echo json_encode(['success' => false, 'error' => 'Required fields are missing']);
    exit();
}

// Check if email exists
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Email already registered']);
    exit();
}

// Hash password
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Insert user
$stmt = $conn->prepare("INSERT INTO users (role, email, password, first_name, last_name, middle_name, extension_name, birthday, contact_number, region, province, city, barangay, street, student_school_id, year_level, program, section) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssssssssssssss", $role, $email, $password_hash, $first_name, $last_name, $middle_name, $extension_name, $birthday, $contact_number, $region, $province, $city, $barangay, $street, $student_id, $year, $program, $section);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! Please login'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Registration failed: ' . $stmt->error
    ]);
}

$stmt->close();
$check->close();
$conn->close();
