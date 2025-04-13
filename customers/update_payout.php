<?php
include '../database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = $_POST['customer_name'];
    $dueLiters = $_POST['due_liters'];
    $rate = $_POST['rate'];
    $totalAmount = $_POST['total_amount'];
    $amountPaid = $_POST['amount_paid'];
    $dueAmount = $_POST['due_amount'];

    try {
        // Insert into payments table
        $stmt = $conn->prepare("INSERT INTO payments (customer_name, total_liters, rate, total_amount, amount_paid, due_amount) 
                                VALUES (:customer_name, :total_liters, :rate, :total_amount, :amount_paid, :due_amount)");
        $stmt->bindParam(':customer_name', $customerName);
        $stmt->bindParam(':total_liters', $dueLiters);
        $stmt->bindParam(':rate', $rate);
        $stmt->bindParam(':total_amount', $totalAmount);
        $stmt->bindParam(':amount_paid', $amountPaid);
        $stmt->bindParam(':due_amount', $dueAmount);
        $stmt->execute();

        // Update orders: Mark as 'Paid' up to the equivalent liters paid
        $paidLiters = $amountPaid / $rate;
        $remainingLiters = $paidLiters;

        $stmt = $conn->prepare("SELECT order_id, milk_quantity FROM orders 
                                WHERE user_name = :customer_name AND payment_status = 'Due' 
                                ORDER BY order_time ASC");
        $stmt->bindParam(':customer_name', $customerName);
        $stmt->execute();
        $dueOrders = $stmt->fetchAll();

        foreach ($dueOrders as $order) {
            if ($remainingLiters <= 0) break;
            $orderLiters = (float)$order['milk_quantity'];
            if ($remainingLiters >= $orderLiters) {
                // Mark entire order as Paid
                $stmt = $conn->prepare("UPDATE orders SET payment_status = 'Paid' WHERE order_id = :order_id");
                $stmt->bindParam(':order_id', $order['order_id']);
                $stmt->execute();
                $remainingLiters -= $orderLiters;
            } else {
                // Split order: reduce due liters and create a new paid order
                $newDueLiters = $orderLiters - $remainingLiters;
                $stmt = $conn->prepare("UPDATE orders SET milk_quantity = :new_due_liters WHERE order_id = :order_id");
                $stmt->bindParam(':new_due_liters', $newDueLiters);
                $stmt->bindParam(':order_id', $order['order_id']);
                $stmt->execute();

                $stmt = $conn->prepare("INSERT INTO orders (order_id, user_name, milk_quantity, order_time, payment_status) 
                                        VALUES (:order_id, :user_name, :milk_quantity, NOW(), 'Paid')");
                $newOrderId = $order['order_id'] . '-P';
                $stmt->bindParam(':order_id', $newOrderId);
                $stmt->bindParam(':user_name', $customerName);
                $stmt->bindParam(':milk_quantity', $remainingLiters);
                $stmt->execute();
                $remainingLiters = 0;
            }
        }

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>