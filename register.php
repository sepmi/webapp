
<?php
require_once("connection.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $invitation_code = trim($_POST['invitation_code']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Step 1: Basic validation
    if (empty($name) || empty($username) || empty($email) || empty($password) || empty($invitation_code)) {
        echo "<p style='color:red;'>❌ All fields are required.</p>";
        echo '<meta http-equiv="refresh" content="3;url=register.php">';
        exit();
    }

    // Step 2: Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<p style='color:red;'>❌ Invalid email format.</p>";
        echo '<meta http-equiv="refresh" content="3;url=register.php">';
        exit();
    }

    // Step 3: Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<p style='color:red;'>❌ Email already exists.</p>";
        echo '<meta http-equiv="refresh" content="3;url=register.php">';
        exit();
    }
    $stmt->close();

    // Step 4: Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<p style='color:red;'>❌ Username already exists.</p>";
        echo '<meta http-equiv="refresh" content="3;url=register.php">';
        exit();
    }
    $stmt->close();

    // Step 5: Validate invitation code
    $stmt = $conn->prepare("SELECT id, used FROM invitation_codes WHERE invitation_code = ?");
    $stmt->bind_param("s", $invitation_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<p style='color:red;'>❌ Invalid invitation code.</p>";
        echo '<meta http-equiv="refresh" content="3;url=register.php">';
        exit();
    }

    $code = $result->fetch_assoc();
    if ($code['used']) {
        echo "<p style='color:red;'>❌ This invitation code has already been used.</p>";
        echo '<meta http-equiv="refresh" content="3;url=register.php">';
        exit();
    }

    $stmt->close();

    // Step 6: Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, name, email, password, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("ssss", $username, $name, $email, $hashed_password);

    if ($stmt->execute()) {
        // Mark invitation as used
        $update = $conn->prepare("UPDATE invitation_codes SET used = 1 WHERE invitation_code = ?");
        $update->bind_param("s", $invitation_code);
        $update->execute();
        $update->close();

        echo "<p style='color:green;'>✅ Registration successful! Redirecting to login...</p>";
        echo '<meta http-equiv="refresh" content="3;url=login.php">';
    } else {
        echo "<p style='color:red;'>❌ Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
    $conn->close();
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Registration</title>
  <link rel="stylesheet" href="static/style.css">
</head>

<body class="reg-body">

  <div class="reg-container">
    <h2 class="reg-title">📝 Register New User</h2>

    <form action="/register.php" method="POST" class="reg-form">

      <!-- Name -->
      <div class="reg-group">
        <label for="name" class="reg-label">Name</label>
        <input type="text" id="name" name="name" required class="reg-input" placeholder="Enter your name">
      </div>

      <!-- Username -->
      <div class="reg-group">
        <label for="username" class="reg-label">Username</label>
        <input type="text" id="username" name="username" required class="reg-input" placeholder="Enter your username">
      </div>

      <!-- Email -->
      <div class="reg-group">
        <label for="email" class="reg-label">Email</label>
        <input type="email" id="email" name="email" required class="reg-input" placeholder="you@example.com">
      </div>

      <!-- Password -->
      <div class="reg-group">
        <label for="password" class="reg-label">Password</label>
        <input type="password" id="password" name="password" required minlength="6" class="reg-input" placeholder="Create a password">
      </div>

      <!-- Invitation Code -->
      <div class="reg-group">
        <label for="invitation_code" class="reg-label">Invitation Code</label>
        <input type="text" id="invitation_code" name="invitation_code" required class="reg-input" placeholder="Enter your invitation code">
      </div>

      <!-- Submit Button -->
      <button type="submit" name="submit" class="reg-button">Register</button>
    </form>

    <p class="reg-footer-text">
      Already have an account? 
      <a href="/login.php" class="reg-link">Login here</a>
    </p>
  </div>

</body>
</html>

