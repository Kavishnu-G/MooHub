<?php
require "database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST["token"];
    $newPassword = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // Update password
    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?");
    if ($stmt->execute([$newPassword, $token])) {
        echo "Password updated!";
        header("Location: index.php");
    } else {
        echo "Error updating password!";
    }
}
?>
