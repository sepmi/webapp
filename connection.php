<?php
// Database configuration
$servername = "localhost";   
$username   = "sepehr";      
$password   = "Sepehr1234";  
$dbname     = "voorivebdb";  

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}


?>


<?php

