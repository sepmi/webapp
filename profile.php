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

// ✅ Validate GET parameter
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    header("Location: msg.php?msg=Invalid user ID&type=error&goto=index.php");
    exit();
}

$user_id = intval($_GET['user_id']);

// ✅ Fetch user data from Flask API
$api_url = "http://localhost:5000/api/users/" . $user_id;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || !$response) {
    header("Location: msg.php?msg=Failed to fetch user data&type=error&goto=index.php");
    exit();
}

$data = json_decode($response, true);

if (!$data || !isset($data['id'])) {
    header("Location: msg.php?msg=User not found&type=error&goto=index.php");
    exit();
}

// ✅ Use default profile picture if missing
$profile_picture = (!empty($data['profile_picture']))
    ? htmlspecialchars($data['profile_picture'])
    : "default.png";

// ✅ Fetch user tweets from MySQL
$stmt = $conn->prepare("SELECT content, created_at FROM tweets WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$tweets = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($data['name']); ?>'s Profile</title>
  <link rel="stylesheet" href="/static/style.css">
</head>

<body class="profile-body">

  <!-- === Header === -->
  <header class="profile-header">
    <div class="profile-header-container">
      <div class="profile-header-logo">🌙 User Profile</div>
      <nav class="profile-header-nav">
        <a href="index.php" class="profile-header-link">Home</a>
        <a href="panel.php" class="profile-header-link">Panel</a>
        <a href="?logout=true" class="profile-header-logout">Logout</a>
      </nav>
    </div>
  </header>

  <!-- === Main Container === -->
  <main class="profile-container">
    <div class="profile-user-section">
      <img src="http://static.webapp.ir/profile_picture/<?php echo $profile_picture; ?>" 
           alt="Profile Picture" class="profile-avatar">
      <div class="profile-user-info">
        <h2 class="profile-name"><?php echo htmlspecialchars($data['name']); ?></h2>
        <p class="profile-bio"><?php echo nl2br(htmlspecialchars($data['bio'] ?? "No bio yet...")); ?></p>
      </div>
    </div>

    <!-- === Tweets Section === -->
    <div class="profile-tweet-list">
      <h3 class="profile-tweet-title">📝 Tweets</h3>
      <?php if (empty($tweets)): ?>
        <p class="profile-no-tweets">No tweets yet.</p>
      <?php else: ?>
        <?php foreach ($tweets as $tweet): ?>
          <div class="profile-tweet-card">
            <p class="profile-tweet-content"><?php echo nl2br(htmlspecialchars($tweet['content'])); ?></p>
            <p class="profile-tweet-time">
              <?php echo htmlspecialchars($tweet['created_at'] ?? ""); ?>
            </p>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>

</body>
</html>
