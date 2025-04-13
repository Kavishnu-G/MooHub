<?php
session_start();
require 'database.php';

// Redirect if user is already logged in
if (isset($_SESSION['user'])) {
    header("Location: dashboard/dashboard.php");
    exit();
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $name = trim($_POST['name']);
    $password = $_POST['password'];
    
    // Look up the user by name
    $stmt = $conn->prepare("SELECT * FROM users WHERE name = ?");
    $stmt->execute([$name]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        header("Location: dashboard/dashboard.php");
        exit();
    } else {
        $login_error = "Invalid name or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="./assets/logo.png">
  <title>Shiva Dairy Farm - Login</title>
  <link rel="stylesheet" href="styles.css" />
  <style>
    .error-message {
      color: #e74c3c;
      text-align: center;
      margin-bottom: 15px;
      font-size: 14px;
    }
    .password-field {
      position: relative;
      display: flex;
      align-items: center;
    }
    .toggle-password {
      position: absolute;
      right: 10px;
      cursor: pointer;
      font-size: 18px;
    }
  </style>
</head>
<body>
  <div class="container">
    <img src="assets/logo.png" alt="Logo" class="logo" />
    <h2 class="title">Shiva Dairy Farm</h2>
    <p class="subtitle">Welcome back!</p>

    <div class="form-box">
      <div class="tabs">
        <button id="loginTab" class="active" onclick="showLogin()">Login</button>
        <button id="signupTab" onclick="showSignup()">Sign Up</button>
      </div>

      <!-- LOGIN FORM -->
      <form id="loginForm" action="" method="POST">
        <input type="hidden" name="login" value="1">
        <?php if (isset($login_error)): ?>
          <div class="error-message"><?php echo htmlspecialchars($login_error); ?></div>
        <?php endif; ?>
        <label>Name</label>
        <input type="text" name="name" placeholder="Enter your name" required />

        <label>Password</label>
        <div class="password-field">
          <input type="password" name="password" placeholder="Enter your password" required />
          <span class="toggle-password">👁</span>
        </div>

        <div class="options">
          <label><input type="checkbox" name="remember" /> Remember me</label>
          <a href="reset_password.php" class="forgot">Forgot Password?</a>
        </div>

        <button type="submit" class="btn">Login</button>
      </form>

      <!-- SIGNUP FORM -->
      <form id="signupForm" action="register.php" method="POST" style="display: none;">
        <label>Full Name</label>
        <input type="text" name="name" placeholder="Enter your full name" required />

        <label>Email</label>
        <input type="email" name="email" placeholder="Enter your email" required />

        <label>Password</label>
        <div class="password-field">
          <input type="password" name="password" placeholder="Enter your password" required />
          <span class="toggle-password">👁</span>
        </div>

        <button type="submit" class="btn">Sign Up</button>
      </form>
    </div>

    <footer>
      <div class="social-links">
        <a href="#"><img src="assets/github.png" alt="GitHub" /></a>
        <a href="#"><img src="assets/discord.png" alt="Discord" /></a>
      </div>
      <div class="legal">
        <a href="#">Privacy Policy</a> | <a href="#">Terms & Conditions</a>
      </div>
      <p>© 2025 CodeXS. All Rights Reserved.</p>
    </footer>
  </div>

  <script src="script.js"></script>
  <script>
    // Ensure script runs after DOM is fully loaded
    document.addEventListener('DOMContentLoaded', function() {
      // Tab switching functions
      function showLogin() {
        document.getElementById('loginForm').style.display = 'block';
        document.getElementById('signupForm').style.display = 'none';
        document.getElementById('loginTab').classList.add('active');
        document.getElementById('signupTab').classList.remove('active');
      }

      function showSignup() {
        document.getElementById('loginForm').style.display = 'none';
        document.getElementById('signupForm').style.display = 'block';
        document.getElementById('loginTab').classList.remove('active');
        document.getElementById('signupTab').classList.add('active');
      }

      // Expose functions to global scope for onclick attributes
      window.showLogin = showLogin;
      window.showSignup = showSignup;

      // Password toggle functionality
      const toggles = document.querySelectorAll('.toggle-password');
      toggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
          const passwordField = this.previousElementSibling; // Get the input right before the span
          if (passwordField.type === 'password') {
            passwordField.type = 'text';
            this.textContent = '🙈';
          } else {
            passwordField.type = 'password';
            this.textContent = '👁';
          }
        });
      });
    });
  </script>
</body>
</html>