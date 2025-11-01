<?php
// Database configuration
$servername = "localhost";   // or your server IP
$username   = "sepehr";        // replace with your MySQL username
$password   = "Sepehr1234";            // replace with your MySQL password
$dbname     = "voorivebdb";  // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}


?>


<?php

