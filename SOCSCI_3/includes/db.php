<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "sql300.infinityfree.com";
$username = "if0_40630767";
$password = "BXdaEd010MtfF";
$dbname = "if0_40630767_socsci3_lms";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");
?>
