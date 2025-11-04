<?php
session_start();
require_once("connection.php");

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
    header("Location: msg.php?msg=Logout%20successful&goto=index.php&type=success");
    exit();
}

// ✅ Get user_id if logged in
$is_logged_in = isset($_SESSION['login']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;

// ✅ Handle Tweet submission only if logged in
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['content']))) {
    $content = trim($_POST['content']);
    $stmt = $conn->prepare("INSERT INTO tweets (user_id, content) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $content);
    $stmt->execute();
    $stmt->close();
}

// ✅ Fetch all tweets with user info
$query = "
    SELECT t.id, t.content, t.created_at, u.name, u.profile_picture, u.id AS uid
    FROM tweets t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.created_at DESC
";
$tweets = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home - Tweet Board</title>
  <link rel="stylesheet" href="/static/style.css">
</head>

<body class="home-body">

  <!-- ===== Header ===== -->
  <header class="home-header">
    <div class="home-header-container">
      <h1 class="home-logo">🐦 Tweet Board</h1>
      <nav class="home-nav">
        <?php if ($is_logged_in): ?>
          <a href="panel.php" class="home-nav-link">Panel</a>
          <a href="?logout=true" class="home-logout-btn">🚪 Logout</a>
        <?php else: ?>
          <a href="login.php" class="home-nav-link">Login</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <!-- ===== Main Content ===== -->
  <main class="home-container">

    <!-- ✅ Show Tweet Form only if logged in -->
    <?php if ($is_logged_in): ?>
    <section class="home-tweet-box">
      <form method="POST" class="home-tweet-form">
        <textarea name="content" class="home-textarea" placeholder="What's happening?" required></textarea>
        <button type="submit" class="home-btn">Tweet</button>
      </form>
    </section>
    <?php endif; ?>

    <!-- ===== Tweets Feed ===== -->
    <section class="home-feed">
      <?php while($tweet = $tweets->fetch_assoc()): 
        $profile_picture = !empty($tweet['profile_picture']) ? htmlspecialchars($tweet['profile_picture']) : "default.png";
      ?>
      <div class="home-tweet">
        <div class="home-tweet-user">
          <a href="profile.php?user_id=<?php echo $tweet['uid']; ?>">
            <img src="http://static.webapp.ir/profile_picture/<?php echo $profile_picture; ?>" 
                 alt="Profile" 
                 class="home-user-avatar">
          </a>
          <div>
            <strong class="home-user-name"><?php echo htmlspecialchars($tweet['name']); ?></strong><br>
            <span class="home-tweet-date"><?php echo htmlspecialchars($tweet['created_at']); ?></span>
          </div>
        </div>
        <p class="home-tweet-content"><?php echo nl2br(htmlspecialchars($tweet['content'])); ?></p>
      </div>
      <?php endwhile; ?>
    </section>
  </main>

</body>
</html>
<?php $conn->close(); ?>
