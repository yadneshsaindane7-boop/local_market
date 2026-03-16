<?php
session_start();
include 'db.php';

// Handle Add to Cart logic (Existing logic remains the same)
if (isset($_GET['add_to_cart'])) {
    $product_id = $_GET['add_to_cart'];
    $prod_check = mysqli_query($conn, "SELECT * FROM products WHERE id = '$product_id'");
    if ($product = mysqli_fetch_assoc($prod_check)) {
        if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }
        if (isset($_SESSION['cart'][$product_id])) { $_SESSION['cart'][$product_id]['qty'] += 1; } 
        else {
            $_SESSION['cart'][$product_id] = [
                'name' => $product['product_name'],
                'price' => $product['price'],
                'shop_id' => $product['shop_id'],
                'qty' => 1
            ];
        }
    }
    header("Location: index.php");
    exit();
}

if (isset($_GET['clear_cart'])) { unset($_SESSION['cart']); header("Location: index.php"); exit(); }

$shops = mysqli_query($conn, "SELECT * FROM shops");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Local Market Explorer</title>
    <style>
        :root { --primary: #4c51bf; --bg: #f8fafc; --success: #28a745; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; }
        nav { background: #fff; padding: 15px 5%; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 1000; }
        .hero { background: var(--primary); color: white; text-align: center; padding: 40px 20px; }
        .container { max-width: 1200px; margin: 30px auto; padding: 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; }
        .shop-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; display: flex; flex-direction: column; }
        .shop-header { position: relative; height: 100px; background: #e2e8f0; }
        .shop-logo { width: 70px; height: 70px; border-radius: 50%; border: 4px solid white; position: absolute; bottom: -35px; left: 20px; object-fit: cover; background: #fff; }
        .shop-body { padding: 45px 20px 20px; flex-grow: 1; }
        .product-strip { display: flex; gap: 12px; overflow-x: auto; padding: 10px 0; margin-top: 10px; }
        .product-mini { min-width: 120px; background: #fff; border: 1px solid #f0f0f0; border-radius: 10px; padding: 8px; text-align: center; }
        .btn-add-cart { display: inline-block; background: var(--primary); color: white; border: none; padding: 5px 10px; border-radius: 5px; font-size: 11px; cursor: pointer; text-decoration: none; margin-top: 5px; }
        .btn-whatsapp { display: block; background: #25D366; color: white; text-align: center; padding: 12px; border-radius: 8px; text-decoration: none; font-weight: bold; margin-top: 15px; font-size: 14px; }
        .cart-toggle { background: var(--success); color: white; padding: 10px 20px; border-radius: 50px; text-decoration: none; font-weight: bold; position: fixed; bottom: 20px; right: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); z-index: 2000; }
        .cart-sidebar { position: fixed; right: -400px; top: 0; width: 350px; height: 100%; background: white; box-shadow: -5px 0 15px rgba(0,0,0,0.1); transition: 0.3s; z-index: 3000; padding: 25px; overflow-y: auto; }
        .cart-sidebar.active { right: 0; }
        .cart-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; font-size: 14px; }
        .close-cart { cursor: pointer; float: right; font-size: 24px; font-weight: bold; }
        .btn-order { background: var(--primary); color: white; border: none; width: 100%; padding: 15px; border-radius: 10px; font-weight: bold; cursor: pointer; margin-top: 20px; }
        .payment-methods { margin-top: 20px; background: #f9f9f9; padding: 15px; border-radius: 10px; border: 1px solid #eee; }
        .qr-display { margin-top: 15px; text-align: center; display: none; padding: 10px; background: #fff; border: 1px dashed #ccc; border-radius: 8px; }
        .qr-img { width: 150px; height: 150px; object-fit: contain; margin: 10px 0; }
        .txn-input { width: 100%; padding: 10px; margin-top: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 13px; display: none; }
    </style>
</head>
<body>
    <nav>
        <div style="font-size: 20px; font-weight: bold; color: var(--primary);">LocalMarket</div>
        <div>
            <?php if(isset($_SESSION['user_id'])): ?>
                <!-- NEW: Back to Dashboard button for Owners -->
                <?php if($_SESSION['user_role'] == 'owner'): ?>
                    <a href="owner_dashboard.php" style="margin-right:15px; font-size:14px; text-decoration:none; background: #333; color: white; padding: 5px 12px; border-radius: 5px;">🛠 Manage My Shop</a>
                <?php endif; ?>
                
                <a href="my_orders.php" style="margin-right:15px; font-size:14px; text-decoration:none; color:var(--primary); font-weight:bold;">📦 My Orders</a>
                <span style="margin-right:15px; font-size:14px;">Hi, <?php echo $_SESSION['user_name']; ?></span>
                <a href="logout.php" style="text-decoration:none; color:#666; font-size:14px;">Logout</a>
            <?php else: ?>
                <a href="login.php" style="text-decoration:none; color:var(--primary); font-weight:bold;">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <?php $cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'qty')) : 0; ?>
    <a href="javascript:void(0)" class="cart-toggle" onclick="toggleCart()">🛒 My Cart (<?php echo $cart_count; ?>)</a>

    <div class="cart-sidebar" id="cartSidebar">
        <span class="close-cart" onclick="toggleCart()">&times;</span>
        <h2>Shopping Cart</h2><hr>
        <?php if($cart_count > 0): ?>
            <?php 
            $total = 0; $shop_ids = [];
            foreach($_SESSION['cart'] as $id => $item): 
                $subtotal = $item['price'] * $item['qty']; $total += $subtotal; $shop_ids[] = $item['shop_id'];
            ?>
                <div class="cart-item">
                    <div><strong><?php echo $item['name']; ?></strong><br><small>Qty: <?php echo $item['qty']; ?> x ₹<?php echo $item['price']; ?></small></div>
                    <div>₹<?php echo $subtotal; ?></div>
                </div>
            <?php endforeach; ?>
            <div style="margin-top:20px; font-size: 18px; font-weight:bold;">Total: ₹<?php echo $total; ?></div>

            <form action="place_order.php" method="POST">
                <div class="payment-methods">
                    <h3>Payment Method</h3>
                    <label><input type="radio" name="payment_method" value="COD" checked onclick="showQR(false)"> Cash on Delivery</label><br>
                    <label><input type="radio" name="payment_method" value="Online" onclick="showQR(true)"> Online Payment</label>

                    <div id="qrSection" class="qr-display">
                        <p style="font-size: 11px; color: #888;">Scan QR to pay:</p>
                        <?php 
                        $u_ids = implode(',', array_unique($shop_ids));
                        $qr_res = mysqli_query($conn, "SELECT shop_name, qr_code FROM shops WHERE id IN ($u_ids)");
                        while($qr = mysqli_fetch_assoc($qr_res)): if($qr['qr_code']): ?>
                            <div style="margin-bottom: 10px;">
                                <div style="font-size:11px;"><?php echo htmlspecialchars($qr['shop_name']); ?></div>
                                <img src="<?php echo $qr['qr_code']; ?>" class="qr-img">
                            </div>
                        <?php endif; endwhile; ?>
                        
                        <!-- NEW: Transaction ID input -->
                        <input type="text" name="transaction_id" id="txnInput" class="txn-input" placeholder="Enter Transaction ID / Ref No.">
                    </div>
                </div>
                <button type="submit" class="btn-order">Confirm & Place Order</button>
            </form>
            <a href="?clear_cart=1" style="display:block; text-align:center; margin-top:10px; color:#999; font-size:12px;">Clear Cart</a>
        <?php else: ?>
            <p style="color:#666; text-align:center; margin-top:50px;">Your cart is empty.</p>
        <?php endif; ?>
    </div>

    <div class="hero"><h1>Connect with Local Shops</h1><p>Select items from local vendors and order directly.</p></div>
    <div class="container">
        <?php while($s = mysqli_fetch_assoc($shops)): ?>
            <div class="shop-card">
                <div class="shop-header">
                    <img src="<?php echo $s['shop_image'] ?: 'https://via.placeholder.com/70'; ?>" class="shop-logo">
                </div>
                <div class="shop-body">
                    <span style="color:var(--primary); font-size:11px; font-weight:bold;"><?php echo $s['category']; ?></span>
                    <h2 style="margin:5px 0; font-size:1.2rem;"><?php echo $s['shop_name']; ?></h2>
                    <p style="font-size:13px; color:#666; height: 40px; overflow: hidden;"><?php echo $s['description']; ?></p>
                    <div class="product-strip">
                        <?php 
                        $prods = mysqli_query($conn, "SELECT * FROM products WHERE shop_id='{$s['id']}'");
                        while($p = mysqli_fetch_assoc($prods)): ?>
                            <div class="product-mini">
                                <img src="<?php echo $p['product_image'] ?: 'https://via.placeholder.com/70'; ?>" style="width:100%; height:70px; object-fit:cover;">
                                <div style="font-size:11px; font-weight:bold;"><?php echo $p['product_name']; ?></div>
                                <div style="font-size:10px; color:green;">₹<?php echo $p['price']; ?></div>
                                <a href="?add_to_cart=<?php echo $p['id']; ?>" class="btn-add-cart">+ Add</a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $s['contact']); ?>" target="_blank" class="btn-whatsapp">Chat on WhatsApp</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
        function toggleCart() { document.getElementById('cartSidebar').classList.toggle('active'); }
        function showQR(status) {
            document.getElementById('qrSection').style.display = status ? 'block' : 'none';
            const txn = document.getElementById('txnInput');
            txn.style.display = status ? 'block' : 'none';
            txn.required = status; // Set as required if online
        }
    </script>
</body>
</html>