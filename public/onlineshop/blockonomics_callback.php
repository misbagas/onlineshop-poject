<?php
// Ensure this file is accessible and secured for handling callbacks
require_once 'db_connect.php'; // Ensure this path is correct

// Replace with your Blockonomics API secret
$blockonomics_api_secret = 'mjnMjJ587myuRc4CXBVmDCTjWrXRJpvpAeiuZhbpuek';

// Ensure request is POST and has a valid payload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw_post_data = file_get_contents('php://input');
    $signature = hash_hmac('sha256', $raw_post_data, $blockonomics_api_secret);

    // Verify signature
    if (hash_equals($signature, $_SERVER['HTTP_X_SIGNATURE'])) {
        // Signature valid, process callback data
        $payload = json_decode($raw_post_data, true);

        // Example: Update order status in database
        $order_id = $payload['order_id'];
        $status = $payload['status']; // 'confirmed', 'unconfirmed', etc.

        // Process payment status
        if ($status === 'confirmed') {
            // Payment confirmed, update order status in your database
            // Example: Update order status to 'Paid'
            $sql_update_order = $conn->prepare("UPDATE orders SET status = 'Paid' WHERE order_id = ?");
            $sql_update_order->bind_param('s', $order_id);
            $sql_update_order->execute();
            $sql_update_order->close();
        } else {
            // Payment not confirmed or other status, handle accordingly
            // Log or update status as required
        }

        http_response_code(200); // Respond with HTTP 200 OK
    } else {
        // Signature doesn't match, log or handle as per your security policy
        http_response_code(403); // Respond with HTTP 403 Forbidden
    }
} else {
    http_response_code(405); // Respond with HTTP 405 Method Not Allowed for non-POST requests
}
?>
