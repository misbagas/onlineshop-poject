<?php
function createPaymentRequest($amount, $currency, $product_name) {
    $api_key = 'mjnMjJ587myuRc4CXBVmDCTjWrXRJpvpAeiuZhbpuek'; // Replace with your Blockonomics API key

    $url = 'https://www.blockonomics.co/api/create_payment';

    $data = [
        'price' => $amount,
        'currency' => $currency,
        'item_name' => $product_name,
        'callback_url' => 'YOUR_CALLBACK_URL', // Replace with your callback URL
        'custom' => 'YOUR_CUSTOM_DATA' // Optional custom data
    ];

    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n" .
                        "Authorization: Bearer $api_key\r\n",
            'method' => 'POST',
            'content' => json_encode($data)
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        die('Error creating payment request');
    }

    $response = json_decode($result, true);

    if (isset($response['payment_url'])) {
        return $response['payment_url'];
    } else {
        // Handle the error appropriately
        die('Error in response: ' . json_encode($response));
    }
}
?>
