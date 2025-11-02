<?php
// Enable error reporting (optional for debugging)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

// Get query string values
$msg  = isset($_GET['msg'])  ? htmlspecialchars($_GET['msg'])  : "No message provided.";
$goto = isset($_GET['goto']) ? htmlspecialchars($_GET['goto']) : "index.php";
$type = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : "info"; // "success" | "error" | "info"

// Set color based on type
switch (strtolower($type)) {
  case 'error':
    $msg_color = '#f85149'; // red
    $icon = '❌';
    break;
  case 'success':
    $msg_color = '#2ea043'; // green
    $icon = '✅';
    break;
  default:
    $msg_color = '#58a6ff'; // blue/info
    $icon = 'ℹ️';
    break;
}

// Redirect after 3 seconds
header("refresh:3;url=$goto");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Message</title>
  <link rel="stylesheet" href="//static.webapp.ir/style.css">

  
    <!-- <script>
    let countdown = 3; // seconds
    function updateCountdown() {
      const counter = document.getElementById('redirect-timer');
      if (countdown > 0) {
        counter.textContent = countdown;
        countdown--;
        setTimeout(updateCountdown, 1000);
      } else {
        window.location.href = "<?php echo $goto; ?>";
      }
    }
    window.onload = updateCountdown;
  
  </script> -->

</head>
<body class="msg-body">

  <div class="msg-container">
    <div class="msg-box" style="border-color: <?php echo $msg_color; ?>;">
      <div class="msg-icon" style="color: <?php echo $msg_color; ?>;"><?php echo $icon; ?></div>
      <p class="msg-text" style="color: <?php echo $msg_color; ?>;"><?php echo $msg; ?></p>
      <p class="msg-redirect">Redirecting in 3 seconds...</p>
    </div>
  </div>

</body>
</html>
