<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Get the action and product_id from the POST request
$action = $_POST['action'];
$product_id = $_POST['product_id'];

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart = $_SESSION['cart'];

if ($action == 'remove' && isset($cart[$product_id])) {
    // Remove the product from the cart
    unset($cart[$product_id]);
    $_SESSION['cart'] = $cart;
    header("Location: profile.php");
    exit();
} elseif ($action == 'buy' && isset($cart[$product_id])) {
    // Handle the purchase logic here (e.g., process payment, reduce stock)
    // For simplicity, we'll just remove the product from the cart
    unset($cart[$product_id]);
    $_SESSION['cart'] = $cart;
    // You can add code here to handle payment processing and order completion
    header("Location: profile.php?purchase=success");
    exit();
}

// Redirect back to profile page if no valid action was taken
header("Location: profile.php");
exit();
