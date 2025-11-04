<?php   
require_once("connection.php");
session_start();

// ✅ Handle logout
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: msg.php?msg=Logout%20successful&goto=login.php&type=success");
    exit();
}

// ✅ Check login
if (!isset($_SESSION['login'])) {
    echo "<p>Need to login first! Redirecting in 3 seconds...</p>";
    echo "<script>
            setTimeout(() => window.location.href = 'login.php', 3000);
          </script>";
    exit();
}

$id = $_SESSION['user_id']; 

// ✅ Fetch user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// ✅ Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['name']);
    $new_bio = trim($_POST['bio']);
    $new_password = trim($_POST['password']);
    $upload_dir = "/var/www/static/profile_picture/";

    $profile_picture = (!empty($user['profile_picture']) && file_exists($upload_dir . $user['profile_picture']))
        ? $user['profile_picture']
        : "default.png";

    // ✅ Handle file upload
    if (!empty($_FILES['profile_picture']['name'])) {
        $file_name = basename($_FILES['profile_picture']['name']);
        $target_path = $upload_dir . $file_name;
        $file_type = strtolower(pathinfo($target_path, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];

        if (in_array($file_type, $allowed)) {
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
                $profile_picture = $file_name;
            } else {
                header("location:msg.php?msg=Failed to upload file&goto=panel.php&type=error");
                exit();
            }
        } else {
            header("location:msg.php?msg=Invalid image type&goto=panel.php&type=error");
            exit();
        }
    }

    // ✅ Update user info
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name=?, bio=?, password=?, profile_picture=? WHERE id=?");
        $stmt->bind_param("ssssi", $new_name, $new_bio, $hashed_password, $profile_picture, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, bio=?, profile_picture=? WHERE id=?");
        $stmt->bind_param("sssi", $new_name, $new_bio, $profile_picture, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['name'] = $new_name;
        header("location:msg.php?msg=Profile%20updated%20successfully&goto=panel.php&type=success");
        exit();
    } else {
        header("location:msg.php?msg=Error%20updating%20profile&goto=panel.php&type=error");
        exit();
    }
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

<body class="panel-body">
  <!-- ✅ Fixed Header -->
  <header class="panel-header">
    <div class="panel-header-container">
      <h1 class="panel-header-logo">🌙 User Panel</h1>
      <nav class="panel-header-nav">
        <a href="index.php" class="panel-header-link">Home</a>
        <a href="profile.php?user_id=<?php echo $id; ?>" class="panel-header-link">Profile</a>
        <a href="?logout=true" class="panel-header-logout">🚪 Logout</a>
      </nav>
    </div>
  </header>

  <!-- ✅ Main Content -->
  <main class="panel-container">
    <div class="panel-user-info">
      <a href="profile.php?user_id=<?php echo $id; ?>">
  <img src="http://static.webapp.ir/profile_picture/<?php echo htmlspecialchars($user['profile_picture']); ?>" 
       alt="Profile Picture" 
       class="panel-user-avatar">
</a>

      <!-- <img src="http://static.webapp.ir/profile_picture/<?php echo htmlspecialchars($user['profile_picture']); ?>" 
           alt="Profile Picture" 
           class="panel-user-avatar"> -->
      <div>
        <h2 class="panel-user-name">👋 <?php echo htmlspecialchars($user['name']); ?></h2>
        <p class="panel-user-bio"><?php echo nl2br(htmlspecialchars($user['bio'] ?? 'No bio yet.')); ?></p>
      </div>
    </div>

    <form method="POST" enctype="multipart/form-data" class="panel-form">
      <label class="panel-label">Username (read-only)</label>
      <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" readonly class="panel-input readonly">

      <label class="panel-label">Email (read-only)</label>
      <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly class="panel-input readonly">

      <label class="panel-label">Name</label>
      <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required class="panel-input">

      <label class="panel-label">Bio</label>
      <textarea name="bio" placeholder="Tell us about yourself..." class="panel-textarea"><?php echo htmlspecialchars($user['bio']); ?></textarea>

      <label class="panel-label">New Password (leave blank to keep current)</label>
      <input type="password" name="password" class="panel-input">

      <label class="panel-label">Profile Picture</label>
      <input type="file" name="profile_picture" accept="image/*" class="panel-file">

      <button type="submit" class="panel-btn">💾 Save Changes</button>
    </form>
  </main>
</body>
</html>
