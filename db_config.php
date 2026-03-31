<?php
// db_config.php - Database Configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'smart_lms';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to handle multilingual content (isiZulu, isiXhosa, Afrikaans, etc.)
$conn->set_charset("utf8mb4");
?>