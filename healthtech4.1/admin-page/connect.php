<?php
$servername = "localhost";
$username = "root";
$password = "12345678";
$database = "konsulku";

// Create connection
$db = new mysqli($servername, $username, $password, $database);

// Check connection
if ($db->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>