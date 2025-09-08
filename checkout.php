<?php
session_start();
require_once "classes/database.php";

$db = Database::getInstance();

// Redirect if cart is empty
if ($db->isCartEmpty()) {
    header("Location: cart.php?error=empty_cart");
    exit;
}

// Get checkout summary using centralized method
$checkoutSummary = $db->getCheckoutSummary();
$cart_items = $checkoutSummary['items'];
$grand_total = $checkoutSummary['total'];
$item_count = $checkoutSummary['item_count'];

// Pre-fill form if user is logged in
$user_data = [];
if ($db->isLoggedIn()) {
    // You can extend this to get user details from database
    $user_data = [
        'name' => $_SESSION['customer_name'] ?? '',
        'email' => $_SESSION['customer_email'] ?? ''
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Happy Sprays</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #fff;
            margin: 20px;
            color: #000;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .back-btn {
            display: inline-block;
            margin: 10px 0 20px 20px;
            padding: 8px 16px;
            border: 1px solid #000;
            text-decoration: none;
            color: #000;
            border-radius: 4px;
            transition: all 0.2s;
        }
        .back-btn:hover {
            background: #000;
            color: #fff;
        }
        .checkout-container {
            width: 80%;
            margin: auto;
            display: flex;
            gap: 30px;
        }
        .form-section, .summary-section {
            flex: 1;
            border: 1px solid #000;
            padding: 20px;
            border-radius: 4px;
        }
        .form-section h2, .summary-section h2 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #000;
            border-radius: 4px;
            background: none;
            box-sizing: border-box;
        }
        .required {
            color: red;
        }
        .place-order-btn {
            display: block;
            width: 100%;
            padding: 12px;
            border: 1px solid #000;
            background: none;
            color: #000;
            font-size: 16px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .place-order-btn:hover {
            background: #000;
            color: #fff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }
        th, td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
        }
        th {
            background: #f2f2f2;
            font-weight: bold;
        }
        img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border: 1px solid #000;
            border-radius: 4px;
        }
        .gcash-info {
            display: none;
            padding: 15px;
            border: 1px solid #28a745;
            border-radius: 6px;
            margin-top: 10px;
            font-size: 14px;
            background: #f8f9fa;
        }
        .gcash-info img {
            width: 150px;
            margin: 10px 0;
            border: 1px solid #000;
            border-radius: 6px;
        }
        .gcash-info h3 {
            color: #28a745;
            margin-top: 0;
        }

        /* Popup styles */
        .popup {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .popup-content {
            background: #fff;
            padding: 30px;
            border-radius: 6px;
            text-align: center;
            max-width: 400px;
            border: 1px solid #000;
        }
        .popup button {
            margin: 10px;
            padding: 10px 20px;
            border: 1px solid #000;
            background: none;
            cursor: pointer;
            font-size: 14px;
            border-radius: 4px;
            transition: all 0.2s;
        }
        .popup button:hover {
            background: #000;
            color: #fff;
        }
        .hidden { display: none; }
        
        .order-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        
        .item-count {
            color: #666;
            font-size: 14px;
            text-align: center;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .checkout-container {
                flex-direction: column;
                width: 95%;
            }
        }
    </style>
    <script>
        function togglePaymentDetails() {
            let payment = document.getElementById("payment").value;
            let gcashInfo = document.getElementById("gcash-info");

            if (payment === "gcash") {
                gcashInfo.style.display = "block";
                document.getElementById("gcash_ref").setAttribute("required", "required");
            } else {
                gcashInfo.style.display = "none";
                document.getElementById("gcash_ref").removeAttribute("required");
            }
        }

        function checkAuthBeforePlaceOrder() {
            let loggedIn = <?= $db->isLoggedIn() ? 'true' : 'false' ?>;
            if (!loggedIn) {
                document.getElementById('authPopup').classList.remove('hidden');
                return false; // stop submit
            }
            
            // Additional client-side validation
            let requiredFields = ['name', 'email', 'street', 'city', 'province', 'postal'];
            for (let field of requiredFields) {
                let element = document.getElementById(field);
                if (!element.value.trim()) {
                    alert(`Please fill in ${field.charAt(0).toUpperCase() + field.slice(1)}`);
                    element.focus();
                    return false;
                }
            }
            
            return true;
        }
        
        function closePopup() {
            document.getElementById('authPopup').classList.add('hidden');
        }
    </script>
</head>
<body>
    <a href="cart.php" class="back-btn">← Back to Cart</a>
    <h1>Checkout</h1>

    <!-- Auth Popup -->
    <div id="authPopup" class="popup hidden">
        <div class="popup-content">
            <h2>Login or Create an Account</h2>
            <p>To track the status of your order, please log in or create an account.</p>
            <div>
                <button onclick="location.href='customer_login.php'">Login</button>
                <button onclick="location.href='customer_register.php'">Create Account</button>
                <button onclick="closePopup()">Continue as Guest</button>
            </div>
        </div>
    </div>

    <div class="checkout-container">
        <!-- Checkout Form -->
        <div class="form-section">
            <h2>Delivery Information</h2>
            
            <div class="order-info">
                <strong>Order Summary:</strong> <?= $item_count ?> item(s) totaling ₱<?= number_format($grand_total, 2) ?>
            </div>
            
            <form action="place_order.php" method="POST" enctype="multipart/form-data">
                <label for="name">Full Name <span class="required">*</span></label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($user_data['name'] ?? '') ?>" required>

                <label for="email">Email Address <span class="required">*</span></label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" required>

                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" placeholder="09XXXXXXXXX">

                <label for="street">Street / Barangay <span class="required">*</span></label>
                <input type="text" id="street" name="street" required>

                <label for="city">City / Municipality <span class="required">*</span></label>
                <input type="text" id="city" name="city" required>

                <label for="province">Province <span class="required">*</span></label>
                <input type="text" id="province" name="province" required>

                <label for="postal">Postal Code <span class="required">*</span></label>
                <input type="text" id="postal" name="postal" pattern="[0-9]{4}" placeholder="1234" required>

                <label for="payment">Payment Method <span class="required">*</span></label>
                <select id="payment" name="payment" required onchange="togglePaymentDetails()">
                    <option value="">-- Select Payment Method --</option>
                    <option value="cod">Cash on Delivery</option>
                    <option value="gcash">GCash</option>
                </select>

                <!-- GCash details -->
                <div id="gcash-info" class="gcash-info">
                    <h3>💳 Pay with GCash</h3>
                    <p><strong>Step 1:</strong> Scan this QR code or send to the number below</p>
                    <img src="images/qrfake.png" alt="GCash QR">
                    <p><strong>GCash Number:</strong> 09451038854</p>
                    <p><strong>Account Name:</strong> Happy Sprays</p>
                    <p><strong>Amount:</strong> ₱<?= number_format($grand_total, 2) ?></p>
                    <hr>
                    <p><strong>Step 2:</strong> Upload screenshot of payment confirmation</p>
                    <label for="gcash_ref">Proof of Payment <span class="required">*</span></label>
                    <input type="file" id="gcash_ref" name="gcash_ref" accept="image/*">
                    <small style="color: #666;">Accepted formats: JPG, PNG (Max 5MB)</small>
                </div>

                <button type="submit" class="place-order-btn" onclick="return checkAuthBeforePlaceOrder()">
                    Place Order - ₱<?= number_format($grand_total, 2) ?>
                </button>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="summary-section">
            <h2>Order Summary</h2>
            
            <div class="item-count">
                <?= $item_count ?> item(s) in your order
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Perfume</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td>
                            <img src="images/<?= htmlspecialchars($item['image']) ?>" 
                                 alt="<?= htmlspecialchars($item['name']) ?>" 
                                 onerror="this.src='images/placeholder.jpg'">
                        </td>
                        <td style="text-align: left;">
                            <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                            <small>₱<?= number_format($item['price'], 2) ?> each</small>
                        </td>
                        <td><?= $item['quantity'] ?></td>
                        <td><strong>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background: #f8f9fa;">
                        <th colspan="3">Grand Total</th>
                        <th style="color: #007bff; font-size: 18px;">₱<?= number_format($grand_total, 2) ?></th>
                    </tr>
                </tfoot>
            </table>
            
            <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 4px; font-size: 12px; color: #666;">
                <strong>Note:</strong> Orders are processed within 1-2 business days. 
                You will receive an email confirmation once your order is placed.
            </div>
        </div>
    </div>
</body>
</html>