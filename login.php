<?php
require_once("connection.php");
session_start();
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // ✅ Added: Collect environment info for login_log
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';

    // ✅ Step 1: Check for empty inputs
    if (empty($email) || empty($password)) {
       
        $msg = "Please fill in both email and password";
        $color = "red";
        
    }

    // ✅ Step 2: Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  
        $msg = " Invalid email format";
        $color= "red";
    
    }

    // Step 3: Check if user exists
    $stmt = $conn->prepare("SELECT id, username, name, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Step 4: Verify password
        if (password_verify($password, $user['password'])) {

            // ✅ Valid login
            $_SESSION['login'] = true;
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['name'];

            echo "<p style='color:green;'>✅ Login successful! Redirecting...</p>";
            echo '<meta http-equiv="refresh" content="3;url=panel.php">';

            // ✅ Added: Log success
            $status = 1;
            $log_stmt = $conn->prepare("INSERT INTO login_log (ip_address, user_agent, referer, login_status, username)
                                        VALUES (?, ?, ?, ?, ?)");
            $log_stmt->bind_param("sssis", $ip, $agent, $referer, $status, $email);
            $log_stmt->execute();
            $log_stmt->close();

            exit();

        } else {
            echo "<p style='color:red;'>❌ Incorrect password.</p>";

            // ✅ Added: Log failed password
            $status = 0;
            $log_stmt = $conn->prepare("INSERT INTO login_log (ip_address, user_agent, referer, login_status, username)
                                        VALUES (?, ?, ?, ?, ?)");
            $log_stmt->bind_param("sssis", $ip, $agent, $referer, $status, $email);
            $log_stmt->execute();
            $log_stmt->close();
        }

    } else {
        echo "<p style='color:red;'>❌ No account found with that email.</p>";

        // ✅ Added: Log failed 
            $status = 0;
            $log_stmt = $conn->prepare("INSERT INTO login_log (ip_address, user_agent, referer, login_status, username)
                                        VALUES (?, ?, ?, ?, ?)");
            $log_stmt->bind_param("sssis", $ip, $agent, $referer, $status, $email);
            $log_stmt->execute();
            $log_stmt->close();
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login Page</title>
  <link rel="stylesheet" href="//static.webapp.ir/style.css">
   
</head>
<body class="login-body">

  <div class="login-container">
    <form method="POST" action="" class="login-form">
      <h2 class="login-title">🔐 Login</h2>

      <div class="login-group">
        <label for="email" class="login-label">Email</label>
        <input type="email" name="email" id="email" class="login-input" placeholder="Enter your email" required>
      </div>

      <div class="login-group">
        <label for="password" class="login-label">Password</label>
        <input type="password" name="password" id="password" class="login-input" placeholder="Enter your password" required>
      </div>

      <button type="submit" class="login-button">Login</button>

      <p class="login-link-text">
        Don’t have an account?
        <a href="/register.php" class="login-link">Register here</a>
      </p>

      <p class="login-link-text">
        Forgot your password?
        <a href="/forget_password.php" class="login-link">Reset it here</a>
      </p>

      <?php if (!empty($msg)): ?>
        <p class="login-message" style="color: <?php echo htmlspecialchars($color); ?>">
          <?php echo htmlspecialchars($msg); ?>
        </p>
      <?php endif; ?>
    </form>
  </div>

</body>
</html>

