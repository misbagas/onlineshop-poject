<?php
// Example code snippet to update product entry after successful Bitcoin payment verification
$transaction_id = $_POST['transaction_id'];
$bitcoin_amount = $_POST['bitcoin_amount'];
$user_email = $_POST['user_email'];
$product_id = $_POST['product_id'];

$servername = "172.27.98.229"; // Replace with your actual database server IP or hostname
$username = "appuser";
$password = "@Bagaskara123";
$database = "online_shop";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare and execute SQL statement to update product entry
$update_sql = $conn->prepare("UPDATE products SET bitcoin_payment_status = 'Paid', bitcoin_transaction_id = ?, bitcoin_amount = ?, bitcoin_payment_time = CURRENT_TIMESTAMP, bitcoin_user_email = ? WHERE id = ?");
$update_sql->bind_param('sdsi', $transaction_id, $bitcoin_amount, $user_email, $product_id);

if ($update_sql->execute()) {
    echo "Bitcoin payment updated successfully.";
} else {
    echo "Error updating Bitcoin payment: " . $update_sql->error;
}

$update_sql->close();
$conn->close();
?>
