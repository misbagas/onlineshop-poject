<?php
session_start();
require_once 'db_connect.php'; // Ensure this path is correct

if (!isset($_GET['product_id']) || !isset($_GET['product_name']) || !isset($_GET['product_price'])) {
    echo "Invalid request.";
    exit();
}

$product_id = $_GET['product_id'];
$product_name = $_GET['product_name'];
$product_price = $_GET['product_price'];
$bitcoin_address = "bc1qx6u9xj2f4xzpca0qevsm7qzr9jvwmfhfmlcjmy"; // Your Bitcoin address

// Example conversion rate
$usd_to_btc_rate = 0.00003; // Example rate: 1 USD = 0.00003 BTC (this rate should be fetched from an API)
$bitcoin_amount = $product_price * $usd_to_btc_rate;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy with Bitcoin</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Buy <?php echo htmlspecialchars($product_name); ?> with Bitcoin</h1>
        <p>Price: USD <?php echo number_format($product_price, 2); ?></p>
        <p>Equivalent Bitcoin Amount: BTC <?php echo number_format($bitcoin_amount, 8); ?></p>
        <p>Send the exact Bitcoin amount to the address below:</p>
        <div class="alert alert-info">
            <strong>Bitcoin Address:</strong> <?php echo $bitcoin_address; ?>
        </div>
        <form action="verify_payment.php" method="post">
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            <input type="hidden" name="product_price" value="<?php echo $product_price; ?>">
            <input type="hidden" name="bitcoin_amount" value="<?php echo $bitcoin_amount; ?>">
            <div class="form-group">
                <label for="transaction_id">Transaction ID</label>
                <input type="text" class="form-control" id="transaction_id" name="transaction_id" required>
            </div>
            <button type="submit" class="btn btn-primary">Verify Payment</button>
        </form>
        <a href="product_detail.php?product_id=<?php echo $product_id; ?>" class="btn btn-secondary mt-2">Back to Product</a>
    </div>
</body>
</html>
