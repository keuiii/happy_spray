<?php
require_once 'classes/database.php';
session_start();

$db = Database::getInstance();
$msg = "";

// Default redirect
$redirect_to = $_GET['redirect_to'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_or_email = trim($_POST['username_or_email']);
    $password = trim($_POST['password']);

    if ($username_or_email === "" || $password === "") {
        $msg = "Please fill in all fields.";
    } else {
        // Login attempt
        $result = $db->loginCustomerAuth($username_or_email, $password);

        if ($result['success']) {
            $redirect = $_POST['redirect_to'] ?? 'index.php';
            if ($redirect === 'customer_login.php' || empty($redirect)) {
                $redirect = 'index.php';
            }
            header("Location: " . $redirect);
            exit;
        } else {
            $msg = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customer Login - Happy Sprays</title>
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0; background: #fff; color: #333;
}
/* Navbar */
.top-nav {
    position: fixed; top: 0; left: 0; width: 100%;
    background: #fff; border-bottom: 1px solid #eee;
    padding: 10px 20px; display: flex;
    justify-content: space-between; align-items: center;
    font-family: 'Playfair Display', serif;
    font-size: 22px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 2px; z-index: 1000;
}
.top-nav .logo { flex: 1; text-align: center; }
.nav-actions {
    display: flex; align-items: center; gap: 20px;
    position: absolute; right: 20px; top: 50%;
    transform: translateY(-50%);
}
.icon-btn, .cart-link, .profile-link { background: none; border: none; cursor: pointer; }
.icon-btn svg, .cart-link svg, .profile-link svg { stroke: black; width: 22px; height: 22px; }
.icon-btn:hover svg, .cart-link:hover svg, .profile-link:hover svg { stroke: #555; }
/* Sub Nav */
.sub-nav {
    position: fixed; top: 60px; left: 0; width: 100%;
    background: #fff; border-bottom: 1px solid #ccc;
    text-align: center; padding: 12px 0; z-index: 999;
    font-family: 'Playfair Display', serif;
    text-transform: uppercase; font-weight: 600; letter-spacing: 1px;
}
.sub-nav a { margin: 0 20px; text-decoration: none; color: #000; font-size: 16px; }
.sub-nav a:hover { color: #555; }
/* Login Form */
.login-container {
    background: #f5f5f5; padding: 40px; border-radius: 12px;
    width: 360px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    text-align: center; margin: 150px auto 60px;
}
.login-container h2 { margin-bottom: 20px; font-size: 28px; font-weight: bold; }
.login-container input {
    width: 100%; padding: 12px; margin: 10px 0;
    border: 1px solid #ddd; border-radius: 8px; font-size: 14px;
}
.login-container button {
    width: 100%; padding: 12px; border: 1px solid #000;
    border-radius: 8px; background: #fff; font-weight: bold;
    cursor: pointer; transition: 0.3s;
}
.login-container button:hover { background: #000; color: #fff; }
.msg { color: red; font-size: 14px; margin-bottom: 10px; }
.extra-links { margin-top: 15px; font-size: 14px; }
.extra-links a { color: #000; text-decoration: none; }
.extra-links a:hover { text-decoration: underline; }
/* Footer */
footer {
    background: #e9e9e9ff; border-top: 1px solid #eee;
    padding: 40px 20px; text-align: center; font-size: 14px; color: #555;
}
.footer-columns { display: flex; justify-content: center; gap: 100px; margin-bottom: 20px; }
.footer-columns h4 { font-size: 16px; margin-bottom: 10px; font-weight: bold; color: #000; }
.footer-columns a { display: block; text-decoration: none; color: #555; margin: 5px 0; }
.footer-columns a:hover { color: #000; }
.social-icons { margin-top: 15px; }
.social-icons a { margin: 0 8px; color: #555; text-decoration: none; font-size: 18px; }
.social-icons a:hover { color: #000; }
</style>
</head>
<body>
<!-- Navbar -->
<div class="top-nav">
    <div class="logo">Happy Sprays</div>
    <div class="nav-actions">
        <a href="cart.php" class="cart-link" title="Cart">
            🛒
        </a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="customer_dashboard.php" class="profile-link" title="My Account">👤</a>
        <?php else: ?>
            <a href="customer_login.php" class="profile-link" title="Login">👤</a>
        <?php endif; ?>
    </div>
</div>
<!-- Sub Navbar -->
<div class="sub-nav">
    <a href="index.php">Home</a>
    <a href="index.php?gender=Male">For Him</a>
    <a href="index.php?gender=Female">For Her</a>
    <a href="contact.php">Contact</a>
</div>
<!-- Login Form -->
<div class="login-container">
    <h2>Login</h2>
    <?php if($msg): ?><p class="msg"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
    <form method="post">
        <input type="text" name="username_or_email" placeholder="E-mail or Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <div class="extra-links">
        <a href="#">Forgot your password?</a><br>
        <a href="customer_register.php">Sign up</a>
    </div>
</div>
<!-- Footer -->
<footer>
    <div class="footer-columns">
        <div>
            <h4>Company</h4>
            <a href="about.php">About</a>
            <a href="reviews.php">Reviews</a>
        </div>
        <div>
            <h4>Customer Service</h4>
            <a href="contact.php">Contact</a>
            <a href="faq.php">FAQ</a>
        </div>
    </div>
    <div class="social-icons">
        <a href="social.php?page=facebook">Facebook</a>
        <a href="social.php?page=instagram">Instagram</a>
    </div>
    <p>© 2025 Happy Sprays. All rights reserved.</p>
</footer>
</body>
</html>
