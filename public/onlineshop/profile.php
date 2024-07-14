<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Database connection
include_once "db_connect.php";

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user information (excluding password)
$user_id = $_SESSION['user_id'];
$sql = $conn->prepare("SELECT username, auth_code, password FROM users WHERE id = ?");
$sql->bind_param('i', $user_id);
$sql->execute();
$sql->bind_result($username, $auth_code, $hashed_password);
$sql->fetch();
$sql->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }
        .profile-container {
            display: flex;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .sidebar {
            flex: 1;
            min-width: 250px;
            padding: 20px;
            background-color: #f8f9fa;
            border-right: 1px solid #e0e0e0;
        }
        .content {
            flex: 3;
            padding: 20px;
        }
        .profile-actions a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            margin: 5px;
            font-size: 16px;
        }
        .profile-actions a:hover {
            background-color: #45a049;
        }
        .card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="onlineshop.php">Online Shop</a>
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

    <div class="profile-container mt-4">
        <div class="sidebar">
            <div class="card">
                <div class="card-header">
                    Account
                </div>
                <div class="card-body">
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
                    <p><strong>Authentication Code:</strong> <?php echo htmlspecialchars($auth_code); ?></p>
                    <p><strong>Password (hashed):</strong> <?php echo htmlspecialchars($hashed_password); ?></p>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    Cart
                </div>
                <div class="card-body">
                    <?php if (!empty($cart)) : ?>
                        <ul class="list-group">
                            <?php foreach ($cart as $product_id => $product) : ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                    <span class="badge badge-primary badge-pill"><?php echo $product['quantity']; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="mt-3">
                            <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                        </div>
                    <?php else : ?>
                        <p>Your cart is empty.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="content">
            <h2>Shopping Cart</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_price = 0;

                    foreach ($cart as $product_id => $product) {
                        $subtotal = $product['price'] * $product['quantity'];
                        $total_price += $subtotal;

                        echo '
                        <tr>
                            <td>' . htmlspecialchars($product['name']) . '</td>
                            <td>USD ' . number_format($product['price'], 2) . '</td>
                            <td>' . $product['quantity'] . '</td>
                            <td>USD ' . number_format($subtotal, 2) . '</td>
                            <td>
                                <form action="cart.php" method="post">
                                    <input type="hidden" name="product_id" value="' . $product_id . '">
                                    <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                        ';
                    }

                    // Display total price
                    echo '
                    <tr>
                        <td colspan="3" class="text-right"><strong>Total:</strong></td>
                        <td><strong>USD ' . number_format($total_price, 2) . '</strong></td>
                        <td></td>
                    </tr>
                    ';
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
