<?php
$host = "localhost";
$dbname = "dairy_farm_db";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST['name'];
        $address = $_POST['address'];
        $phone = $_POST['phone'];
        $whatsapp = $_POST['whatsapp'];

        // Insert data
        $stmt = $conn->prepare("INSERT INTO customers (name, address, phone, whatsapp) VALUES (:name, :address, :phone, :whatsapp)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':whatsapp', $whatsapp);
        $stmt->execute();

        echo json_encode(["status" => "success"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
