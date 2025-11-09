<?php
session_start();
require_once("connection.php");

if (!isset($_SESSION['login'])) {
    header("Location: msg.php?msg=Please login first&type=error&goto=login.php");
    exit();
}

if (isset($_GET['tweet_id']) && is_numeric($_GET['tweet_id'])) {
    $tweet_id = intval($_GET['tweet_id']);
    $user_id = $_SESSION['user_id'];

    // Only delete the tweet if it belongs to the logged-in user
    $stmt = $conn->prepare("DELETE FROM tweets WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $tweet_id, $user_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header("Location: msg.php?msg=Tweet%20deleted&type=success&goto=profile.php?user_id=$user_id");
    } else {
        header("Location: msg.php?msg=You%20can%20only%20delete%20your%20own%20tweets&type=error&goto=profile.php?user_id=$user_id");
    }

    $stmt->close();
}
$conn->close();
?>
