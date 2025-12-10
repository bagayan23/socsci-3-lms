<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// PostgreSQL connection
$host = 'dpg-d4sva8pr0fns73f3k6qg-a.oregon-postgres.render.com';
$db   = 'socsci3_lms';
$user = 'wilms';
$pass = 'gZ6rRJ8ER3H2pktUGd0ZQaCFNg7lcWDa';
$port = '5432'; 

// DSN with SSL mode required (essential for Render.com)
$dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
    $pdo = $conn; // Alias for compatibility
} catch (\PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Helper function to execute queries (for compatibility)
function executeQuery($conn, $sql, $params = []) {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
?>
