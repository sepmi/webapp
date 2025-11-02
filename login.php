<?php
require_once("connection.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // ✅ Added: Collect environment info for login_log
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';

    // ✅ Step 1: Check for empty inputs
    if (empty($email) || empty($password)) {
        echo "<p style='color:red;'>❌ Please fill in both email and password.</p>";
        echo '<meta http-equiv="refresh" content="3;url=login.php">';

        exit();
    }

    // ✅ Step 2: Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<p style='color:red;'>❌ Invalid email format.</p>";
        echo '<meta http-equiv="refresh" content="3;url=login.php">';

        exit();
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
<body>

<form method="POST" action="">
    <h2>Login</h2>
    <label>Email</label>
    <input type="email" name="email" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit">Login</button>

    <p class="text-center text-sm text-gray-500 mt-4">
      Don’t have an account? 
      <a href="/register.php" class="text-blue-600 hover:underline">Register here</a>
    </p>

    <p class="text-center text-sm text-gray-500 mt-4">
      Forget your password? 
      <a href="/forget_password.php" class="text-blue-600 hover:underline">Forget Password </a>
    </p>
</form>

</body>
</html>
