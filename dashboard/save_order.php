<?php
session_start();
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

require '../database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and trim POST data
    $order_id = trim($_POST['order_id']);
    $user_name = trim($_POST['user_name']);
    $milk_quantity = trim($_POST['milk_quantity']);
    $timestamp = trim($_POST['timestamp']);
    $payment_status = trim($_POST['payment_status']);
    $user_id = $_SESSION['user']['id'];

    // Initialize an array for validation errors
    $errors = [];

    // Validate inputs
    if (empty($order_id)) {
        $errors[] = "Order ID is required";
    }
    if (empty($user_name)) {
        $errors[] = "User name is required";
    }
    if (!is_numeric($milk_quantity) || $milk_quantity <= 0) {
        $errors[] = "Milk quantity must be a positive number";
    }
    if (!in_array($payment_status, ['Paid', 'Due'])) {
        $errors[] = "Invalid payment status";
    }

    // Validate the timestamp format (YYYY-MM-DD HH:MM:SS)
    if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $timestamp)) {
        $errors[] = "Invalid timestamp format: $timestamp";
    } else {
        $order_time = $timestamp; // Use directly in the database
    }

    // If there are validation errors, return them
    if (!empty($errors)) {
        echo json_encode(["status" => "error", "message" => implode(", ", $errors)]);
        exit;
    }

    // Prepare and execute the insert query
    $stmt = $conn->prepare("INSERT INTO orders (order_id, user_name, milk_quantity, order_time, payment_status, user_id) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$order_id, $user_name, $milk_quantity, $order_time, $payment_status, $user_id])) {
        echo json_encode(["status" => "success", "message" => "Order saved"]);
    } else {
        $errorInfo = $stmt->errorInfo();
        if ($errorInfo[1] == 1062) { // MySQL error code for duplicate entry
            echo json_encode(["status" => "error", "message" => "Order ID already exists"]);
        } else {
            error_log("Database error: " . $errorInfo[2]); // Log error for debugging
            echo json_encode(["status" => "error", "message" => "Order save failed"]);
        }
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>