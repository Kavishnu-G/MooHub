<?php
require 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  // Check if a user with this name already exists
  $stmt = $conn->prepare("SELECT * FROM users WHERE name = ?");
  $stmt->execute([$name]);
  if ($stmt->rowCount() > 0) {
    echo "User with this name already exists!";
    exit();
  }

  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
  $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
  if ($stmt->execute([$name, $email, $hashedPassword])) {
    header("Location: index.php");
    exit();
  } else {
    echo "Error during registration.";
  }
}
?>
