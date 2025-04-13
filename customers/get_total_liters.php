<?php
include '../database.php';

if (isset($_GET['customer_name']) && isset($_GET['from']) && isset($_GET['to'])) {
    $customerName = $_GET['customer_name'];
    $fromDate = $_GET['from'];
    $toDate = $_GET['to'];

    try {
        $stmt = $conn->prepare("SELECT SUM(milk_quantity) as total_liters 
                                FROM orders 
                                WHERE user_name = :customer_name 
                                AND order_time BETWEEN :from_date AND :to_date");
        $stmt->bindParam(':customer_name', $customerName);
        $stmt->bindParam(':from_date', $fromDate);
        $stmt->bindParam(':to_date', $toDate);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'total_liters' => $result['total_liters'] ?? 0]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>