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
// $query = "
//     SELECT t.id, t.content, t.created_at, u.name, u.profile_picture, u.id AS uid
//     FROM tweets t
//     JOIN users u ON t.user_id = u.id
//     ORDER BY t.created_at DESC
// ";
// $tweets = $conn->query($query);

//fetch tweets with xmlhttprequest with js

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home - Tweet Board</title>
  <link rel="stylesheet" href="/static/style.css">

  <script>
    // === Fetch tweets using XMLHttpRequest ===
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "tweets.php", true);

    xhr.onload = function() {
      if (xhr.status === 200) {
        try {
          const response = JSON.parse(xhr.responseText);
          // Get the container element for the tweets
          const tweetsFeed = document.getElementById("home-feed"); 

          if (response.status === "success" && response.tweets.length > 0) {
            response.tweets.forEach(tweet => {
              // Determine the profile picture filename (default.png if empty)
              const profilePicture = tweet.profile_picture ? tweet.profile_picture : "default.png";
              
              // Helper function to escape HTML special characters for content safety
              const escapeHTML = (str) => {
                  if (typeof str !== 'string') return '';
                  return str.replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(/"/g, '&quot;')
                            .replace(/'/g, '&#039;');
              };

              // --- Start of tweet element creation matching the PHP structure ---
              
              const tweetEl = document.createElement("div");
              // **IMPORTANT: Use class "home-tweet"**
              tweetEl.className = "home-tweet"; 

              const userDiv = document.createElement("div");
              // **IMPORTANT: Use class "home-tweet-user"**
              userDiv.className = "home-tweet-user";

              const profileLink = document.createElement("a");
              profileLink.href = `profile.php?user_id=${tweet.uid}`;

              const avatarImg = document.createElement("img");
              avatarImg.src = `http://static.webapp.ir/profile_picture/${profilePicture}`;
              avatarImg.alt = "Profile";
              // **IMPORTANT: Use class "home-user-avatar"**
              avatarImg.className = "home-user-avatar";
              
              profileLink.appendChild(avatarImg);
              userDiv.appendChild(profileLink);

              const userInfoDiv = document.createElement("div");
              
              const userNameStrong = document.createElement("strong");
              // **IMPORTANT: Use class "home-user-name"**
              userNameStrong.className = "home-user-name";
              userNameStrong.textContent = escapeHTML(tweet.name);
              
              const dateSpan = document.createElement("span");
              // **IMPORTANT: Use class "home-tweet-date"**
              dateSpan.className = "home-tweet-date";
              dateSpan.textContent = escapeHTML(tweet.created_at);

              // Append elements to the userInfoDiv
              userInfoDiv.appendChild(userNameStrong);
              userInfoDiv.appendChild(document.createElement("br")); // Add the <br> tag
              userInfoDiv.appendChild(dateSpan);
              
              userDiv.appendChild(userInfoDiv);
              tweetEl.appendChild(userDiv); // Append user info section

              const contentParagraph = document.createElement("p");
              // **IMPORTANT: Use class "home-tweet-content"**
              contentParagraph.className = "home-tweet-content";
              // Emulate nl2br by replacing newlines with <br> and ensuring content is safe
              const safeContent = escapeHTML(tweet.content);
              contentParagraph.innerHTML = safeContent.replace(/\n/g, '<br>');

              tweetEl.appendChild(contentParagraph); // Append content section

              // Add the complete tweet element to the feed
              tweetsFeed.appendChild(tweetEl);
            });
          } else {
            tweetsFeed.innerHTML = "<p>No tweets found.</p>";
          }
        } catch (e) {
          console.error("JSON parse error:", e);
          // Changed from "tweets" to "home-feed" as per the HTML snippet
          document.getElementById("home-feed").innerHTML = "<p>Failed to load tweets.</p>"; 
        }
      } else {
        console.error("Request failed:", xhr.status);
        // Changed from "tweets" to "home-feed" as per the HTML snippet
        document.getElementById("home-feed").innerHTML = "<p>Failed to fetch tweets from server.</p>";
      }
    };

    xhr.onerror = function() {
      // Changed from "tweets" to "home-feed" as per the HTML snippet
      document.getElementById("home-feed").innerHTML = "<p>Network error occurred.</p>";
    };

    xhr.send();
  </script>

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
    <section class="home-feed" id="home-feed">
      <!-- <?php while($tweet = $tweets->fetch_assoc()): 
        $profile_picture = !empty($tweet['profile_picture']) ? htmlspecialchars($tweet['profile_picture']) : "default.png";
      ?> -->
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
