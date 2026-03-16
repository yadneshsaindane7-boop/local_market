<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php"); exit();
}

$owner_id = $_SESSION['user_id'];
$shop_res = mysqli_query($conn, "SELECT * FROM shops WHERE owner_id='$owner_id'");
$shop = mysqli_fetch_assoc($shop_res);

// Handle Multi-step Status Update
if (isset($_GET['update_status'])) {
    $order_id = $_GET['update_status'];
    $s_id = $shop['id'];
    
    // Get current status to determine the next step
    $check_status = mysqli_query($conn, "SELECT status FROM orders WHERE id='$order_id'");
    $row = mysqli_fetch_assoc($check_status);
    $current = $row['status'];

    $next_status = $current;
    if ($current == 'pending') $next_status = 'completed';
    elseif ($current == 'completed') $next_status = 'delivered';

    mysqli_query($conn, "UPDATE orders SET status='$next_status' WHERE id='$order_id' AND id IN (SELECT order_id FROM order_items WHERE shop_id='$s_id')");
    header("Location: owner_dashboard.php");
    exit();
}

// Existing Product Add/Delete logic remains the same...
if (isset($_POST['update_shop'])) {
    $name = mysqli_real_escape_string($conn, $_POST['shop_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $cat = mysqli_real_escape_string($conn, $_POST['category']);
    $phone = mysqli_real_escape_string($conn, $_POST['contact']);
    $addr = mysqli_real_escape_string($conn, $_POST['address']);
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $image_query = "";
    if (!empty($_FILES['shop_logo']['name'])) {
        $file_name = time() . "_" . basename($_FILES["shop_logo"]["name"]);
        if (move_uploaded_file($_FILES["shop_logo"]["tmp_name"], $target_dir . $file_name)) { $image_query = ", shop_image='$target_dir$file_name'"; }
    }
    $qr_query = "";
    if (!empty($_FILES['qr_code']['name'])) {
        $qr_file_name = time() . "_qr_" . basename($_FILES["qr_code"]["name"]);
        if (move_uploaded_file($_FILES["qr_code"]["tmp_name"], $target_dir . $qr_file_name)) { $qr_query = ", qr_code='$target_dir$qr_file_name'"; }
    }
    if ($shop) {
        mysqli_query($conn, "UPDATE shops SET shop_name='$name', description='$desc', category='$cat', contact='$phone', address='$addr' $image_query $qr_query WHERE owner_id='$owner_id'");
    } else {
        mysqli_query($conn, "INSERT INTO shops (owner_id, shop_name, description, category, contact, address) VALUES ('$owner_id', '$name', '$desc', '$cat', '$phone', '$addr')");
    }
    header("Location: owner_dashboard.php"); exit();
}

if (isset($_GET['delete_product'])) {
    $pid = $_GET['delete_product'];
    mysqli_query($conn, "DELETE FROM products WHERE id='$pid' AND shop_id='{$shop['id']}'");
    header("Location: owner_dashboard.php"); exit();
}

if (isset($_POST['add_product'])) {
    $p_name = mysqli_real_escape_string($conn, $_POST['p_name']);
    $p_price = mysqli_real_escape_string($conn, $_POST['p_price']);
    $p_desc = mysqli_real_escape_string($conn, $_POST['p_desc']);
    $s_id = $shop['id'];
    $p_image = "";
    if (!empty($_FILES['p_image']['name'])) {
        $target_dir = "uploads/";
        $p_file_name = time() . "_prod_" . basename($_FILES["p_image"]["name"]);
        if (move_uploaded_file($_FILES["p_image"]["tmp_name"], $target_dir . $p_file_name)) { $p_image = $target_dir . $p_file_name; }
    }
    mysqli_query($conn, "INSERT INTO products (shop_id, product_name, price, description, product_image) VALUES ('$s_id', '$p_name', '$p_price', '$p_desc', '$p_image')");
    header("Location: owner_dashboard.php"); exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Owner Dashboard</title>
    <style>
        :root { --primary: #4c51bf; --success: #28a745; --info: #17a2b8; --danger: #dc3545; --bg: #f4f6f9; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; background: var(--bg); color: #333; }
        header { background: #333; color: white; padding: 15px 5%; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        input, textarea, button, select { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #eee; font-size: 13px; }
        th { background: #f8f9fa; color: #666; }
        .status-badge { padding: 5px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; text-decoration: none; display: inline-block; transition: 0.2s; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #d1ecf1; color: #0c5460; }
        .status-delivered { background: #d4edda; color: #155724; }
        .btn-update:hover { filter: brightness(90%); cursor: pointer; }
    </style>
</head>
<body>
    <header>
        <strong>LocalMarket | Shop Panel</strong>
        <div style="font-size: 14px;">Welcome, <?php echo $_SESSION['user_name']; ?> | <a href="index.php" style="color:white;">View Site</a> | <a href="logout.php" style="color:white;">Logout</a></div>
    </header>

    <div class="container">
        <div class="grid">
            <div class="card">
                <h2>Shop Settings</h2>
                <form method="POST" enctype="multipart/form-data">
                    <label>Logo & QR Code</label>
                    <input type="file" name="shop_logo" accept="image/*">
                    <input type="file" name="qr_code" accept="image/*">
                    <input type="text" name="shop_name" value="<?php echo htmlspecialchars($shop['shop_name'] ?? ''); ?>" required>
                    <input type="text" name="contact" value="<?php echo htmlspecialchars($shop['contact'] ?? ''); ?>">
                    <textarea name="description" rows="2"><?php echo htmlspecialchars($shop['description'] ?? ''); ?></textarea>
                    <textarea name="address" rows="2"><?php echo htmlspecialchars($shop['address'] ?? ''); ?></textarea>
                    <button type="submit" name="update_shop" style="background: var(--primary); color:white; border:none; cursor:pointer;">Update Info</button>
                </form>
            </div>
            <div class="card">
                <h2>Add Product</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="text" name="p_name" placeholder="Product Name" required>
                    <input type="number" name="p_price" placeholder="Price" required>
                    <input type="file" name="p_image" accept="image/*">
                    <button type="submit" name="add_product" style="background: var(--success); color:white; border:none; cursor:pointer;">Add Product</button>
                </form>
            </div>
        </div>

        <div class="card">
            <h2>Orders Received</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Payment</th>
                        <th>Status (Click to Update)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if($shop) {
                        $s_id = $shop['id'];
                        $res = mysqli_query($conn, "SELECT oi.*, o.order_date, o.status, o.payment_method, u.fullname FROM order_items oi JOIN orders o ON oi.order_id = o.id JOIN users u ON o.user_id = u.id WHERE oi.shop_id = '$s_id' ORDER BY o.order_date DESC");
                        while($o = mysqli_fetch_assoc($res)): ?>
                        <tr>
                            <td><?php echo date('d M', strtotime($o['order_date'])); ?></td>
                            <td><?php echo htmlspecialchars($o['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($o['product_name']); ?></td>
                            <td><?php echo $o['payment_method']; ?></td>
                            <td>
                                <?php if($o['status'] != 'delivered'): ?>
                                    <a href="owner_dashboard.php?update_status=<?php echo $o['order_id']; ?>" 
                                       class="status-badge status-<?php echo $o['status']; ?> btn-update">
                                        <?php echo strtoupper($o['status']); ?> → Next Step
                                    </a>
                                <?php else: ?>
                                    <span class="status-badge status-delivered">DELIVERED ✅</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>