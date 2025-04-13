<?php
$message = "";
$error = "";
$mailto_link = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_email = trim($_POST["email"]);
    
    if (empty($user_email)) {
        $error = "Please enter your email.";
    } else {
        // Define your admin email (recipient)
        $admin_email = "codexs003@gmail.com"; // Replace with your email
        
        // Prepare email subject and body
        $subject = "Password Reset Request";
        $body = "Dear Admin,\n\nI forgot my password. Please help me reset it.\nMy email is: $user_email\n\nThank you.";
        
        // Create a mailto link with URL encoded subject and body
        $mailto_link = "mailto:$admin_email?subject=" . urlencode($subject) . "&body=" . urlencode($body);
        
        $message = "Click the button below to open your email client and send the password reset request.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reset Password</title>
  <link rel="stylesheet" href="styles.css" />
  <link rel="icon" type="image/png" href="../assets/logo.png">
</head>
<body>
  <div class="container">
    <h2>Reset Password</h2>
    <?php
      if (!empty($error)) {
          echo "<p class='error'>$error</p>";
      }
      if (!empty($message) && $mailto_link) {
          echo "<p class='success'>$message</p>";
          echo "<a href='$mailto_link' class='btn'>Send Email</a>";
      } else {
    ?>
    <form action="reset_password.php" method="POST">
      <label>Email</label>
      <input type="email" name="email" placeholder="Enter your email" required />
      <button type="submit" class="btn">Proceed</button>
    </form>
    <a href="index.php">Back to Login</a>
    <?php } ?>
  </div>
</body>
</html>

