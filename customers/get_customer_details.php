<?php
include '../database.php';

$customerName = $_GET['name'];

$stmt = $conn->prepare("SELECT * FROM orders WHERE user_name = :name");
$stmt->bindParam(':name', $customerName);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM payments WHERE customer_name = :name");
$stmt->bindParam(':name', $customerName);
$stmt->execute();
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalLiters = 0;
$paidLiters = 0;
$dueLiters = 0;

// Calculate liters from orders
foreach ($orders as $order) {
    $quantity = (float)$order['milk_quantity'];
    $totalLiters += $quantity;
    if ($order['payment_status'] === 'Paid') {
        $paidLiters += $quantity;
    } else {
        $dueLiters += $quantity;
    }
}

echo json_encode([
    'status' => 'success',
    'total_liters' => $totalLiters,
    'paid_liters' => $paidLiters,
    'due_liters' => $dueLiters,
    'orders' => $orders,
    'payments' => $payments
]);
?>