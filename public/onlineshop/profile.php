<?php
session_start();
require_once 'db_connect.php'; // Ensure this path is correct

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user information (excluding password and only select address additionally)
$user_id = $_SESSION['user_id'];
$sql = $conn->prepare("SELECT username, auth_code, address FROM users WHERE id = ?");
$sql->bind_param('i', $user_id);
$sql->execute();
$sql->bind_result($username, $auth_code, $address);
$sql->fetch();
$sql->close();
$conn->close();

// Your Bitcoin address
$bitcoin_address = 'bc1qx6u9xj2f4xzpca0qevsm7qzr9jvwmfhfmlcjmy';

// Check if payment is successful
$payment_received = isset($_SESSION['payment_received']) ? $_SESSION['payment_received'] : false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Your custom CSS styles */
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
                    echo '
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="onlineshop.php?logout=true">Logout</a>
                    </li>
                    ';
                } else {
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
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($address); ?></p>
                </div>
            </div>
        </div>

        <div class="content">
            <?php if ($payment_received): ?>
                <div class="alert alert-success" role="alert">
                    Payment successfully received!
                </div>
            <?php endif; ?>

            <h2>Payment Instructions</h2>
            <p>Please transfer the total amount to the following Bitcoin address:</p>
            <p><strong><?php echo htmlspecialchars($bitcoin_address); ?></strong></p>
            <p>Once the payment is confirmed, you will receive an alert.</p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
