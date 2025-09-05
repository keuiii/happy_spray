<?php
require_once 'classes/database.php';
$db = Database::getInstance();

// Get product ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = $db->fetch("SELECT * FROM perfumes WHERE id = ?", [$id]);

if($product){
    // Handle images (support both image + image2)
    $images = [];
    if (!empty($product['images'])) {
        // kung may multiple images column (comma separated)
        $images = explode(",", $product['images']);
    } else {
        if (!empty($product['image']))  $images[] = $product['image'];
        if (!empty($product['image2'])) $images[] = $product['image2'];
    }
} else {
    die("Product not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($product['name']) ?> - Happy Sprays</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
* {margin:0; padding:0; box-sizing:border-box;}
body {font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#fff; color:#000;}

/* Top Navbar */
.top-nav {position:fixed; top:0; left:0; width:100%; background:#fff; border-bottom:1px solid #ccc; padding:15px 0; text-align:center; font-family:'Playfair Display', serif; font-size:32px; font-weight:700; text-transform:uppercase; letter-spacing:2px; z-index:1000;}

/* Sub Navbar */
.sub-nav {position:fixed; top:60px; left:0; width:100%; background:#fff; border-bottom:1px solid #ccc; text-align:center; padding:12px 0; transition:top 0.3s; z-index:999; font-family:'Playfair Display', serif; text-transform:uppercase; font-weight:700; letter-spacing:1px;}
.sub-nav a {margin:0 20px; text-decoration:none; color:#000; font-size:18px; transition:color 0.3s;}
.sub-nav a:hover {color:#555;}

/* Back to Shop Button */
.back-btn-bar { position: fixed; top: 140px; left: 30px; z-index: 998; transition: top 0.4s ease;}
.back-btn-bar a { padding: 12px 24px; font-size: 16px; font-weight: bold; background: #fff; color: #000; border: 2px solid #000; text-decoration: none; border-radius: 6px; transition: 0.3s; display: inline-block;}
.back-btn-bar a:hover {background:#000;color:#fff;}

/* Product Container */
.container { display: flex; justify-content: center; align-items: flex-start; gap: 80px; padding: 180px 40px 100px; max-width: 1200px; margin: auto;}

/* Image Gallery */
.product-image {
  flex: 1;
  max-width: 450px;
  position: relative;
  overflow: hidden;  /* para hindi lumabas yung kasunod */
  border: 2px solid #ddd;
  padding: 20px;
  border-radius: 12px;
  background: #fafafa;
}

.product-slider {
  display: flex;
  width: 100%;
  transition: transform 0.4s ease;
}

.product-slider img {
  min-width: 100%;  /* bawat image sakto sa frame */
  height: auto;
  object-fit: contain;
}


.slider-btn { position:absolute; top:50%; transform:translateY(-50%); background:rgba(0,0,0,0.5); color:#fff; border:none; padding:10px; cursor:pointer; font-size:18px; border-radius:50%; }
.slider-btn.left { left:10px; }
.slider-btn.right { right:10px; }

/* Product details */
.product-details { flex: 1; max-width: 500px;}
.product-details h1 { font-family: 'Playfair Display', serif; font-size: 38px; margin-bottom: 15px; letter-spacing: 1px; border-bottom: 2px solid #000; padding-bottom: 10px;}
.product-details p { font-size: 16px; margin: 8px 0; line-height: 1.5;}
.price { font-size: 28px; font-weight: bold; margin: 20px 0;}
.ml {
    font-size: 18px;
    font-weight: 500;
    margin: 5px 0 15px;
    color: #444;
}


/* Tabs */
.tabs { margin: 20px 0; display: flex; gap: 15px; border-bottom: 2px solid #000;}
.tab-btn { padding: 10px 18px; font-size: 15px; font-weight: bold; border: none; background: none; cursor: pointer; transition: 0.3s; border-bottom: 2px solid transparent;}
.tab-btn.active { border-bottom: 2px solid #000;}
.tab-btn:hover { color: #555;}
.tab-content { margin-top: 20px; font-size: 15px; line-height: 1.6;}

/* Add to Cart button */
.add-to-cart-btn { padding: 14px 28px; font-size: 16px; font-weight: bold; background: #fff; color: #000; border: 2px solid #000; cursor: pointer; margin-top: 25px; transition: 0.3s; letter-spacing: 1px;}
.add-to-cart-btn:hover { background: #000; color: #fff; }

/* Divider + Recommendations */
.divider { max-width: 1200px; margin: 60px auto 40px; border: none; border-top: 1px solid #ccc; }
.recommendations { max-width: 1200px; margin: auto; padding: 20px 40px 100px; text-align: center; }
.recommendations h2 { font-family: 'Playfair Display', serif; font-size: 28px; margin-bottom: 40px; text-transform: uppercase; letter-spacing: 1px; }
.recommend-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 30px; justify-items: center; }
.recommend-item { border: 1px solid #eee; padding: 20px; border-radius: 12px; transition: 0.3s; background: #fafafa; width: 100%; max-width: 250px; cursor:pointer; }
.recommend-item:hover { box-shadow: 0 6px 15px rgba(0,0,0,0.1); }
.recommend-item img { width: 100%; height: 200px; object-fit: contain; margin-bottom: 15px; }
.recommend-item h3 { font-size: 18px; margin-bottom: 8px; }
.recommend-item p { font-size: 14px; margin-bottom: 12px; color: #444; }

/* Custom SweetAlert Styling */
.custom-swal-popup { border: 2px solid #000;  /* Black border */ border-radius: 10px; /* Rounded edges */}
.custom-swal-btn { background: #fff !important; color: #000 !important; border: 2px solid #000 !important; border-radius: 5px !important; padding: 6px 14px !important; font-weight: 600;}
.custom-swal-btn:hover { background: #000 !important; color: #fff !important; }

/* Footer */
.footer { background: #111; color: #fff; text-align: center; padding: 40px 20px; margin-top: 60px;}
.footer p { margin: 10px 0; font-size: 14px;}
.footer .social-links { margin: 15px 0;}
.footer .social-links a { margin: 0 12px; color: #fff; text-decoration: none; font-weight: bold; transition: 0.3s;}
.footer .social-links a:hover { color: #ccc;}
.footer .copy { margin-top: 20px; font-size: 12px; color: #aaa;}

.qty-box { display: flex; align-items: center; margin-top: 20px; }
.qty-box button { width: 32px; height: 32px; font-size: 18px; border: 1px solid #000; background: #fff; cursor: pointer; border-radius: 4px;}
.qty-box input { width: 60px; text-align: center; margin: 0 8px; padding: 6px; border: 1px solid #000; border-radius: 4px;}

</style>

</head>
<body>

<!-- Top Nav -->
<div class="top-nav">Happy Sprays</div>

<!-- Sub Nav -->
<div class="sub-nav" id="subNav">
    <a href="index.php">HOME</a>
    <a href="index.php?gender=Male">For Him</a>
    <a href="index.php?gender=Female">For Her</a>
    <a href="#contact">CONTACT</a>
</div>

<!-- Back to Shop Button -->
<div class="back-btn-bar" id="backBtnBar">
    <a href="index.php">←</a>
</div>

<div class="container">
    <!-- Product Image Slider -->
   <div class="product-image">
    <div class="product-slider" id="slider">
        <?php foreach($images as $img): ?>
            <img src="images/<?= trim($img) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        <?php endforeach; ?>
    </div>
    <?php if(count($images) > 1): ?>
        <button class="slider-btn left" onclick="prevSlide()">‹</button>
        <button class="slider-btn right" onclick="nextSlide()">›</button>
    <?php endif; ?>
</div>


    <!-- Product Details -->
    <div class="product-details">
        <h1><?= htmlspecialchars($product['name']) ?></h1>
       <p class="price">₱<?= $product['price'] ?></p>
<?php if (!empty($product['ml_size'])): ?>
    <p class="ml">Size: <?= htmlspecialchars($product['ml_size']) ?></p>
<?php endif; ?>


        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn active" data-tab="desc">Description</button>
            <button class="tab-btn" data-tab="scent">Inspired Scent</button>
            <button class="tab-btn" data-tab="reviews">Reviews</button>
            <button class="tab-btn" data-tab="shipping">Shipping</button>
        </div>

        <!-- Tab Contents -->
        <div class="tab-content" id="desc">
            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        </div>
        <div class="tab-content" id="scent" style="display:none;">
            <p><strong><?= htmlspecialchars($product['brand']) ?></strong></p>
        </div>
        <div class="tab-content" id="reviews" style="display:none;">
            <p>No reviews yet. Be the first to review this perfume!</p>
        </div>
        <div class="tab-content" id="shipping" style="display:none;">
            <p>Standard shipping: 3-5 business days.<br>Express shipping: 1-2 business days.</p>
        </div>
<form class="add-to-cart-form">
    <input type="hidden" name="id" value="<?= $product['id'] ?>">
    <input type="hidden" name="name" value="<?= htmlspecialchars($product['name']) ?>">
    <input type="hidden" name="price" value="<?= $product['price'] ?>">
    <input type="hidden" name="image" value="<?= $product['image'] ?>">

    <!-- Quantity Box -->
    <div class="qty-box">
        <button type="button" onclick="changeQty(-1)">-</button>
        <input type="number" id="qtyInput" name="qty" value="1" min="1">
        <button type="button" onclick="changeQty(1)">+</button>
    </div>

    <!-- Add to Cart -->
    <button type="submit" name="add_to_cart" value="1" class="add-to-cart-btn">
        ADD TO CART
        </button>
    </form>
</form>

    </div>
</div>

<!-- Divider -->
<hr class="divider">

<!-- Recommendations -->
<div class="recommendations">
    <h2>Also you may like</h2>
    <div class="recommend-grid">
        <?php
        $related = $db->select("SELECT * FROM perfumes WHERE id != ? ORDER BY RAND() LIMIT 4", [$id]);

        if ($related) {
            foreach($related as $rel) {
                echo "
                <div class='recommend-item' onclick=\"window.location.href='view_product.php?id={$rel['id']}'\">
                    <img src='images/{$rel['image']}' alt='".htmlspecialchars($rel['name'])."'>
                    <h3>".htmlspecialchars($rel['name'])."</h3>
                    <p>₱{$rel['price']}</p>
                </div>
                ";
            }
        } else {
            echo "<p>No related products found.</p>";
        }
        ?>
    </div>
</div>

<!-- Footer -->
<footer class="footer" id="contact">
    <p>Follow us:</p>
    <div class="social-links">
        <a href="https://facebook.com/YourPage" target="_blank">Facebook</a>
        <a href="https://instagram.com/YourPage" target="_blank">Instagram</a>
    </div>
    <p class="copy">© <?= date("Y") ?> Happy Sprays. All Rights Reserved.</p>
</footer>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/* [same JS as your original, unchanged] */
let lastScrollTop = 0;
const subNav = document.getElementById("subNav");
const backBtnBar = document.getElementById("backBtnBar");

window.addEventListener("scroll", function(){
    let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    if (scrollTop > lastScrollTop) {
        subNav.style.top = "-60px";
        backBtnBar.style.top = "-100px";
    } else {
        subNav.style.top = "60px";
        backBtnBar.style.top = "140px";
    }
    lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
}, false);

// Tabs
const tabBtns = document.querySelectorAll(".tab-btn");
const tabContents = document.querySelectorAll(".tab-content");
tabBtns.forEach(btn => {
    btn.addEventListener("click", () => {
        tabBtns.forEach(b => b.classList.remove("active"));
        tabContents.forEach(c => c.style.display = "none");
        btn.classList.add("active");
        document.getElementById(btn.dataset.tab).style.display = "block";
    });
});

// Slider
let currentIndex = 0;
const slider = document.getElementById("slider");
const slides = slider.children;
function showSlide(index){
    if(index < 0) index = slides.length - 1;
    if(index >= slides.length) index = 0;
    currentIndex = index;
    slider.style.transform = `translateX(-${index * 100}%)`;
}
function nextSlide(){ showSlide(currentIndex + 1); }
function prevSlide(){ showSlide(currentIndex - 1); }

// Swipe support
let startX = 0;
slider.addEventListener("touchstart", e => startX = e.touches[0].clientX);
slider.addEventListener("touchend", e => {
    let endX = e.changedTouches[0].clientX;
    if(endX - startX > 50) prevSlide();
    if(startX - endX > 50) nextSlide();
});

document.querySelectorAll('.add-to-cart-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        // siguraduhin kasama si add_to_cart
        formData.append("add_to_cart", "1");

        fetch('cart.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === "success") {
                Swal.fire({
                    title: '✔ Added to Cart',
                    text: 'Your product has been added.',
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Check Cart',
                    cancelButtonText: 'Continue Shopping'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'cart.php';
                    }
                });
            } else {
                console.log("Server response:", data); // debug output
            }
        });
    });
});

function changeQty(val){
    let input = document.getElementById("qtyInput");
    let newVal = parseInt(input.value) + val;
    if(newVal < 1) newVal = 1;
    input.value = newVal;
}
</script>
</body>
</html>