<?php
session_start();

$bitcoin_address = 'bc1qx6u9xj2f4xzpca0qevsm7qzr9jvwmfhfmlcjmy';
$api_url = "https://api.blockcypher.com/v1/btc/main/addrs/$bitcoin_address/full?token=YOUR_API_TOKEN";

// Initialize cURL session
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute cURL session and fetch response
$response = curl_exec($ch);
curl_close($ch);

// Decode the JSON response
$data = json_decode($response, true);

// Check if there are any transactions
$payment_received = false;
if (isset($data['txs']) && count($data['txs']) > 0) {
    $payment_received = true;
}

// Return JSON response
echo json_encode(['payment_received' => $payment_received]);
?>
