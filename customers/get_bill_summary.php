<?php
// get_bill_summary.php - Generate and download monthly bill summary as CSV
require '../database.php';

$customer_name = $_GET['name'];
$month = $_GET['month'];

if (empty($customer_name) || empty($month)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit();
}

$start_date = "$month-01 00:00:00";
$end_date = date('Y-m-t 23:59:59', strtotime($start_date));

$stmt = $conn->prepare("SELECT * FROM orders WHERE user_name = ? AND order_time >= ? AND order_time <= ? ORDER BY order_time");
$stmt->execute([$customer_name, $start_date, $end_date]);
$orders = $stmt->fetchAll();

$rate = 50;
$total_milk = 0;
$total_amount = 0;
$amount_paid = 0;

foreach ($orders as $order) {
    $quantity = $order['milk_quantity'];
    $amount = $quantity * $rate;
    $total_milk += $quantity;
    $total_amount += $amount;
    if ($order['payment_status'] == 'Paid') {
        $amount_paid += $amount;
    }
}

$due_amount = $total_amount - $amount_paid;

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="bill_summary_' . $month . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Bill Summary for ' . $customer_name . ' - ' . $month]);
fputcsv($output, ['Order ID', 'Date', 'Quantity (L)', 'Amount (INR)', 'Payment Status']);
foreach ($orders as $order) {
    fputcsv($output, [
        $order['order_id'],
        $order['order_time'],
        $order['milk_quantity'],
        $order['milk_quantity'] * $rate,
        $order['payment_status']
    ]);
}
fputcsv($output, ['', '', 'Total Milk:', $total_milk, '']);
fputcsv($output, ['', '', 'Total Amount:', $total_amount, '']);
fputcsv($output, ['', '', 'Amount Paid:', $amount_paid, '']);
fputcsv($output, ['', '', 'Due Amount:', $due_amount, '']);

fclose($output);
exit();
?>