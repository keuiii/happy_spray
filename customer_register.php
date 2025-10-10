<?php
session_start();
require_once 'classes/database.php';

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$db = Database::getInstance();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $firstname = trim($_POST['firstname']);
    $lastname  = trim($_POST['lastname']);
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($firstname) || empty($lastname) || empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        try {
            // Check if email or username already exists
            $existing = $db->fetch(
                "SELECT customer_id FROM customers WHERE customer_username = ? OR customer_email = ? LIMIT 1",
                [$username, $email]
            );

            if ($existing) {
                $error = "Username or Email already exists.";
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                
                // Generate OTP
                $otp = rand(100000, 999999);
                $expiry = date("Y-m-d H:i:s", strtotime("+100 seconds"));
                
                // Insert new customer (NOT VERIFIED YET)
                $customer_id = $db->insert(
                    "INSERT INTO customers 
                    (customer_firstname, customer_lastname, customer_username, customer_email, customer_password, verification_code, otp_expires, is_verified, cs_created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())",
                    [$firstname, $lastname, $username, $email, $hashedPassword, $otp, $expiry]
                );

                if ($customer_id) {
                    // Store pending email in session for OTP verification
                    $_SESSION['pending_email'] = $email;
                    $_SESSION['pending_customer_id'] = $customer_id;

                    // Send OTP Email via PHPMailer
                    try {
                        $mail = new PHPMailer(true);
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.hostinger.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'happyspray@happyspray.shop';
                        $mail->Password   = 'JANJANbuen@5';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                        $mail->Port       = 465;

                        $mail->setFrom('happyspray@happyspray.shop', 'Happy Sprays');
                        $mail->addAddress($email, $firstname . ' ' . $lastname);

                        $mail->isHTML(true);
                        $mail->Subject = 'Verify Your Email - Happy Sprays';
                        $mail->Body    = "
                        <html>
                        <body style='font-family: Arial, sans-serif;'>
                            <h2>Welcome to Happy Sprays!</h2>
                            <p>Hello <strong>{$firstname} {$lastname}</strong>,</p>
                            <p>Thank you for registering. Please use the following OTP code to verify your email:</p>
                            <div style='background: #f5f5f5; padding: 20px; text-align: center; margin: 20px 0;'>
                                <h1 style='color: #000; letter-spacing: 5px;'>{$otp}</h1>
                            </div>
                            <p><strong>This code will expire in 100 seconds.</strong></p>
                            <p>If you didn't create this account, please ignore this email.</p>
                            <br>
                            <p>Best regards,<br>Happy Sprays Team</p>
                        </body>
                        </html>
                        ";

                        $mail->send();
                        
                        // Redirect to OTP verification page
                        header("Location: verify_otp.php");
                        exit;
                        
                    } catch (Exception $e) {
                        // If email fails, still show OTP for testing
                        $error = "Registration successful but email could not be sent.<br>Your OTP code is: <strong>{$otp}</strong><br>Error: {$mail->ErrorInfo}";
                    }
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
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
<title>Register - Happy Sprays</title>
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

.register-container {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    max-width: 900px;
    width: 100%;
    display: grid;
    grid-template-columns: 1fr 1fr;
}

.register-left {
    background: #fff;
    border-right: 1px solid #eee;
    padding: 60px 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
}

.register-left h1 {
    font-family: 'Playfair Display', serif;
    font-size: 42px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #111;
    margin-bottom: 25px;
}

.register-left p {
    font-size: 16px;
    line-height: 1.6;
    color: #333;
    max-width: 350px;
}

.register-right {
    background: #f9f9f9;
    padding: 60px 40px;
    max-height: 90vh;
    overflow-y: auto;
}

.register-header {
    margin-bottom: 30px;
}

.register-header h2 {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    margin-bottom: 10px;
    color: #111;
}

.register-header p {
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

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.register-btn {
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

.register-btn:hover {
    background: #333;
    transform: translateY(-1px);
}

.login-link {
    text-align: center;
    margin-top: 20px;
    color: #555;
}

.login-link a {
    color: #000;
    text-decoration: none;
    font-weight: 600;
}

.login-link a:hover {
    text-decoration: underline;
}

.password-hint {
    font-size: 12px;
    color: #777;
    margin-top: 5px;
}

@media (max-width: 768px) {
    .register-container {
        grid-template-columns: 1fr;
    }
    
    .register-left {
        padding: 40px 30px;
        border-right: none;
        border-bottom: 1px solid #eee;
    }
    
    .register-right {
        padding: 40px 30px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}

</style>
</head>
<body>

<div class="register-container">
    <div class="register-left">
    <h1>HAPPY SPRAYS</h1>
    <p>Join our community and discover exclusive fragrances. Create an account to enjoy personalized recommendations, track your orders, and get special offers!</p>
</div>

    
    <div class="register-right">
        <div class="register-header">
            <h2>Create Account</h2>
            <p>Fill in your details to get started</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message"><?= $success ?></div>
        <?php endif; ?>
        
        <form method="POST" action="customer_register.php">
            <div class="form-row">
                <div class="form-group">
                    <label for="firstname">First Name *</label>
                    <input type="text" 
                           id="firstname" 
                           name="firstname" 
                           placeholder="Juan"
                           value="<?= htmlspecialchars($_POST['firstname'] ?? '') ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="lastname">Last Name *</label>
                    <input type="text" 
                           id="lastname" 
                           name="lastname" 
                           placeholder="Dela Cruz"
                           value="<?= htmlspecialchars($_POST['lastname'] ?? '') ?>"
                           required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="username">Username *</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       placeholder="Choose a unique username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       placeholder="your.email@example.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required>
            </div>
            
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="Create a strong password"
                       required>
                <div class="password-hint">Must be at least 6 characters long</div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <input type="password" 
                       id="confirm_password" 
                       name="confirm_password" 
                       placeholder="Re-enter your password"
                       required>
            </div>
            
            <button type="submit" name="register" class="register-btn">Create Account</button>
            
            <div class="login-link">
                Already have an account? <a href="customer_login.php">Login here</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>