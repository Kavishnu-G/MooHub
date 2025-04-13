<?php
session_start();

// Ensure the user is authenticated
if (!isset($_SESSION['user'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

require '../database.php';

try {
    // Attempt to decode JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    // Check if order_id exists in JSON input; otherwise, try POST
    if (!empty($data) && isset($data['order_id'])) {
        $order_id = $data['order_id'];
    } elseif (isset($_POST['order_id'])) {
        $order_id = $_POST['order_id'];
    } else {
        echo json_encode(["status" => "error", "message" => "Order ID not provided"]);
        exit();
    }

    // Prepare and execute the delete query
    $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user']['id']]);

    // Check if deletion was successful
    if ($stmt->rowCount() > 0) {
        echo json_encode(["status" => "success", "message" => "Order deleted"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Order not found or already deleted"]);
    }
} catch (PDOException $e) {
    // Output the error message (for debugging purposes)
    echo json_encode(["status" => "error", "message" => "PDO Error: " . $e->getMessage()]);
}
?>
