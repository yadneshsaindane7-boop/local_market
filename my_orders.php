<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];
$orders_query = "SELECT o.id, o.order_date, o.total_price, o.status, 
                        GROUP_CONCAT(CONCAT(oi.product_name, ' (x', oi.quantity, ')') SEPARATOR ', ') as item_list
                 FROM orders o
                 JOIN order_items oi ON o.id = oi.order_id
                 WHERE o.user_id = '$user_id'
                 GROUP BY o.id
                 ORDER BY o.order_date DESC";
$orders_result = mysqli_query($conn, $orders_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <style>
        :root { --primary: #4c51bf; --bg: #f8fafc; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; }
        nav { background: #fff; padding: 15px 5%; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .order-card { background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .status-pill { padding: 5px 15px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #d1ecf1; color: #0c5460; }
        .status-delivered { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <nav>
        <div style="font-size: 20px; font-weight: bold; color: var(--primary);">LocalMarket</div>
        <a href="index.php" style="text-decoration: none; color: #666;">Back to Shops</a>
    </nav>

    <div class="container">
        <h1>Order History</h1>
        <?php while($row = mysqli_fetch_assoc($orders_result)): ?>
            <div class="order-card">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                    <div>
                        <strong>Order #<?php echo $row['id']; ?></strong><br>
                        <small><?php echo date('d M Y', strtotime($row['order_date'])); ?></small>
                    </div>
                    <span class="status-pill status-<?php echo strtolower($row['status']); ?>">
                        <?php echo ($row['status'] == 'delivered') ? 'Delivered ✅' : $row['status']; ?>
                    </span>
                </div>
                <div style="margin: 15px 0; font-size: 14px;">
                    <strong>Items:</strong> <?php echo htmlspecialchars($row['item_list']); ?>
                </div>
                <div style="font-weight: bold; text-align: right;">Total: ₹<?php echo number_format($row['total_price'], 2); ?></div>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>