
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
  <script src="https://cdn.tailwindcss.com"></script>
</head>



<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Register New User</h2>

    <form action="/register.php" method="POST" class="space-y-5">
      
     <!-- name -->
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">name</label>
        <input type="text" id="name" name="name" required 
               class="w-full border border-gray-300 rounded-lg p-2 focus:ring focus:ring-blue-200 focus:border-blue-500" 
               placeholder="Enter your name">
      </div>

      <!-- Username -->
      <div>
        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
        <input type="text" id="username" name="username" required 
               class="w-full border border-gray-300 rounded-lg p-2 focus:ring focus:ring-blue-200 focus:border-blue-500" 
               placeholder="Enter your username">
      </div>

     

      <!-- Email -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" id="email" name="email" required
               class="w-full border border-gray-300 rounded-lg p-2 focus:ring focus:ring-blue-200 focus:border-blue-500"
               placeholder="you@example.com">
      </div>

      

      <!-- Password -->
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input type="password" id="password" name="password" required minlength="6"
               class="w-full border border-gray-300 rounded-lg p-2 focus:ring focus:ring-blue-200 focus:border-blue-500"
               placeholder="Create a password">
      </div>
      <!-- Invitation Code -->
      <div>
        <label for="invitation_code" class="block text-sm font-medium text-gray-700 mb-1">Invitation Code</label>
        <input type="text" id="invitation_code" name="invitation_code" required
               class="w-full border border-gray-300 rounded-lg p-2 focus:ring focus:ring-blue-200 focus:border-blue-500"
               placeholder="Enter your invitation code">
      </div>

      <!-- Submit Button -->
      <button type="submit" name="submit"
              class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition duration-200">
        Register
      </button>

    </form>

    <p class="text-center text-sm text-gray-500 mt-4">
      Already have an account? 
      <a href="/login.php" class="text-blue-600 hover:underline">Login here</a>
    </p>
  </div>

</body>
</html>
