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
  <link rel="stylesheet" href="static/style.css">
</head>
<body class="fp-body">

<div class="fp-container">
  <form method="POST" action="" class="fp-form">
      <h2 class="fp-title">🔒 Forgot Password</h2>

      <div class="fp-group">
        <label for="username" class="fp-label">Username</label>
        <input type="text" name="username" id="username" class="fp-input" placeholder="Enter your username" required>
      </div>

      <button type="submit" class="fp-button">Generate Reset Token</button>

      <p class="fp-footer-text">
        <a href="login.php" class="fp-link">⬅ Back to login</a>
      </p>
  </form>
</div>

</body>
</html>

