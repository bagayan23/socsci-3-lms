<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// $servername = "sql300.infinityfree.com";
// $username = "if0_40630767";
// $password = "BXdaEd010MtfF";
// $dbname = "if0_40630767_socsci3_lms";

// // Create connection
// $conn = new mysqli($servername, $username, $password, $dbname);

// // Check connection
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }

// // Set charset to utf8mb4
// $conn->set_charset("utf8mb4");

// Credentials extracted from your connection string
$host = 'dpg-d4sva8pr0fns73f3k6qg-a.oregon-postgres.render.com';
$db   = 'socsci3_lms';
$user = 'wilms';
$pass = 'gZ6rRJ8ER3H2pktUGd0ZQaCFNg7lcWDa';
$port = "5432"; 

// DSN with SSL mode required (essential for Render.com)
$dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // echo "Connected successfully!"; 
} catch (\PDOException $e) {
    // If the connection fails, this will print the exact error
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
