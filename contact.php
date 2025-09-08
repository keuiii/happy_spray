<?php
// contact.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Contact Us - Happy Sprays</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
* {margin:0; padding:0; box-sizing:border-box;}
body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background:#fff;
  color:#000;
  line-height:1.6;
}

.top-nav {
  position: fixed;
  top: 0; left: 0;
  width: 100%;
  background: #fff;
  border-bottom: 1px solid #eee;
  padding: 10px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-family: 'Playfair Display', serif;
  font-size: 22px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
  z-index: 1000;
}
.top-nav .logo { flex: 1; text-align: center; }

.sub-nav {
  position: fixed;
  top: 60px; left: 0;
  width: 100%;
  background: #fff;
  border-bottom: 1px solid #ccc;
  text-align: center;
  padding: 12px 0;
  z-index: 999;
  font-family: 'Playfair Display', serif;
  text-transform: uppercase;
  font-weight: 600;
  letter-spacing: 1px;
}
.sub-nav a {
  margin: 0 20px;
  text-decoration: none;
  color: #000;
  font-size: 16px;
}
.sub-nav a:hover { color:#555; }

.contact-section {
  max-width: 700px;
  margin: 150px auto 60px;
  padding: 20px;
  text-align: center;
}
.contact-section h1 {
  font-family: 'Playfair Display', serif;
  font-size: 42px;
  margin-bottom: 20px;
}
.contact-links {
  margin-bottom: 25px;
  font-size: 16px;
}
.contact-links a {
  text-decoration: none;
  color: #000;
  margin: 0 8px;
}
.contact-links a:hover { color:#666; }

.contact-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}
.contact-form input,
.contact-form textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 15px;
}
.contact-form button {
  background: #000;
  color: #fff;
  padding: 12px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
  font-weight: bold;
  transition: background 0.3s;
}
.contact-form button:hover {
  background: #444;
}

footer {
    background: #e9e9e9;
    border-top: 1px solid #eee;
    padding: 40px 20px;
    text-align: center;
    font-size: 14px;
    color: #555;
    margin-top: 60px;
}
.footer-columns {
    display: flex;
    justify-content: center;
    gap: 100px;
    margin-bottom: 20px;
}
.footer-columns h4 {
    font-size: 16px;
    margin-bottom: 10px;
    font-weight: bold;
    color: #000;
}
.footer-columns a {
    display: block;
    text-decoration: none;
    color: #555;
    margin: 5px 0;
}
.footer-columns a:hover { color: #000; }
.social-icons { margin-top: 15px; }
.social-icons a {
    margin: 0 8px;
    color: #555;
    text-decoration: none;
    font-size: 18px;
}
.social-icons a:hover { color: #000; }
</style>
</head>
<body>

<!-- Navbar -->
<div class="top-nav">
  <div class="logo">Happy Sprays</div>
</div>
<div class="sub-nav">
  <a href="index.php">Home</a>
  <a href="index.php?gender=Male">For Him</a>
  <a href="index.php?gender=Female">For Her</a>
  <a href="contact.php">Contact</a>
</div>

<!-- Contact Section -->
<section class="contact-section" id="contact">
    <h1>Contact Us</h1>
    <div class="contact-links">
        <a href="https://www.facebook.com/thethriftbytf" target="_blank">Facebook</a> | 
        <a href="https://www.instagram.com/thehappysprays/" target="_blank">Instagram</a>
    </div>
    <form action="contact_submit.php" method="post" class="contact-form">
        <input type="text" name="name" placeholder="Your Name" required>
        <input type="email" name="email" placeholder="Your Email" required>
        <textarea name="message" placeholder="Your Message" rows="4" required></textarea>
        <button type="submit">Send Message</button>
    </form>
</section>

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
            <a href="faq.php">FAQ</a>
            <a href="contact.php">Contact</a>
        </div>
    </div>
    <div class="social-icons">
        <a href="https://www.facebook.com/thethriftbytf" target="_blank">Facebook</a>
        <a href="https://www.instagram.com/thehappysprays/" target="_blank">Instagram</a>
    </div>
    <p>© 2025 Happy Sprays. All rights reserved.</p>
</footer>

</body>
</html>