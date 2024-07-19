<?php
session_start();
include_once 'db_connect.php';


// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch username based on user_id
$sql = $conn->prepare("SELECT username FROM users WHERE id = ?");
$sql->bind_param('i', $user_id);
$sql->execute();
$sql->bind_result($username);
$sql->fetch();
$sql->close();

// Fetch chat messages
$chat_query = "SELECT * FROM chat_messages WHERE sender='$username' OR receiver='$username' ORDER BY timestamp DESC";
$chat_result = $conn->query($chat_query);

// Handle sending chat messages
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['chat_message'])) {
    $receiver = 'admin'; // Static value for admin
    $message = $_POST['chat_message'];
    $sender = $username;

    $stmt = $conn->prepare("INSERT INTO chat_messages (sender, receiver, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $sender, $receiver, $message);
    $stmt->execute();
    $stmt->close();
    header("Location: onlineshop.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Shop</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
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
                    echo '</a>';
                    echo '</div>';
                }
            } else {
                echo '<p>No products found.</p>';
            }
            ?>
        </div>
    </div>
    
   <!-- Chat Section -->
   <div class="card">
            <div class="card-header">Chat with Admin</div>
            <div class="card-body">
                <div class="chat-box" style="height: 300px; overflow-y: scroll;">
                    <?php while ($chat = $chat_result->fetch_assoc()) { ?>
                        <div>
                            <strong><?php echo htmlspecialchars($chat['sender']); ?>:</strong>
                            <span><?php echo htmlspecialchars($chat['message']); ?></span>
                            <small class="text-muted"><?php echo $chat['timestamp']; ?></small>
                        </div>
                    <?php } ?>
                </div>
                <form action="onlineshop.php" method="POST">
                    <div class="form-group">
                        <label for="chat_message">Message</label>
                        <textarea class="form-control" id="chat_message" name="chat_message" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        $(document).ready(function() {
            function checkNewMessages() {
                $.ajax({
                    url: 'check_new_messages.php',
                    method: 'GET',
                    success: function(data) {
                        if (data.new_messages > 0) {
                            $('#chat-notification-count').text(data.new_messages);
                        } else {
                            $('#chat-notification-count').text('');
                        }
                    }
                });
            }
            
            // Check for new messages every 5 seconds
            setInterval(checkNewMessages, 5000);
        });
    </script>
    
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
