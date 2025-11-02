<?php
require_once("connection.php");

session_start();

$message = "";
$showForm = false;

// ✅ STEP 1: Handle GET (token verification)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['username'], $_GET['token'])) {
    $username = trim($_GET['username']);
    $token = trim($_GET['token']);

    // Check if user + token match
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND reset_token = ?");
    $stmt->bind_param("ss", $username, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $showForm = true; // Valid token
    } else {
        // Invalid token → redirect
        header("Location: forget_password.php?error=invalid_token");
        exit();
    }
    $stmt->close();
}

// ✅ STEP 2: Handle POST (password update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $token = trim($_POST['token']);
    $newPassword = trim($_POST['new_password']);

    // Validate input
    if (empty($newPassword) || strlen($newPassword) < 6) {
        $message = "<p class='error'>❌ Password must be at least 6 characters long.</p>";
        $showForm = true;
    } else {
        // Check token validity again (for security)
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND reset_token = ?");
        $stmt->bind_param("ss", $username, $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // ✅ Update password + clear token
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE username = ?");
            $update->bind_param("ss", $hashedPassword, $username);

            if ($update->execute()) {
                $message = "<p class='success'>✅ Password reset successfully! Redirecting to login...</p>";
                echo '<meta http-equiv="refresh" content="3;url=login.php">';
            } else {
                $message = "<p class='error'>❌ Failed to reset password. Try again.</p>";
            }

            $update->close();
        } else {
            header("Location: forget_password.php?error=invalid_token");
            exit();
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
    margin: 0;
    padding: 0;
    background: #0f0f0f;
    color: #e0e0e0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.container {
    width: 90%;
    max-width: 420px;
    background: #1a1a1a;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.6);
    text-align: center;
}

h2 {
    color: #00bcd4;
    font-weight: 600;
    margin-bottom: 20px;
}

input {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 6px;
    border: 1px solid #333;
    background: #222;
    color: #e0e0e0;
    font-size: 14px;
}

button {
    width: 100%;
    padding: 12px;
    background: #00bcd4;
    border: none;
    border-radius: 6px;
    color: #fff;
    font-size: 15px;
    cursor: pointer;
    transition: background 0.3s ease;
}

button:hover {
    background: #0097a7;
}

.success, .error {
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 15px;
}

.success {
    background: rgba(0, 150, 136, 0.2);
    border: 1px solid #00bfa5;
    color: #80cbc4;
}

.error {
    background: rgba(183, 28, 28, 0.2);
    border: 1px solid #ef5350;
    color: #ef9a9a;
}

.footer-text {
    margin-top: 10px;
    font-size: 13px;
    color: #888;
}

.footer-text a {
    color: #00bcd4;
    text-decoration: none;
}
.footer-text a:hover {
    text-decoration: underline;
}

  </style>
</head>
<body class="body">

<div class="container">
  <h2>🔑 Reset Password</h2>

  <?php if (!empty($message)) echo $message; ?>

  <?php if ($showForm): ?>
  <form method="POST" action="">
      <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
      <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

      <label for="new_password">New Password</label>
      <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>

      <button type="submit">Update Password</button>

      <p class="footer-text">
        <a href="login.php">⬅ Back to login</a>
      </p>
  </form>
  <?php endif; ?>
</div>

</body>
</html>
