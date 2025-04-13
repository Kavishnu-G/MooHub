<?php
// update_customer.php - Update customer details and linked orders
require '../database.php';

$original_name = $_POST['name'];
$new_name = $_POST['new_name'];
$address = $_POST['address'];
$phone = $_POST['phone'];

if (empty($original_name) || empty($new_name) || empty($address) || empty($phone)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit();
}

try {
    // Update customer details
    $stmt = $conn->prepare("UPDATE customers SET name = ?, address = ?, phone = ? WHERE name = ?");
    $stmt->execute([$new_name, $address, $phone, $original_name]);

    // If name changed, update orders
    if ($new_name !== $original_name) {
        $stmt = $conn->prepare("UPDATE orders SET user_name = ? WHERE user_name = ?");
        $stmt->execute([$new_name, $original_name]);
    }

    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>