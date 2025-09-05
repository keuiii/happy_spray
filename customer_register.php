<?php
session_start();
require_once 'classes/database.php';
$db = Database::getInstance();

$msg = "";

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname  = trim($_POST['lastname']);
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $password  = trim($_POST['password']);

    if ($firstname == "" || $lastname == "" || $username == "" || $email == "" || $password == "") {
        $msg = "Please fill in all fields.";
    } else {
        $result = $db->registerCustomer($firstname, $lastname, $username, $email, $password);

        if ($result === true) {
            // Success → redirect to login page with success flag
            header("Location: customer_login.php?registered=1");
            exit;
        } else {
            $msg = $result; // Error message from DB
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customer Register - Happy Sprays</title>
<link rel="stylesheet" href="styles.css"> <!-- optional kung may global css ka -->
<style>
/* --- same CSS na gamit sa login para uniform look --- */
body {font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; background: #fff; color: #000;}
.top-nav {position: fixed; top: 0; left: 0; width: 100%; background: #fff; border-bottom: 1px solid #eee; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; font-family: 'Playfair Display', serif; font-size: 22px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; z-index: 1000;}
.top-nav .logo {flex: 1; text-align: center;}
.nav-actions {display: flex; align-items: center; gap: 20px; position: absolute; right: 20px; top: 50%; transform: translateY(-50%);}
.icon-btn, .cart-link, .profile-link {background: none; border: none; cursor: pointer;}
.sub-nav {position: fixed; top: 60px; left: 0; width: 100%; background: #fff; border-bottom: 1px solid #ccc; text-align: center; padding: 12px 0; z-index: 999; font-family: 'Playfair Display', serif; text-transform: uppercase; font-weight: 600; letter-spacing: 1px;}
.sub-nav a {margin: 0 20px; text-decoration: none; color: #000; font-size: 16px;}
.sub-nav a:hover {color: #555;}
.register-container {background: #f5f5f5; padding: 40px; border-radius: 12px; width: 360px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center; margin: 150px auto 60px;}
.register-container h2 {margin-bottom: 20px; font-size: 28px; font-weight: bold;}
.register-container input {width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;}
.register-container button {width: 100%; padding: 12px; border: 1px solid #000; border-radius: 8px; background: #fff; font-weight: bold; cursor: pointer; transition: 0.3s;}
.register-container button:hover {background: #000; color: #fff;}
.msg {color: red; font-size: 14px; margin-bottom: 10px;}
.extra-links {margin-top: 15px; font-size: 14px;}
.extra-links a {color: #000; text-decoration: none;}
.extra-links a:hover {text-decoration: underline;}
footer {background: #e9e9e9ff; border-top: 1px solid #eee; padding: 60px 20px 40px; text-align: center; font-size: 14px; color: #555;}
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
  <!-- Top Navbar -->
  <div class="top-nav">
    <div class="logo">Happy Sprays</div>
    <div class="nav-actions">
      <a href="cart.php" class="cart-link" title="Cart">🛒</a>
      <a href="customer_login.php" class="profile-link" title="Login">👤</a>
    </div>
  </div>

  <!-- Sub Navbar -->
  <div class="sub-nav">
    <a href="index.php">Home</a>
    <a href="index.php?gender=Male">For Him</a>
    <a href="index.php?gender=Female">For Her</a>
    <a href="contact.php">Contact</a>
  </div>

  <!-- Register Form -->
  <div class="register-container">
    <h2>Create Account</h2>
    <?php if($msg): ?><p class="msg"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
    <form method="post">
      <input type="text" name="firstname" placeholder="First Name" required>
      <input type="text" name="lastname" placeholder="Last Name" required>
      <input type="text" name="username" placeholder="Username" required>
      <input type="email" name="email" placeholder="Email Address" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Register</button>
    </form>
    <div class="extra-links">
      <a href="customer_login.php">Already have an account? Login</a>
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
