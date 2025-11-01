<?php
// You can include this header on all your pages with:  include('header.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        header {
            background-color: #222;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-buttons {
            display: flex;
            gap: 10px;
        }
        .nav-buttons a {
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: 0.2s;
        }
        .nav-buttons a:hover {
            background-color: #45a049;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <div class="title">My Website</div>
        <div class="nav-buttons">
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
            <a href="panel.php">Panel</a>
        </div>
    </header>
</body>
</html>
