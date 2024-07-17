<?php
session_start();
require_once 'db_connect.php'; // Ensure this path is correct

// Function to fetch product details from database
function fetchProductDetails($product_id, $conn) {
    $sql_fetch_product = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $sql_fetch_product->bind_param('i', $product_id);
    $sql_fetch_product->execute();
    $result = $sql_fetch_product->get_result();
    return $result->fetch_assoc();
}

// Function to calculate total cart price
function calculateCartTotal() {
    $total = 0;
    foreach ($_SESSION['cart'] as $product_id => $product) {
        $total += $product['price'] * $product['quantity'];
    }
    return $total;
}

// Ensure cart is not empty
if (empty($_SESSION['cart'])) {
    $_SESSION['error_message'] = "Your shopping cart is empty.";
    header("Location: profile.php");
    exit();
}

// Calculate total price
$total_price = calculateCartTotal();

// Handle payment process
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_with_bitcoin'])) {
    // Set up Blockonomics API parameters
    $blockonomics_api_key = 'your_blockonomics_api_key';
    $blockonomics_callback_url = 'https://yourdomain.com/blockonomics_callback.php'; // Replace with your actual callback URL

    // Prepare payment data
    $payment_data = [
        'api_key' => $blockonomics_api_key,
        'price' => $total_price,
        'currency' => 'USD',
        'callback_url' => $blockonomics_callback_url,
        'order_id' => uniqid(), // Replace with your order ID generation logic
        'description' => 'Payment for products', // Replace with your description
    ];

    // Initialize cURL session
    $ch = curl_init('https://www.blockonomics.co/api/new_address');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payment_data));

    // Execute cURL session and capture API response
    $response = curl_exec($ch);
    curl_close($ch);

    // Parse JSON response
    $payment_info = json_decode($response, true);

    // Redirect to Blockonomics payment page
    if (isset($payment_info['address'])) {
        $_SESSION['payment_info'] = $payment_info; // Store payment info in session for callback verification
        header("Location: https://www.blockonomics.co/payment/" . $payment_info['address']);
        exit();
    } else {
        $_SESSION['error_message'] = "Failed to initiate payment. Please try again later.";
        header("Location: profile.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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

    <div class="container mt-4">
        <h1>Checkout</h1>
        <hr>
        <div class="row">
            <div class="col-md-8">
                <h3>Order Summary</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($_SESSION['cart'] as $product_id => $product) {
                            $product_details = fetchProductDetails($product_id, $conn);
                            if ($product_details) {
                                $subtotal = $product['quantity'] * $product_details['price'];
                                echo '
                                <tr>
                                    <td>' . htmlspecialchars($product_details['name']) . '</td>
                                    <td>$' . number_format($product_details['price'], 2) . '</td>
                                    <td>' . $product['quantity'] . '</td>
                                    <td>$' . number_format($subtotal, 2) . '</td>
                                </tr>
                                ';
                            }
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3">Total</th>
                            <th>$<?php echo number_format($total_price, 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Payment Method</h5>
                        <p>Pay with Bitcoin via Blockonomics</p>
                        <form action="" method="post">
                            <button type="submit" class="btn btn-primary btn-block" name="pay_with_bitcoin">Pay with Bitcoin</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
