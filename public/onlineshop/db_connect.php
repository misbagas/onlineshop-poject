<?php
$servername = "localhost";
$username = "usershop";
$password = "Bagaskara123";
$dbname = "online_shop";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}