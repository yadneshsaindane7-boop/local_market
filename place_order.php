<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first to place an order.'); window.location.href='login.php';</script>";
    exit();
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<script>alert('Your cart is empty.'); window.location.href='index.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$total_price = 0;
$payment_method = $_POST['payment_method'] ?? 'COD';
// NEW: Capture Transaction ID
$txn_id = isset($_POST['transaction_id']) ? mysqli_real_escape_string($conn, $_POST['transaction_id']) : NULL;

foreach ($_SESSION['cart'] as $item) { $total_price += ($item['price'] * $item['qty']); }

// 1. Insert into 'orders' table including payment_method AND transaction_id
$order_query = "INSERT INTO orders (user_id, total_price, status, payment_method, transaction_id) 
                VALUES ('$user_id', '$total_price', 'pending', '$payment_method', '$txn_id')";

if (mysqli_query($conn, $order_query)) {
    $order_id = mysqli_insert_id($conn);
    foreach ($_SESSION['cart'] as $item) {
        $p_name = mysqli_real_escape_string($conn, $item['name']);
        $p_price = $item['price'];
        $p_qty = $item['qty'];
        $shop_id = $item['shop_id']; 

        $item_query = "INSERT INTO order_items (order_id, shop_id, product_name, price, quantity) 
                       VALUES ('$order_id', '$shop_id', '$p_name', '$p_price', '$p_qty')";
        mysqli_query($conn, $item_query);
    }
    unset($_SESSION['cart']);
    echo "<script>alert('Order placed successfully! Owner will verify your payment.'); window.location.href='index.php';</script>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>