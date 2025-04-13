<?php
// Include the database connection
include('../database.php'); // Ensure this path is correct!

// Check if PDO connection is successful
if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get the search query from the request
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare the SQL query to search for customers by name
$sql = "SELECT name FROM customers WHERE name LIKE :searchQuery";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind the parameter using bindValue
$stmt->bindValue(':searchQuery', $searchQuery . '%', PDO::PARAM_STR);

// Execute the query
$stmt->execute();

// Fetch the results
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return the results as JSON
header('Content-Type: application/json');
echo json_encode($customers);

// Close the database connection (optional: PDO doesn't require explicit closing)
$conn = null;
?>
