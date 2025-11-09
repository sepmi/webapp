<?php
header("Content-Type: application/json; charset=UTF-8");
require_once("connection.php");

// ✅ Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
    // ✅ Fetch all tweets with user info
    $sql = "
        SELECT 
            t.id, 
            t.content, 
            t.created_at, 
            u.name, 
            u.profile_picture, 
            u.id AS uid
        FROM tweets t
        JOIN users u ON t.user_id = u.id
        ORDER BY t.created_at DESC
    ";

    $result = $conn->query($sql);

    $tweets = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // ✅ Ensure profile picture fallback
            $row['profile_picture'] = !empty($row['profile_picture']) ? $row['profile_picture'] : "default.png";
            $tweets[] = $row;
        }
    }

    // ✅ Respond with JSON
    echo json_encode([
        "status" => "success",
        "count"  => count($tweets),
        "tweets" => $tweets
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // ✅ Handle any errors gracefully
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "Failed to fetch tweets",
        "details" => $e->getMessage()
    ]);
} finally {
    $conn->close();
}
}


?>
