<?php
session_start();
require_once "classes/database.php";
$db = Database::getInstance();

// --- ADD TO CART ---
if (isset($_POST['add_to_cart'])) {
    $db->addToCart(
        $_POST['id'],
        $_POST['name'] ?? '',
        $_POST['price'] ?? 0,
        $_POST['image'] ?? '', // pass full DB file_path here
        $_POST['qty'] ?? 1
    );

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        echo "success";
        exit;
    }
}

// --- UPDATE QUANTITY ---
if (isset($_POST['update_qty'])) {
    $db->updateCartQuantity($_POST['id'], $_POST['quantity']);
    header("Location: cart.php");
    exit;
}

// --- REMOVE ITEM ---
if (isset($_GET['remove'])) {
    $db->removeFromCart($_GET['remove']);
    header("Location: cart.php");
    exit;
}

$cart = $_SESSION['cart'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Cart - Happy Sprays</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
* {margin: 0; padding: 0; box-sizing: border-box;}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #fff;
    color: #000;
}

/* Top Navigation */
.top-nav {
    background: #fff;
    border-bottom: 2px solid #000;
    padding: 20px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 100;
}

.logo {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    text-transform: uppercase;
    letter-spacing: 3px;
    font-weight: 700;
}

.logo a {
    color: #000;
    text-decoration: none;
}

.nav-icons {
    display: flex;
    align-items: center;
    gap: 30px;
}

.nav-icon {
    position: relative;
    font-size: 24px;
    color: #000;
    text-decoration: none;
    transition: transform 0.2s;
    cursor: pointer;
}

.nav-icon:hover {
    transform: scale(1.1);
}

.logout-btn {
    background: #000;
    color: #fff;
    padding: 12px 24px;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s;
    border: 2px solid #000;
}

.logout-btn:hover {
    background: #fff;
    color: #000;
}

.cart-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #ff0000;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 50%;
    min-width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.back-btn {
    display: inline-block;
    margin-bottom: 30px;
    padding: 12px 24px;
    border: 2px solid #000;
    text-decoration: none;
    color: #000;
    font-weight: 600;
    transition: 0.2s;
}

.back-btn:hover {
    background: #000;
    color: #fff;
}

h1 {
    font-family: 'Playfair Display', serif;
    text-align: center;
    font-size: 42px;
    margin-bottom: 50px;
    text-transform: uppercase;
    letter-spacing: 2px;
    border-bottom: 3px solid #000;
    padding-bottom: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 40px;
    background: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: 2px solid #000;
}

th {
    background: #fff;
    color: #000;
    padding: 18px 15px;
    text-align: center;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 14px;
    border: 2px solid #000;
}

td {
    padding: 20px 15px;
    text-align: center;
    border: 2px solid #000;
    vertical-align: middle;
}

tr:hover {
    background: #f8f8f8;
}

img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid #000;
    box-shadow: 2px 2px 0 #000;
}

.product-name {
    font-weight: 600;
    font-size: 16px;
}

.price-cell {
    font-weight: 700;
    font-size: 18px;
}

.qty-input {
    width: 80px;
    padding: 10px;
    text-align: center;
    border: 2px solid #000;
    border-radius: 5px;
    font-size: 16px;
    font-weight: 600;
    background: #fff;
    margin: 0 10px;
}

.qty-input:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1);
}

.qty-btn {
    width: 40px;
    height: 40px;
    background: #fff;
    color: #000;
    border: 2px solid #000;
    font-size: 20px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.qty-btn:hover {
    background: #000;
    color: #fff;
}

.qty-btn:active {
    transform: scale(0.95);
}

.qty-btn.minus {
    border-radius: 50%;
}

.qty-btn.plus {
    border-radius: 50%;
}

.qty-form {
    display: flex;
    gap: 0;
    justify-content: center;
    align-items: center;
}

.remove-btn {
    padding: 10px 20px;
    background: #000;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    border: 2px solid #000;
    font-weight: 600;
    transition: 0.2s;
    display: inline-block;
}

.remove-btn:hover {
    background: #fff;
    color: #000;
}

.grand-total-row {
    background: #f5f5f5;
    border-top: 3px solid #000;
}

.grand-total-row th {
    background: #fff;
    color: #000;
    font-size: 20px;
    padding: 25px;
    border: 2px solid #000;
}

.grand-total-amount {
    font-size: 28px;
    font-weight: 700;
}

.checkout-btn {
    display: block;
    width: 300px;
    margin: 40px auto;
    padding: 18px;
    border: 2px solid #000;
    background: #000;
    color: #fff;
    text-align: center;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 700;
    font-size: 16px;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: 0.2s;
}

.checkout-btn:hover {
    background: #fff;
    color: #000;
}

.empty {
    text-align: center;
    font-size: 20px;
    margin-top: 100px;
    color: #666;
}

.empty-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .top-nav {
        padding: 15px 20px;
    }
    
    .logo {
        font-size: 20px;
    }
    
    .nav-icons {
        gap: 20px;
    }
    
    .nav-icon {
        font-size: 20px;
    }
    
    table {
        font-size: 12px;
    }
    
    th, td {
        padding: 10px 5px;
    }
    
    img {
        width: 60px;
        height: 60px;
    }
    
    h1 {
        font-size: 28px;
    }
    
    .qty-input {
        width: 60px;
        padding: 8px;
    }
    
    .qty-btn {
        width: 35px;
        height: 40px;
        font-size: 18px;
    }
    
    .checkout-btn {
        width: 90%;
    }
}
</style>
</head>
<body>

