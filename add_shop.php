<?php
session_start();
include 'db.php';

// Check if user is logged in AND is an owner
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['submit_shop'])) {
    $owner_id = $_SESSION['user_id'];
    $name = mysqli_real_escape_string($conn, $_POST['shop_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $cat = mysqli_real_escape_string($conn, $_POST['category']);
    $phone = mysqli_real_escape_string($conn, $_POST['contact']);
    $addr = mysqli_real_escape_string($conn, $_POST['address']);

    $query = "INSERT INTO shops (owner_id, shop_name, description, category, contact, address) 
              VALUES ('$owner_id', '$name', '$desc', '$cat', '$phone', '$addr')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Shop successfully listed!'); window.location.href='index.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Your Shop</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 50px; }
        .form-container { background: white; padding: 30px; border-radius: 8px; max-width: 500px; margin: auto; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        input, textarea, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #218838; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>List Your Business</h2>
    <p>Welcome, <?php echo $_SESSION['user_name']; ?>. Fill in the details below to showcase your shop.</p>
    <form method="POST">
        <label>Shop Name</label>
        <input type="text" name="shop_name" placeholder="e.g. Saindane Electronics" required>

        <label>Category</label>
        <select name="category">
            <option value="Grocery">Grocery</option>
            <option value="Electronics">Electronics</option>
            <option value="Clothing">Clothing</option>
            <option value="Services">Local Services (Plumbing, etc.)</option>
            <option value="Other">Other</option>
        </select>

        <label>Description</label>
        <textarea name="description" rows="4" placeholder="Describe what you sell or do..."></textarea>

        <label>Contact Number</label>
        <input type="text" name="contact" placeholder="Mobile or Phone Number" required>

        <label>Full Address</label>
        <textarea name="address" rows="3" placeholder="Shop address for locals to find you..." required></textarea>

        <button type="submit" name="submit_shop">Publish Shop Listing</button>
    </form>
    <br>
    <a href="index.php">Back to Home</a>
</div>

</body>
</html>