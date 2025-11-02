<?php
require_once("connection.php");
require_once("function.php");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);

    if (empty($username)) {
        echo "<p style='color:red;'>❌ Please enter your username.</p>";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $token = random_token();

            // Update token
            $update_stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE username = ?");
            $update_stmt->bind_param("ss", $token, $username);
            if ($update_stmt->execute()) {
                // Redirect to show_token.php
                header("Location: reset_password.php?username=" . urlencode($username) . "&token=" . urlencode($token));
                exit();
            } else {
                echo "<p style='color:red;'>❌ Failed to save reset token.</p>";
            }
            $update_stmt->close();
        } else {
            echo "<p style='color:red;'>❌ Username not found.</p>";
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password</title>
  <link rel="stylesheet" href="//static.webapp.ir/style.css">

  
</head>
<body>

<div class="container">
  <form method="POST" action="">
      <h2>🔒 Forgot Password</h2>
      <label for="username">Username</label>
      <input type="text" name="username" id="username" placeholder="Enter your username" required>
      <button type="submit">Generate Reset Token</button>
      <p class="footer-text">
        <a href="login.php">⬅ Back to login</a>
      </p>
  </form>
</div>

</body>
</html>
