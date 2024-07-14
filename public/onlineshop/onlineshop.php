<?php
session_start();
include_once 'db_connect.php';

// Handle logout if logout parameter is set
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    // Unset all session variables
    $_SESSION = array();
    // Destroy the session
    session_destroy();
    // Redirect to onlineshop.php after logout
    header("Location: onlineshop.php");
    exit();
}

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // User is logged in
    $navbar_links = '
        <li class="nav-item">
            <a class="nav-link" href="profile.php">Profile</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="onlineshop.php?logout=true">Logout</a>
        </li>
    ';
} else {
    // User is not logged in
    $navbar_links = '
        <li class="nav-item">
            <a class="nav-link" href="login.php">Login</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="register.php">Register</a>
        </li>
    ';
}

// Fetch products from database
$sql_fetch_products = "SELECT * FROM products";
$result = $conn->query($sql_fetch_products);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Shop</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Indonesian Product</a>
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
                <?php echo $navbar_links; ?>
            </ul>
        </div>
    </nav>
    
    <div class="container">
    <div class="marquee">
        <marquee behavior="scroll" direction="left">Welcome to our Online Shop!</marquee>
    </div>
</div>
    <div class="container">
        <h1 class="mt-4 mb-4">Products</h1>
        <div class="card-columns">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="card">';
                    echo '<a href="product_detail.php?product_id=' . $row['id'] . '">';
                    echo '<img src="/online_shop/public/' . $row['image'] . '" class="card-img-top" alt="Product Image">';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">' . $row['name'] . '</h5>';
                    echo '<p class="card-text">' . $row['description'] . '</p>';
                    echo '<p class="card-text">Price: ' . $row['price'] . '</p>';
                    echo '</div>';
                    echo '</a>';  // Add missing closing </a> tag
                    echo '</div>';
                }
            } else {
                echo '<p>No products found.</p>';
            }
            ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
