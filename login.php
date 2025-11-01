<?php
// Enable error reporting (for debugging)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

require_once("connection.php");

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);


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
    $stmt = $conn->prepare("SELECT id, username,name, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Step 2: Verify password
        if (password_verify($password, $user['password'])) {

            

            
                // ✅ Valid login +
                $_SESSION['login'] = true;
                $_SESSION['email'] = $user['email'];
                $_SESSION['name'] = $user['name'];

                echo "<p style='color: green;'>✅ Login successful! redirect to panel ..</p>";
                 echo '<meta http-equiv="refresh" content="3;url=panel.php">';
                 exit();

                
        } else {
            echo "<p style='color: red;'>❌ Incorrect password.</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No account found with that email.</p>";
    }

    $stmt->close();
    $conn->close();

}
?>

<!-- HTML Login Form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f8f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 320px;
        }
        input {
            display: block;
            width: 100%;
            padding: 10px;
            margin: 10px 0;
        }
        button {
            background: #007bff;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
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
      Dosen't have an account? 
      <a href="/register.php" class="text-blue-600 hover:underline">Register here</a>
    </p>
</form>



</body>
</html>
