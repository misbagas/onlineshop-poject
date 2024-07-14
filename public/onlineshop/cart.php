<?php
session_start();
include_once "db_connect.php";

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Retrieve the current cart data from the database
$sql = $conn->prepare("SELECT cart FROM users WHERE id = ?");
$sql->bind_param('i', $user_id);
$sql->execute();
$sql->bind_result($cart_json);
$sql->fetch();
$sql->close();

$cart = $cart_json ? json_decode($cart_json, true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];

    // Add or remove product from the cart
    if (isset($_POST['remove'])) {
        // Remove product from cart
        unset($cart[$product_id]);
    } else {
        // Add product to cart
        $cart[$product_id] = [
            'name' => $_POST['product_name'],
            'price' => $_POST['product_price'],
            'quantity' => $_POST['product_quantity']
        ];

        // Set success message
        $_SESSION['cart_message'] = "Your product has been added in the profile section.";
    }

    // Save updated cart to database
    $cart_json = json_encode($cart);
    $sql = $conn->prepare("UPDATE users SET cart = ? WHERE id = ?");
    $sql->bind_param('si', $cart_json, $user_id);
    $sql->execute();
    $sql->close();
}

// Close the database connection
$conn->close();

// Redirect back to cart or shop page
header("Location: onlineshop.php");
exit();
?>
