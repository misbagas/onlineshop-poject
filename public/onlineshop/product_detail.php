<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'appuser', 'password', 'online_shop');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if product ID is set
if (!isset($_GET['product_id'])) {
    echo "Product ID is not set.";
    exit();
}

$product_id = $_GET['product_id'];

// Fetch product details from database
$sql_fetch_product = $conn->prepare("SELECT * FROM products WHERE id = ?");
$sql_fetch_product->bind_param('i', $product_id);
$sql_fetch_product->execute();
$result = $sql_fetch_product->get_result();
$product = $result->fetch_assoc();
$conn->close();

// Check if product exists
if (!$product) {
    echo "Product not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-4 mb-4"><?php echo htmlspecialchars($product['name']); ?></h1>
        <div class="card mb-4">
            <img src="/online_shop/public/photo_product/<?php echo basename($product['image']); ?>" class="card-img-top" alt="Product Image">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                <p class="card-text"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                <p class="card-text">Price: <?php echo htmlspecialchars($product['price']); ?></p>
                <a href="onlineshop.php" class="btn btn-primary">Back to Shop</a>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
