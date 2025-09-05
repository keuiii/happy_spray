<?php
session_start();
require_once 'classes/database.php';
$db = Database::getInstance();

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username === "" || $password === "") {
        $msg = "Please fill in all fields.";
    } else {
        // ✅ Use centralized DB method
        $user = $db->loginUser($username, $password);

        if ($user) {
            if ($user['role'] === "admin") {
                // Admin login success
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                header("Location: admin_dashboard.php");
                exit;
            } else {
                // Not an admin, reject login here
                $msg = "This login page is for admins only. Please use the Customer Login page.";
            }
        } else {
            $msg = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - Happy Sprays</title>
<style>
    body {font-family:'Segoe UI',sans-serif; background:#f4f4f4; margin:0; display:flex; height:100vh; align-items:center; justify-content:center;}
    .login-box {background:#fff; padding:30px; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.1); width:320px;}
    h2 {margin:0 0 20px; text-align:center; font-weight:600;}
    input {width:100%; padding:10px; margin:8px 0; border:1px solid #ccc; border-radius:6px;}
    button {width:100%; padding:10px; border:none; background:#333; color:#fff; font-weight:600; border-radius:6px; cursor:pointer;}
    button:hover {background:#000;}
    .msg {font-size:14px; text-align:center; margin-bottom:10px; color:red;}
    .register-link {display:block; text-align:center; margin-top:12px; font-size:14px;}
</style>
</head>
<body>
    <form class="login-box" method="post">
        <h2>Login</h2>
        <?php if($msg): ?><p class="msg"><?= $msg ?></p><?php endif; ?>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
        <a href="register.php" class="register-link">Create Account</a>
    </form>
</body>
</html>