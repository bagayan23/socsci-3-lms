<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // PostgreSQL connection
    $host = 'dpg-d4sva8pr0fns73f3k6qg-a.oregon-postgres.render.com';
    $db   = 'socsci3_lms';
    $user = 'wilms';
    $pass = 'gZ6rRJ8ER3H2pktUGd0ZQaCFNg7lcWDa';
    $port = '5432';

    $dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";

    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful!',
        'server_info' => $conn->getAttribute(PDO::ATTR_SERVER_VERSION)
    ]);

    // Test if users table exists
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connected and users table exists',
        'user_count' => $result['count'],
        'server_version' => $conn->getAttribute(PDO::ATTR_SERVER_VERSION)
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
}
?>
