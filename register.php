<?php
include 'db.php';

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    // Hash password for security
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Check if email already exists
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if(mysqli_num_rows($check) > 0) {
        $error = "This email is already registered!";
    } else {
        $sql = "INSERT INTO users (fullname, email, password, role) VALUES ('$name', '$email', '$pass', '$role')";
        if (mysqli_query($conn, $sql)) {
            header("Location: login.php?msg=registered");
            exit();
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Local Market</title>
    <style>
        :root {
            --primary: #4c51bf;
            --primary-hover: #434190;
            --secondary: #764ba2;
            --text-main: #2d3748;
            --error: #e53e3e;
        }

        body { 
            font-family: 'Segoe UI', system-ui, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin: 0; 
            padding: 20px;
            box-sizing: border-box;
        }

        .register-card { 
            background: white; 
            padding: 40px; 
            border-radius: 16px; 
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); 
            width: 100%; 
            max-width: 450px; 
            text-align: center; 
        }

        h2 { 
            color: var(--text-main); 
            margin-bottom: 8px; 
            font-size: 1.8rem;
            font-weight: 800;
        }

        p.subtitle {
            color: #718096;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }

        .form-group {
            text-align: left;
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #4a5568;
        }

        input, select { 
            width: 100%; 
            padding: 12px 16px; 
            border: 2px solid #e2e8f0; 
            border-radius: 8px; 
            box-sizing: border-box; 
            font-size: 15px; 
            transition: all 0.2s;
            outline: none;
        }

        input:focus, select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(76, 81, 191, 0.1);
        }

        button { 
            width: 100%; 
            padding: 14px; 
            background: var(--primary); 
            color: white; 
            border: none; 
            border-radius: 8px; 
            font-size: 16px; 
            font-weight: 700; 
            cursor: pointer; 
            transition: 0.3s; 
            margin-top: 20px; 
        }

        button:hover { 
            background: var(--primary-hover); 
            transform: translateY(-1px);
        }

        .error-box { 
            background: #fff5f5;
            color: var(--error); 
            padding: 12px;
            border-radius: 8px;
            border-left: 4px solid var(--error);
            font-size: 14px; 
            margin-bottom: 20px; 
            text-align: left;
        }

        .footer-link {
            margin-top: 25px;
            color: #718096;
            font-size: 14px;
        }

        .footer-link a { 
            color: var(--primary); 
            text-decoration: none; 
            font-weight: 700;
        }

        .footer-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <h2>Join Local Market</h2>
        <p class="subtitle">Showcase your products to the community</p>

        <?php if(isset($error)): ?>
            <div class="error-box"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" placeholder="John Doe" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="name@example.com" required>
            </div>

            <div class="form-group">
                <label>Create Password</label>
                <input type="password" name="password" placeholder="Min. 8 characters" required>
            </div>

            <div class="form-group">
                <label>I want to...</label>
                <select name="role">
                    <option value="customer">Browse and Buy Products</option>
                    <option value="owner">Register My Local Business</option>
                </select>
            </div>

            <button type="submit" name="register">Create My Account</button>
        </form>

        <div class="footer-link">
            Already have an account? <a href="login.php">Sign in here</a>
        </div>
    </div>
</body>
</html>