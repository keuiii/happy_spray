<?php
require_once 'classes/database.php';
session_start();

$conn = Database::getInstance()->getConnection();
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_or_email = trim($_POST['username_or_email']);
    $new_password      = trim($_POST['new_password']);
    $confirm_password  = trim($_POST['confirm_password']);

    if ($username_or_email === "" || $new_password === "" || $confirm_password === "") {
        $msg = "⚠️ Please fill in all fields.";
    } elseif ($new_password !== $confirm_password) {
        $msg = "❌ Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username_or_email, $username_or_email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $user['id']]);
            $msg = "✅ Password successfully updated! You can now login.";
        } else {
            $msg = "❌ User not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password - Happy Sprays</title>
<style>
body {font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; background: #fff; color: #333;}
.top-nav {position: fixed; top: 0; left: 0; width: 100%; background: #fff; border-bottom: 1px solid #eee; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; font-family: 'Playfair Display', serif; font-size: 22px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; z-index: 1000;}
.top-nav .logo { flex: 1; text-align: center; }
.sub-nav {position: fixed; top: 60px; left: 0; width: 100%; background: #fff; border-bottom: 1px solid #ccc; text-align: center; padding: 12px 0; z-index: 999; font-family: 'Playfair Display', serif; text-transform: uppercase; font-weight: 600; letter-spacing: 1px;}
.sub-nav a { margin: 0 20px; text-decoration: none; color: #000; font-size: 16px; }
.sub-nav a:hover { color: #555; }
/* Reset Form */
.reset-container {background: #e9e9e9ff; padding: 50px; border-radius: 12px; width: 420px; box-shadow: 0 6px 15px rgba(0,0,0,0.15); text-align: center; margin: 180px auto 80px;}
.reset-container h2 {margin-bottom: 25px; font-size: 30px; font-weight: bold;}
.reset-container input {width: 100%; padding: 15px; margin: 12px 0; border: 1px solid #ccc; border-radius: 10px; font-size: 16px; box-sizing: border-box;}
.reset-container button {width: 100%; padding: 15px; border: 1px solid #000; border-radius: 10px; background: #fff; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 10px;}
.reset-container button:hover {background: #000; color: #fff;}
.msg {font-size: 14px; margin-bottom: 12px;}
.msg.error {color: red;}
.msg.success {color: green;}
.extra-links {margin-top: 18px; font-size: 14px;}
.extra-links a {color: #000; text-decoration: none;}
.extra-links a:hover {text-decoration: underline;}
/* Footer */
footer {background: #e9e9e9ff; border-top: 1px solid #eee; padding: 40px 20px; text-align: center; font-size: 14px; color: #555;}
.footer-columns {display: flex; justify-content: center; gap: 100px; margin-bottom: 20px;}
.footer-columns h4 {font-size: 16px; margin-bottom: 10px; font-weight: bold; color: #000;}
.footer-columns a {display: block; text-decoration: none; color: #555; margin: 5px 0;}
.footer-columns a:hover {color: #000;}
.social-icons {margin-top: 15px;}
.social-icons a {margin: 0 8px; color: #555; text-decoration: none; font-size: 18px;}
.social-icons a:hover {color: #000;}
</style>
</head>
<body>
<!-- Navbar -->
<div class="top-nav">
    <div class="logo">Happy Sprays</div>
</div>
<!-- Sub Navbar -->
<div class="sub-nav">
    <a href="index.php">Home</a>
    <a href="index.php?gender=Male">For Him</a>
    <a href="index.php?gender=Female">For Her</a>
    <a href="contact.php">Contact</a>
</div>
<!-- Reset Form -->
<div class="reset-container">
    <h2>Reset Password</h2>
    <?php if($msg): ?>
        <p class="msg <?= strpos($msg,'✅')!==false ? 'success' : 'error' ?>"><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="username_or_email" placeholder="E-mail or Username" required>
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit">Update Password</button>
    </form>
    <div class="extra-links">
        <a href="customer_login.php">← Back to Login</a>
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
