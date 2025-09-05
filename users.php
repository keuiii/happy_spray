<?php
require_once 'classes/database.php';
$db = Database::getInstance();

// ✅ Use centralized method
$users = $db->getAllUsers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Users List</title>
<style>
    body { font-family: 'Segoe UI', sans-serif; background:#fff; color:#000; margin:30px; }
    h1 { text-align:center; }
    table { width:80%; margin:auto; border-collapse:collapse; }
    th, td { border:1px solid #000; padding:10px; text-align:center; }
    th { background:#f2f2f2; }
    .back-btn {
        display:inline-block; margin:20px auto; padding:10px 20px;
        border:1px solid #000; text-decoration:none; color:#000; border-radius:4px;
    }
    .back-btn:hover { background:#000; color:#fff; }
</style>
</head>
<body>
    <h1>Registered Users</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Date Created</th>
        </tr>
        <?php if (!empty($users)): ?>
            <?php foreach($users as $row): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['role']) ?></td>
                <td><?= $row['created_at'] ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4">No users found.</td></tr>
        <?php endif; ?>
    </table>

    <div style="text-align:center;">
        <a href="admin_dashboard.php" class="back-btn">← Back to Admin Dashboard</a>
    </div>
</body>
</html>
