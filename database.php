<?php
$host = "localhost";
$dbname = "dairy_farm_db";  // Ensure this matches your actual database name
$username = "root";
$password = "";

try {
    // Create a new PDO instance with better error handling
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Enable exceptions for errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Fetch data as an associative array
        PDO::ATTR_EMULATE_PREPARES => false  // Disable emulation to prevent SQL injection
    ]);
} catch (PDOException $e) {
    // Log error (you can modify this to log errors in a file instead of displaying them)
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
?>
