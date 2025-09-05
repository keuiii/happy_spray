<?php
// Include centralized database connection
require_once 'classes/database.php';

try {
    // Get database instance
    $db = Database::getInstance();
    
    // Handle Delete
    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        $deleted = $db->deleteById('perfumes', $id);
        
        if ($deleted > 0) {
            // Redirect to avoid resubmission on refresh
            header("Location: products_list.php?success=deleted");
            exit;
        }
    }
    
    // PAGINATION
    $limit = 5;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    
    // Get paginated results using the centralized method
    $paginationResult = $db->getPaginatedResults('perfumes', $page, $limit, 'id DESC');
    
    $products = $paginationResult['data'];
    $totalProducts = $paginationResult['total'];
    $totalPages = $paginationResult['total_pages'];
    $currentPage = $paginationResult['current_page'];
    
} catch (Exception $e) {
    // Handle any database errors gracefully
    $error = "Error loading products. Please try again.";
    $products = [];
    $totalPages = 0;
    $currentPage = 1;
    // Optionally log the error: error_log($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Products List - Happy Sprays</title>
<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background: #f9f9f9;
    color: #333;
}
h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #222;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
th, td {
    padding: 12px;
    text-align: center;
}
th {
    background: #f4f4f4;
    color: #333;
    font-weight: bold;
    border-bottom: 2px solid #ddd;
}
td {
    border-bottom: 1px solid #eee;
}
tr:nth-child(even) {
    background: #fafafa;
}
a.delete {
    color: #d9534f;
    text-decoration: none;
    font-weight: bold;
    padding: 6px 10px;
    border-radius: 4px;
    transition: background 0.2s;
}
a.delete:hover {
    background: #d9534f;
    color: #fff;
}
a.edit {
    color: #0275d8;
    text-decoration: none;
    font-weight: bold;
    padding: 6px 10px;
    border-radius: 4px;
    transition: background 0.2s;
}
a.edit:hover {
    background: #0275d8;
    color: #fff;
}
img {
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.pagination {
    text-align: center;
    margin: 20px 0;
}
.pagination a {
    padding: 8px 14px;
    margin: 2px;
    border: 1px solid #ddd;
    text-decoration: none;
    color: #333;
    border-radius: 5px;
    background: #fff;
    transition: all 0.2s;
}
.pagination a:hover {
    background: #333;
    color: #fff;
}
.pagination a.active {
    background: #333;
    color: #fff;
    font-weight: bold;
}
.back-btn {
    display: inline-block;
    margin: 10px 0 20px;
    padding: 10px 18px;
    border: none;
    background: #333;
    text-decoration: none;
    color: #fff;
    font-weight: bold;
    border-radius: 6px;
    transition: background 0.2s;
}
.back-btn:hover {
    background: #000;
}
.message {
    padding: 10px;
    margin: 10px 0;
    border-radius: 4px;
    text-align: center;
}
.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
.empty-state {
    text-align: center;
    padding: 40px;
    color: #666;
}
</style>
</head>

<body>

<a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>

<?php if (isset($_GET['success']) && $_GET['success'] == 'deleted'): ?>
    <div class="message success">Product deleted successfully!</div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="message error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div id="productsTable">
<h2>Existing Products</h2>

<?php if (empty($products)): ?>
    <div class="empty-state">
        <p>No products found.</p>
        <a href="add_products.php" class="back-btn">Add First Product</a>
    </div>
<?php else: ?>

<table>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Inspired Scent</th>
    <th>Price</th>
    <th>Gender</th>
    <th>Stock</th>
    <th>Images</th>
    <th>Actions</th>
</tr>
<?php foreach ($products as $product): ?>
<tr>
    <td><?= htmlspecialchars($product['id']) ?></td>
    <td><?= htmlspecialchars($product['name']) ?></td>
    <td><?= htmlspecialchars($product['brand']) ?></td>
    <td>$<?= number_format($product['price'], 2) ?></td>
    <td><?= htmlspecialchars($product['gender']) ?></td>
    <td><?= htmlspecialchars($product['stock']) ?></td>
    <td>
        <?php if (!empty($product['image'])): ?>
            <img src="images/<?= htmlspecialchars($product['image']) ?>" width="60" alt="Product Image">
        <?php endif; ?>
        <?php if (!empty($product['image2'])): ?>
            <img src="images/<?= htmlspecialchars($product['image2']) ?>" width="60" alt="Product Image 2">
        <?php endif; ?>
    </td>
    <td>
        <a class="edit" href="add_products.php?edit=<?= $product['id'] ?>">Edit</a> | 
        <a class="delete" href="products_list.php?delete=<?= $product['id'] ?>" onclick="return confirm('Delete this product?');">Delete</a>
    </td>
</tr>
<?php endforeach; ?>
</table>

<?php if ($totalPages > 1): ?>
<div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="products_list.php?page=<?= $i ?>" class="<?= ($i == $currentPage) ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php endif; ?>

<p style="text-align: center; margin-top: 20px; color: #666;">
    Showing <?= count($products) ?> of <?= $totalProducts ?> products
</p>

</div>

<script>
function toggleProducts(){
    const table=document.getElementById("productsTable");
    table.style.display = (table.style.display==="none"||table.style.display==="") ? "block" : "none";
}
</script>

</body>
</html>