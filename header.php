

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (isset($_GET['logout']) && $_GET['logout'] === 'true') {

  // ✅ If a session cookie exists, remove it from the browser
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // ✅ Destroy the session entirely
    session_destroy();
    // Redirect to msg.php with success message
    header("Location: msg.php?msg=Logout%20successful&goto=login.php&type=success");
    exit();

} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/static/style.css">
</head>
<body>

<header class="main-header">
  <div class="main-header-container">
    <div class="main-header-left">
      <a href="/panel.php" class="main-header-logo">🌙 WebApp</a>
    </div>

    <nav class="main-header-nav">
      <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
        <a href="/panel.php" class="main-header-link">Panel</a>
        <a href="/update_user.php" class="main-header-link">Edit Profile</a>
        <a href="/logout.php?logout=true" class="main-header-logout">🚪 Logout</a>
      <?php else: ?>
        <a href="/login.php" class="main-header-link">Login</a>
        <a href="/register.php" class="main-header-link">Register</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

</body>
</html>
