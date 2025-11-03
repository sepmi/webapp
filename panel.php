<?php   
require_once("connection.php");
session_start();

if(!isset($_SESSION['login'])){
    echo "<p>Need to login first! Redirecting in 3 seconds...</p>";
    echo "<script>
            setTimeout(function() {
                window.location.href = 'login.php';
            }, 3000);
          </script>";
    exit();
} else {

    $id = $_SESSION['user_id']; 

    // ✅ Fetch user info
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // ✅ Handle profile update
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_name = trim($_POST['name']);
        $new_password = trim($_POST['password']);
        $upload_dir = "/var/www/static/users_profiles/";
        $profile_picture = $user['profile_picture'];

        // Handle file upload
        if (!empty($_FILES['profile_picture']['name'])) {
            $file_name = basename($_FILES['profile_picture']['name']);
            $target_path = $upload_dir . $file_name;
            $file_type = strtolower(pathinfo($target_path, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];

            if (in_array($file_type, $allowed)) {
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
                    $profile_picture = $file_name;
                } else {
                    echo "<p style='color:red;'>❌ Failed to upload file.</p>";
                }
            } else {
                echo "<p style='color:red;'>❌ Invalid image type.</p>";
            }
        }

        // Update query
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name=?, password=?, profile_picture=? WHERE id=?");
            $stmt->bind_param("sssi", $new_name, $hashed_password, $profile_picture, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=?, profile_picture=? WHERE id=?");
            $stmt->bind_param("ssi", $new_name, $profile_picture, $id);
        }

        if ($stmt->execute()) {
            echo "<p style='color:lightgreen;'>✅ Profile updated successfully!</p>";
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
  <title>User Panel</title>
  <link rel="stylesheet" href="/static/style.css">
</head>

<body class="update-user-body">

  <!-- ✅ Fixed Header -->
  <header class="main-header">
    <div class="main-header-container">
      <h1 class="main-header-logo">🌙 User Panel</h1>
      <nav class="main-header-nav">
        <a href="panel.php" class="main-header-link">Dashboard</a>
        <a href="update_user.php" class="main-header-link">Edit Profile</a>
        <a href="logout.php?logout=true" class="main-header-logout">🚪 Logout</a>
      </nav>
    </div>
  </header>

  <!-- ✅ Main Content -->
  <main class="update-user-container">
    <h2 class="update-user-title">👋 Welcome, <?php echo htmlspecialchars($user['name']); ?></h2>

    <form method="POST" enctype="multipart/form-data" class="update-user-form">
      
      <label for="username" class="update-user-label">Username (read-only)</label>
      <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly class="update-user-input readonly">

      <label for="email" class="update-user-label">Email (read-only)</label>
      <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly class="update-user-input readonly">

      <label for="name" class="update-user-label">Name</label>
      <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required class="update-user-input">

      <label for="password" class="update-user-label">New Password (leave blank to keep current)</label>
      <input type="password" id="password" name="password" class="update-user-input">

      <label for="profile_picture" class="update-user-label">Profile Picture</label>
      <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="update-user-input-file">

      <div class="update-user-preview">
        <p>Current Image:</p>
        <img src="/static/users_profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="update-user-image">
      </div>

      <button type="submit" class="update-user-btn">💾 Save Changes</button>
    </form>
  </main>

</body>
</html>
<?php } ?>
