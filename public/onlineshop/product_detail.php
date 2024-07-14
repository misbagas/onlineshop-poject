<?php
session_start();
require_once 'db_connect.php'; // Ensure this path is correct

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

// Handle adding to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    // Validate quantity, ensure it's a positive integer (you can add more validation as needed)
    if ($quantity <= 0) {
        $_SESSION['error_message'] = "Invalid quantity.";
        header("Location: product_detail.php?product_id=$product_id");
        exit();
    }

    // Initialize cart if it doesn't exist in session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add product to cart or update quantity if already in cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        $product['quantity'] = $quantity;
        $_SESSION['cart'][$product_id] = $product;
    }

    // Redirect to product detail page or cart page
    header("Location: product_detail.php?product_id=$product_id&added_to_cart=true");
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
    <style>
        .product-image {
            width: 100%;
            max-width: 400px;
            margin: auto;
        }
        .product-details {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .product-info {
            flex: 1;
            margin-left: 20px;
        }
        .out-of-stock {
            color: red;
        }
        .price {
            font-size: 24px;
            color: #000;
        }
        .discounted-price {
            text-decoration: line-through;
            color: grey;
        }
        .quantity-input {
            max-width: 80px;
            text-align: center;
        }
        .buy-now-btn {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="onlineshop.php">Indonesian Product</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="onlineshop.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">Contact</a>
                </li>
                <?php
                if (isset($_SESSION['user_id'])) {
                    // User is logged in
                    echo '
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="onlineshop.php?logout=true">Logout</a>
                    </li>
                    ';
                } else {
                    // User is not logged in
                    echo '
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                    ';
                }
                ?>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="marquee">
            <marquee behavior="scroll" direction="left">Welcome to our Online Shop!</marquee>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_GET['added_to_cart']) && $_GET['added_to_cart'] == 'true'): ?>
            <div class="alert alert-success" role="alert">
                Product added to Profile in shopping cart section sucessfully
            </div>
        <?php endif; ?>
        <div class="product-details mt-4">
            <div class="product-image">
                <img src="/online_shop/public/<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image" class="img-fluid">
            </div>
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="price-details">
                    <?php if ($product['stock'] <= 0) : ?>
                        <p class="out-of-stock">Out of stock</p>
                    <?php else: ?>
                        <p>In stock</p>
                    <?php endif; ?>
                    <p class="price">USD <?php echo number_format($product['price'], 2); ?> 
                        <?php if (!empty($product['discounted_price'])) : ?>
                            <span class="discounted-price">USD <?php echo number_format($product['discounted_price'], 2); ?></span>
                        <?php endif; ?>
                    </p>
                </div>
                <form action="product_detail.php?product_id=<?php echo $product_id; ?>" method="post">
                    <div class="form-group">
                        <label for="quantity">Quantity:</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <button type="button" class="btn btn-outline-secondary" id="decrease">-</button>
                            </div>
                            <input type="number" name="quantity" id="quantity" value="1" class="form-control quantity-input" min="1" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="increase">+</button>
                            </div>
                        </div>
                    </div>
                    <p>Total Price: <span id="total-price">USD <?php echo number_format($product['price'], 2); ?></span></p>
                    <button type="submit" class="btn btn-primary buy-now-btn" name="add_to_cart" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>Add to Cart</button>
                </form>
                <div class="mt-3">
                    <a href="#" class="btn btn-outline-secondary">Wishlist</a>
                </div>
                <div class="mt-3">
                    <p>Categories: JUUL, Best Seller</p>
                    <p>Tags: JUUL</p>
                </div>
                <hr>
                <h3>Description</h3>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('increase').addEventListener('click', function() {
            updateQuantity(1);
        });

        document.getElementById('decrease').addEventListener('click', function() {
            updateQuantity(-1);
        });

        function updateQuantity(change) {
            let quantityInput = document.getElementById('quantity');
            let currentQuantity = parseInt(quantityInput.value);
            let newQuantity = currentQuantity + change;

            if (newQuantity < 1) {
                return; // Prevent quantity from going negative
            }

            quantityInput.value = newQuantity;
            updateTotalPrice(newQuantity);
        }

        function updateTotalPrice(quantity) {
            let price = <?php echo $product['price']; ?>;
            let totalPriceElement = document.getElementById('total-price');
            let totalPrice = price * quantity;
            totalPriceElement.textContent = 'USD ' + totalPrice.toFixed(2);
        }

        // Update total price on page load
        updateTotalPrice(parseInt(document.getElementById('quantity').value));
    </script>
</body>
</html>
