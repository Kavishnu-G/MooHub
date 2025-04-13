<?php
include '../database.php'; // Adjust the path to your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    try {
        $stmt = $conn->prepare("DELETE FROM customers WHERE name = :name");
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Customer not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>