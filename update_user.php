<?php
session_start();
require_once("connection.php");

// ✅ Redirect if user is not logged in
if (!isset($_SESSION['email'])) {
    header("Location: msg.php?msg=Please%20login%20first&goto=login.php&type=error");
    exit();
}

$user_email = $_SESSION['email'];

// ✅ Get current user info
$stmt = $conn->prepare("SELECT id, username, name, email, profile_picture FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['name']);
    $new_password = trim($_POST['password']);
    $user_id = $user['id'];
    $upload_dir = "/var/www/static/users_profiles/";

    // Default to existing image
    $profile_picture = $user['profile_picture'];

    // ✅ Handle image upload if provided
    if (!empty($_FILES['profile_picture']['name'])) {
        $file_name = basename($_FILES['profile_picture']['name']);
        $target_path = $upload_dir . $file_name;
        $file_type = strtolower(pathinfo($target_path, PATHINFO_EXTENSION));

        // Allow only image files
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
                $profile_picture = $file_name;
            } else {
                echo "<p style='color:red;'>❌ Failed to upload image.</p>";
            }
        } else {
            echo "<p style='color:red;'>❌ Invalid image format. Only JPG, PNG, GIF, WEBP allowed.</p>";
        }
    }

    // ✅ Hash password if user entered a new one
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name=?, password=?, profile_picture=? WHERE id=?");
        $stmt->bind_param("sssi", $new_name, $hashed_password, $profile_picture, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, profile_picture=? WHERE id=?");
        $stmt->bind_param("ssi", $new_name, $profile_picture, $user_id);
    }

    if ($stmt->execute()) {
        echo "<p style='color:green;'>✅ Profile updated successfully!</p>";
        $_SESSION['name'] = $new_name;
        echo '<meta http-equiv="refresh" content="2;url=panel.php">';
    } else {
        echo "<p style='color:red;'>❌ Error updating profile: " . $stmt->error . "</p>";
    }

    $stmt->close();
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Profile</title>
  <link rel="stylesheet" href="/static/style.css">
</head>
<body class="update-user-body">

<div class="update-user-container">
  <h2 class="update-user-title">👤 Update Your Profile</h2>

  <form method="POST" enctype="multipart/form-data" class="update-user-form">
    
    <label for="username" class="update-user-label">Username (read-only)</label>
    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly class="update-user-input readonly">

    <label for="email" class="update-user-label">Email (read-only)</label>
    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly class="update-user-input readonly">

    <label for="name" class="update-user-label">Name</label>
    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required class="update-user-input">

    <label for="password" class="update-user-label">New Password (leave empty to keep current)</label>
    <input type="password" id="password" name="password" class="update-user-input">

    <label for="profile_picture" class="update-user-label">Profile Picture</label>
    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="update-user-input-file">

    <div class="update-user-preview">
      <p>Current Image:</p>
      <img src="/static/users_profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile" class="update-user-image">
    </div>

    <button type="submit" class="update-user-btn">💾 Save Changes</button>
  </form>

  <p class="update-user-footer">
    <a href="panel.php" class="update-user-back">⬅ Back to Panel</a>
  </p>
</div>

</body>
</html>
