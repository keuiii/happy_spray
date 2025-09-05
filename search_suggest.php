<?php
header('Content-Type: application/json');

require_once 'classes/database.php';
$db = Database::getInstance();

// Get query
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$results = [];
if ($q !== '') {
    $like = "%{$q}%";
    // table name = perfumes (ayon sa code mo)
    $rows = $db->select(
        "SELECT id, name, image, price FROM perfumes 
         WHERE name LIKE ? 
         OR description LIKE ? 
         LIMIT 10",
        [$like, $like]
    );

    if ($rows) {
        $results = $rows;
    }
}

echo json_encode($results);
?>