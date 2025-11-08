
<?php
require_once("connection.php");
session_start();

$msg ="";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $invitation_code = trim($_POST['invitation_code']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Step 1: Basic validation
    if (empty($name) || empty($username) || empty($email) || empty($password) || empty($invitation_code)) {
        
        // header("location:msg.php?msg=❌ All fields are required&goto=register.php&type=error");
        $msg = "All fields are required";
        $color = "red";
        
    }elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        
        header("location:msg.php?msg=❌ Invalid email format&goto=register.php&type=error");
        
        exit();
    }

    if(empty($msg)){
    // Step 3: Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        
        header("location:msg.php?msg=❌ Email already exists&goto=register.php&type=error");
        exit();
    }
    $stmt->close();

    // Step 4: Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        
        header("location:msg.php?msg=❌ Username already exists&goto=register.php&type=error");
        exit();
    }
    $stmt->close();

    // Step 5: Validate invitation code
    $stmt = $conn->prepare("SELECT id, used FROM invitation_codes WHERE invitation_code = ?");
    $stmt->bind_param("s", $invitation_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header("location:msg.php?msg=❌ Invalid invitation code&goto=register.php&type=error");
        exit();
    }

    $code = $result->fetch_assoc();
    if ($code['used']) {
      
        header("location:msg.php?msg=❌ This invitation code has already been used&goto=register.php&type=error");
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

        
        header("location:msg.php?msg=✅ Registration successful! Redirecting to login...&goto=login.php&type=success");

    } else {
        echo "<p style='color:red;'>❌ Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
    $conn->close();
    }
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
        <input type="text" id="name" name="name" require class="reg-input" placeholder="Enter your name">
      </div>

      <!-- Username -->
      <div class="reg-group">
        <label for="username" class="reg-label">Username</label>
        <input type="text" id="username" name="username" require class="reg-input" placeholder="Enter your username">
      </div>

      <!-- Email -->
      <div class="reg-group">
        <label for="email" class="reg-label">Email</label>
        <input type="email" id="email" name="email"require  class="reg-input" placeholder="you@example.com">
      </div>

      <!-- Password -->
      <div class="reg-group">
        <label for="password" class="reg-label">Password</label>
        <input type="password" id="password" name="password" require minlength="6" class="reg-input" placeholder="Create a password">
      </div>

      <!-- Invitation Code -->
      <div class="reg-group">
        <label for="invitation_code" class="reg-label">Invitation Code</label>
        <input type="text" id="invitation_code" name="invitation_code" require class="reg-input" placeholder="Enter your invitation code">
      </div>

      <!-- Submit Button -->
      <button type="submit" name="submit" class="reg-button">Register</button>
    </form>

    <?php if(!empty($msg)): ?>

    <p class="reg-footer-text" style="color: <?php echo htmlspecialchars($color); ?>">
      <?php echo htmlspecialchars($msg); ?>
    </p>
    <?php endif ?>
    
    <p class="reg-footer-text">
      Already have an account? 
      <a href="/login.php" class="reg-link">Login here</a>
    </p>
  </div>

</body>
</html>

