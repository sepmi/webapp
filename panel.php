<?php include('header.php'); ?>

<?php   
// Enable error reporting (for debugging)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

session_start();



if(!isset($_SESSION['login'])){
    echo "<p> need to login first ! Redirecting in 3 seconds...</p>";
    echo "<script>
            setTimeout(function() {
                window.location.href = 'login.php';
            }, 3000); // 3000 ms = 3 seconds
          </script>";
    exit();
} else{

    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
    </head>
    <body>
        <h1>panel</h1>
    </body>
    </html>
    <?php
    print("hello: " . $_SESSION['name']);
}
?>