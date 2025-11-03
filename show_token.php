



<?php
require_once("connection.php");

// Get username and token from query string
$username = $_GET['username'] ?? '';
$token = $_GET['token'] ?? '';

$isValid = false;
$message = "";

// ✅ Validate inputs first
if (!empty($username) && !empty($token)) {
    // Prepare statement to check token
    $stmt = $conn->prepare("SELECT reset_token FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // ✅ Compare token with stored one
        if ($user['reset_token']=== $token) {
            $isValid = true;
        } else {
            $message = "<p class='error'>❌ Invalid or expired token for this user.</p>";
        }
    } else {
        $message = "<p class='error'>❌ Username not found.</p>";
    }

    $stmt->close();
} else {
    $message = "<p class='error'>❌ Missing username or token in the link.</p>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Reset Token</title>
  
</head>
<style>
/* 🌙 Global Dark Theme */
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

/* 🔳 Main Container */
.container {
    width: 90%;
    max-width: 420px;
    background: #1a1a1a;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.6);
    text-align: center;
}

/* 🏷️ Titles */
h2 {
    color: #00bcd4;
    font-weight: 600;
    margin-bottom: 20px;
}

/* 🔘 Buttons & Links */
button,
.btn-link {
    display: inline-block;
    width: 100%;
    padding: 12px;
    background: #00bcd4;
    border: none;
    border-radius: 6px;
    color: #fff;
    font-size: 15px;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.1s ease;
}

button:hover,
.btn-link:hover {
    background: #0097a7;
    transform: scale(1.02);
}

/* 📦 Input Fields */
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

input:focus {
    outline: none;
    border-color: #00bcd4;
    background: #252525;
}

/* ✅ Success & ❌ Error Messages */
.success, .error {
    padding: 12px;
    border-radius: 6px;
    text-align: center;
    font-size: 14px;
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

/* 🔐 Token Box */
.token-box {
    background: #212121;
    padding: 15px;
    border-radius: 8px;
    border: 1px dashed #00bcd4;
    color: #b2ebf2;
    word-wrap: break-word;
    font-size: 15px;
    margin-bottom: 20px;
}

/* 🧭 Footer */
.footer-text {
    margin-top: 15px;
    font-size: 13px;
    color: #888;
}

.footer-text a {
    color: #00bcd4;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-text a:hover {
    color: #26c6da;
    text-decoration: underline;
}

/* 📱 Responsive */
@media (max-width: 480px) {
    .container {
        padding: 20px;
        width: 95%;
    }
    h2 {
        font-size: 1.2em;
    }
    button, .btn-link {
        font-size: 14px;
        padding: 10px;
    }
}

</style>
<body>

<div class="container">
  <h2>🔐 Password Reset Token</h2>

  <?php if ($isValid): ?>
    <p class="success">✅ Token verified successfully for 
      <strong><?php echo htmlspecialchars($username); ?></strong>.</p>

    <div class="token-box">
      <p><b><?php echo htmlspecialchars($token); ?></b></p>
    </div>

    <p>You can now reset your password by clicking below:</p>

    <a href="reset_password.php?username=<?php echo urlencode($username); ?>&token=<?php echo urlencode($token); ?>" class="btn-link">
      👉 Reset Password
    </a>

  <?php else: ?>
    <?php echo $message; ?>
  <?php endif; ?>

  <p class="footer-text">
    <a href="login.php">⬅ Back to Login</a>
  </p>
</div>

</body>
</html>
