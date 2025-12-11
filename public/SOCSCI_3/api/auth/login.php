<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable HTML error output
ini_set('log_errors', 1); // Log errors instead

// Set JSON header first to ensure JSON output
header('Content-Type: application/json');

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Global error handler to return JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $errstr]);
    exit();
});

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

session_start();

try {
    include '../../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Email and password are required']);
    exit();
}

// PDO prepared statement
$stmt = $conn->prepare("SELECT id, role, password, first_name, last_name FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if ($user) {
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['initials'] = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));

        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'role' => $user['role'],
                'name' => $_SESSION['name'],
                'initials' => $_SESSION['initials']
            ],
            'redirect' => $user['role'] === 'teacher' ? '/SOCSCI_3/teacher/dashboard.html' : '/SOCSCI_3/student/dashboard.html'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid password']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'User not found']);
}

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Login failed: ' . $e->getMessage()]);
}
?>