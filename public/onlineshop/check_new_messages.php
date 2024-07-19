<?php
session_start();
include 'db_connect.php';

$user = isset($_SESSION['username']) ? $_SESSION['username'] : 'guest';

// Count new messages
$count_query = "SELECT COUNT(*) AS new_messages FROM chat_messages WHERE receiver='$user' AND timestamp > NOW() - INTERVAL 5 SECOND";
$count_result = $conn->query($count_query);
$count_data = $count_result->fetch_assoc();

header('Content-Type: application/json');
echo json_encode(['new_messages' => $count_data['new_messages']]);
?>
