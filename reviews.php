<?php
$reviews = [
  "r1.png","r2.png","r3.png","r4.png","r5.png",
  "r6.png","r7.png","r8.png","r9.png","r10.png","r11.png"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Happy Sprays - Customer Reviews</title>
  <style>
body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: #fafafa;
  color: #222;
  margin: 0;
  padding: 0;
}

header {
  text-align: center;
  padding: 40px 20px 20px;
  border-bottom: 1px solid #eee;
}
header h1 {
  font-family: 'Playfair Display', serif;
  font-size: 36px;
  font-weight: 700;
  margin: 0;
}
header p {
  color: #666;
  margin-top: 5px;
  font-size: 15px;
}

.reviews-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 25px;
  max-width: 1200px;
  margin: 50px auto;
  padding: 0 20px;
}

.review-card {
  border-radius: 14px;
  overflow: hidden;
  cursor: pointer;
  background: #fff;
  box-shadow: 0 4px 10px rgba(0,0,0,0.08);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.review-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 8px 18px rgba(0,0,0,0.15);
}
.review-card img {
  width: 100%;
  height: 280px;
  object-fit: cover;
  display: block;
  transition: transform 0.35s ease;
}
.review-card:hover img {
  transform: scale(1.06);
}

/* Back Button */
.back-btn {
  display: block;
  width: max-content;
  margin: 40px auto;
  padding: 12px 30px;
  border-radius: 30px;
  background: linear-gradient(135deg, #111, #333);
  color: #fff;
  text-decoration: none;
  font-weight: 600;
  letter-spacing: 0.5px;
  transition: 0.3s;
}
.back-btn:hover {
  background: #fff;
  color: #111;
  border: 2px solid #111;
}

/* Footer */
footer {
  text-align: center;
  padding: 30px;
  border-top: 1px solid #eee;
  margin-top: 60px;
  font-size: 14px;
  color: #777;
}

/* --- POPUP MODAL --- */
.popup-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.4);
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease;
}
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.popup-overlay.active {
  display: flex;
}

.popup-content {
  position: relative;
  background: #fff;
  padding: 12px;
  border-radius: 14px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.25);
  animation: popIn 0.25s ease;
  max-width: 480px;
  max-height: 480px;
}
.popup-content img {
  width: 100%;
  height: auto;
  border-radius: 10px;
  display: block;
}
.popup-close {
  position: absolute;
  top: 8px;
  right: 12px;
  font-size: 22px;
  font-weight: bold;
  cursor: pointer;
  color: #444;
  transition: 0.2s;
}
.popup-close:hover {
  color: #000;
  transform: scale(1.1);
}
@keyframes popIn {
  from { transform: scale(0.85); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}


  </style>
</head>
<body>
  <header>
    <h1>Customer Reviews</h1>
    <p>Click an image to pop out 👇</p>
  </header>

  <div class="reviews-container">
    <?php foreach ($reviews as $img): ?>
      <div class="review-card">
        <img src="images/<?= $img ?>" alt="Happy Sprays Review" onclick="openPopup('images/<?= $img ?>')">
      </div>
    <?php endforeach; ?>
  </div>

  <a href="index.php" class="back-btn">← Back to Home</a>

  <!-- Popup Modal -->
  <div class="popup-overlay" id="popup">
    <div class="popup-content">
      <span class="popup-close" onclick="closePopup()">&times;</span>
      <img id="popup-img" src="" alt="Review">
    </div>
  </div>

  <footer>
    &copy; <?= date("Y") ?> Happy Sprays. All rights reserved.
  </footer>

  <script>
    function openPopup(src) {
      document.getElementById("popup-img").src = src;
      document.getElementById("popup").classList.add("active");
    }
    function closePopup() {
      document.getElementById("popup").classList.remove("active");
    }
    // close on overlay click
    document.getElementById("popup").addEventListener("click", function(e){
      if(e.target === this) closePopup();
    });
  </script>
</body>
</html>