<!-- Top Navigation -->
<div class="top-nav">
    <div class="logo">
        <a href="index.php">Happy Sprays</a>
    </div>
    <div class="nav-icons">
        <a href="index.php" class="nav-icon" title="Shop">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
            </svg>
        </a>
        <a href="cart.php" class="nav-icon" title="Cart">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <?php 
            $cart_count = 0;
            if (!empty($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $item) {
                    $cart_count += $item['quantity'];
                }
            }
            if ($cart_count > 0): 
            ?>
            <span class="cart-badge"><?= $cart_count ?></span>
            <?php endif; ?>
        </a>
        <?php if(isset($_SESSION['customer_id'])): ?>
        <a href="customer_dashboard.php" class="nav-icon" title="My Account">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </a>
        <a href="customer_logout.php" class="logout-btn" title="Logout">LOGOUT</a>
        <?php else: ?>
        <a href="customer_login.php" class="nav-icon" title="Login">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <a href="index.php" class="back-btn">← Back to Shop</a>
    <h1>My Cart</h1>

<script>
function updateQty(itemId, change) {
    const input = document.getElementById('qty' + itemId);
    const currentQty = parseInt(input.value);
    const newQty = currentQty + change;
    
    if (newQty >= 1) {
        input.value = newQty;
        document.getElementById('qtyForm' + itemId).submit();
    }
}
</script>

<?php if (!empty($cart)): ?>
<table>
    <tr>
        <th>Image</th>
        <th>Perfume</th>
        <th>Price</th>
        <th>Qty</th>
        <th>Total</th>
        <th>Action</th>
    </tr>
    <?php 
    $grand_total = 0;
    foreach ($cart as $id => $item): 
        $total = $item['price'] * $item['quantity'];
        $grand_total += $total;

        // Fetch actual product image from database
        $productData = $db->fetch("
            SELECT p.perfume_name, i.file_path 
            FROM perfumes p
            LEFT JOIN images i ON p.perfume_id = i.perfume_id
            WHERE p.perfume_id = ?
            LIMIT 1
        ", [$id]);
        
        // Determine image path - use database image or fallback
        $imgPath = 'images/DEFAULT.png';
        if ($productData && !empty($productData['file_path'])) {
            $imgPath = $productData['file_path'];
        } elseif (!empty($item['image'])) {
            $imgPath = $item['image'];
        }
    ?>
    <tr>
        <td>
            <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($item['name']) ?>" onerror="this.src='images/DEFAULT.png'">
        </td>
        <td class="product-name"><?= htmlspecialchars($item['name']) ?></td>
        <td class="price-cell">₱<?= number_format($item['price'], 2) ?></td>
        <td>
            <form method="post" class="qty-form" id="qtyForm<?= htmlspecialchars($id) ?>">
                <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                <input type="hidden" name="update_qty" value="1">
                <button type="button" class="qty-btn minus" onclick="updateQty('<?= htmlspecialchars($id) ?>', -1)">-</button>
                <input type="number" name="quantity" id="qty<?= htmlspecialchars($id) ?>" value="<?= (int)$item['quantity'] ?>" min="1" step="1" class="qty-input" readonly>
                <button type="button" class="qty-btn plus" onclick="updateQty('<?= htmlspecialchars($id) ?>', 1)">+</button>
            </form>
        </td>
        <td class="price-cell">₱<?= number_format($total, 2) ?></td>
        <td><a href="cart.php?remove=<?= urlencode($id) ?>" class="remove-btn" onclick="return confirm('Remove this item from cart?')">Remove</a></td>
    </tr>
    <?php endforeach; ?>
    <tr class="grand-total-row">
        <th colspan="4">Total Amount</th>
        <th colspan="2" class="grand-total-amount">₱<?= number_format($grand_total, 2) ?></th>
    </tr>
</table>

<?php if(isset($_SESSION['customer_id'])): ?>
    <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
<?php else: ?>
    <a href="customer_login.php?redirect_to=checkout.php" class="checkout-btn">Login to Checkout</a>
<?php endif; ?>

<?php else: ?>
<div class="empty">
    <div class="empty-icon">🛒</div>
    <p>Your cart is empty.</p>
    <p style="margin-top: 20px;"><a href="index.php" style="color: #000; font-weight: 600; text-decoration: underline;">Continue Shopping</a></p>
</div>
<?php endif; ?>

</div>
</body>
</html>
