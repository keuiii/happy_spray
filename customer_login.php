<?php
session_start();
require_once 'classes/database.php';

// Move PHPMailer imports to top
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

$db = Database::getInstance();

// If already logged in, redirect
if ($db->isLoggedIn()) {
    $role = $db->getCurrentUserRole();
    if ($role === 'customer') {
        header("Location: index.php");
    } else {
        header("Location: admin_dashboard.php");
    }
    exit;
}

$error = '';
$success = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $usernameOrEmail = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($usernameOrEmail) || empty($password)) {
        $error = "Please enter both username/email and password.";
    } 
    // ✅ HARD-CODED ADMIN LOGIN CHECK
    elseif ($usernameOrEmail === 'admin' && $password === 'admin123') {
        $_SESSION['role'] = 'admin';
        $_SESSION['admin_username'] = 'admin';
        header("Location: admin_dashboard.php");
        exit;
    } 
    // ✅ CUSTOMER LOGIN (via database)
    else {
        try {
            // First, check if user exists and get their info
            $customer = $db->fetch(
                "SELECT * FROM customers WHERE customer_username = ? OR customer_email = ? LIMIT 1",
                [$usernameOrEmail, $usernameOrEmail]
            );
            
            if (!$customer) {
                $error = "Invalid username/email or password.";
            } 
            // ✅ CHECK PASSWORD FIRST (before checking verification)
            elseif (!password_verify($password, $customer['customer_password'])) {
                $error = "Invalid username/email or password.";
            }
            // ✅ CHECK IF EMAIL IS VERIFIED
            elseif ($customer['is_verified'] == 0) {
                // Check if OTP has expired
                $otp_expired = false;
                if (!empty($customer['otp_expires'])) {
                    $otp_expired = strtotime($customer['otp_expires']) < time();
                }
                
                if ($otp_expired || empty($customer['verification_code'])) {
                    // Generate new OTP
                    $new_otp = rand(100000, 999999);
                    $new_expiry = date("Y-m-d H:i:s", strtotime("+100 seconds"));
                    
                    $db->update(
                        "UPDATE customers SET verification_code = ?, otp_expires = ? WHERE customer_id = ?",
                        [$new_otp, $new_expiry, $customer['customer_id']]
                    );
                    
                    // Send new OTP
                    try {
                        $mail = new PHPMailer(true);
                        $mail->isSMTP();
                        $mail->Host = 'smtp.hostinger.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'happyspray@happyspray.shop';
                        $mail->Password = 'JANJANbuen@5';  // Use consistent password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                        $mail->Port = 465;
                        
                        $mail->setFrom('happyspray@happyspray.shop', 'Happy Sprays');
                        $mail->addAddress($customer['customer_email'], $customer['customer_firstname'] . ' ' . $customer['customer_lastname']);
                        
                        $mail->isHTML(true);
                        $mail->Subject = 'New Verification Code - Happy Sprays';
                        $mail->Body = "
                        <html>
                        <body style='font-family: Arial, sans-serif;'>
                            <h2>New Verification Code</h2>
                            <p>Hello <strong>{$customer['customer_firstname']} {$customer['customer_lastname']}</strong>,</p>
                            <p>Your OTP expired. Here is your new code:</p>
                            <div style='background: #f5f5f5; padding: 20px; text-align: center; margin: 20px 0;'>
                                <h1 style='color: #000; letter-spacing: 5px;'>{$new_otp}</h1>
                            </div>
                            <p><strong>This code will expire in 100 seconds.</strong></p>
                            <br>
                            <p>Best regards,<br>Happy Sprays Team</p>
                        </body>
                        </html>
                        ";
                        
                        $mail->send();
                        $success = "A new verification code has been sent to your email!";
                    } catch (MailException $e) {
                        error_log("Failed to send new OTP: " . $e->getMessage());
                        $error = "Failed to send verification code. Your OTP is: <strong>{$new_otp}</strong>";
                    }
                }
                
                // Set session for OTP page
                $_SESSION['pending_email'] = $customer['customer_email'];
                $_SESSION['pending_customer_id'] = $customer['customer_id'];
                
                // Redirect to OTP page after 2 seconds if success
                if (!empty($success)) {
                    header("refresh:2;url=verify_otp.php");
                } else {
                    header("Location: verify_otp.php");
                    exit;
                }
            }
            // ✅ LOGIN SUCCESS
            else {
                // Set session variables
                $_SESSION['role'] = 'customer';
                $_SESSION['customer_id'] = $customer['customer_id'];
                $_SESSION['customer_username'] = $customer['customer_username'];
                $_SESSION['customer_email'] = $customer['customer_email'];
                $_SESSION['customer_firstname'] = $customer['customer_firstname'];
                $_SESSION['customer_lastname'] = $customer['customer_lastname'];
                
                // Redirect
                $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
                header("Location: " . $redirect);
                exit;
            }
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "Login failed. Please try again.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Happy Sprays</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
* {margin:0; padding:0; box-sizing:border-box;}
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #fff;
    color: #111;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
}

.login-container {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    max-width: 900px;
    width: 100%;
    display: grid;
    grid-template-columns: 1fr 1fr;
}

.login-left {
    background: #fff;
    border-right: 1px solid #eee;
    padding: 60px 40px;
    color: #111;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
}

.login-left h1 {
    font-family: 'Playfair Display', serif;
    font-size: 36px;
    margin-bottom: 20px;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: #111;
}

.login-right {
    background: #f9f9f9;
    padding: 60px 40px;
    max-height: 90vh;
    overflow-y: auto;
}

.login-header {
    margin-bottom: 30px;
}

.login-header h2 {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    margin-bottom: 10px;
    color: #111;
}

.login-header p {
    color: #555;
}

.error-message {
    background: #ffebee;
    color: #c62828;
    padding: 12px;
    border-radius: 5px;
    margin-bottom: 20px;
    border-left: 4px solid #c62828;
}

.success-message {
    background: #e8f5e9;
    color: #2e7d32;
    padding: 12px;
    border-radius: 5px;
    margin-bottom: 20px;
    border-left: 4px solid #2e7d32;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #222;
}

.form-group input {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
    transition: 0.3s;
    background: #fff;
}

.form-group input:focus {
    outline: none;
    border-color: #111;
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 5px;
}

.forgot-link {
    color: #000;
    text-decoration: none;
    font-size: 14px;
}

.forgot-link:hover {
    text-decoration: underline;
}

.login-btn {
    width: 100%;
    padding: 14px;
    background: #111;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}

.login-btn:hover {
    background: #333;
    transform: translateY(-1px);
}

.register-link {
    text-align: center;
    margin-top: 20px;
    color: #555;
}

.register-link a {
    color: #000;
    text-decoration: none;
    font-weight: 600;
}

.register-link a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .login-container {
        grid-template-columns: 1fr;
    }
    
    .login-left {
        padding: 40px 30px;
        border-right: none;
        border-bottom: 1px solid #eee;
    }
    
    .login-right {
        padding: 40px 30px;
    }
}

</style>
</head>
<body>

<div class="login-container">
    <div class="login-left">
        <h1>Happy Sprays</h1>
        <p>Welcome back! Sign in to continue exploring our premium fragrances, track your orders, and manage your account with ease.</p>
    </div>

    
    <div class="login-right">
        <div class="login-header">
            <h2>Welcome Back</h2>
            <p>Please login to your account</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="customer_login.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>">
            <div class="form-group">
                <label for="username">Email or Username</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       placeholder="Enter your email or username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="Enter your password"
                       required>
            </div>
            
            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember">
                    <span>Remember me</span>
                </label>
                <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
            </div>
            
            <button type="submit" name="login" class="login-btn">Login</button>
            
            <div class="register-link">
                Don't have an account? <a href="customer_register.php">Register here</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>