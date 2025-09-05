<?php
// classes/database.php - Centralized Database Connection for HAPPY-SPRAYS

class Database {
    private static $instance = null;
    private $connection;
    
    // Database configuration
    private $host = 'mysql.hostinger.com';
    private $database = 'u425676266_happy_sprays';
    private $username = 'u425676266_jows';
    private $password = 'GIAjanda9';
    private $charset = 'utf8mb4';
    
    // Private constructor to prevent direct instantiation
    private function __construct() {
        $this->connect();
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    // Get the singleton instance
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Create the database connection
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
            ];
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    // Get the PDO connection
    public function getConnection() {
        return $this->connection;
    }

    // =================================================================
    // GENERIC QUERY HELPERS
    // =================================================================
    
    public function select($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Select query failed: " . $e->getMessage());
        }
    }
    
    public function fetch($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Fetch query failed: " . $e->getMessage());
        }
    }
    
    public function insert($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Insert query failed: " . $e->getMessage());
        }
    }
    
    public function update($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Update query failed: " . $e->getMessage());
        }
    }
    
    public function delete($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Delete query failed: " . $e->getMessage());
        }
    }
    public function getAllUsers() {
        return $this->select("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC");
    }
    // Helper method to delete a record safely by ID
    public function deleteById($table, $id) {
        try {
            $stmt = $this->connection->prepare("DELETE FROM {$table} WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Delete failed: " . $e->getMessage());
        }
    }

    // =================================================================
    // SESSION MANAGEMENT HELPERS
    // =================================================================
    
    public function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
   // Replace your existing methods in database.php with these fixed versions:

// Replace your existing methods in database.php with these fixed versions:

public function loginCustomerAuth($username_or_email, $password) {
    if (empty($username_or_email) || empty($password)) {
        return ["success" => false, "message" => "Please fill in all fields."];
    }

    try {
        // Search by username OR email with role 'customer'
        $user = $this->fetch(
            "SELECT * FROM users WHERE (username = ? OR email = ?) AND role = 'customer' LIMIT 1",
            [$username_or_email, $username_or_email]
        );

        if (!$user) {
            // Check if account exists with different role
            $anyUser = $this->fetch(
                "SELECT role FROM users WHERE (username = ? OR email = ?) LIMIT 1",
                [$username_or_email, $username_or_email]
            );

            if ($anyUser) {
                return ["success" => false, "message" => "This account is not a customer account."];
            }

            return ["success" => false, "message" => "Account not found. Please register first."];
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return ["success" => false, "message" => "Incorrect password."];
        }

        // Save session
        $this->loginCustomer($user);

        return ["success" => true, "user" => $user];

    } catch (Exception $e) {
        error_log("Customer login error: " . $e->getMessage());
        return ["success" => false, "message" => "Login error. Please try again."];
    }
}


public function registerCustomer($firstname, $lastname, $username, $email, $password) {
    try {
        // Check duplicates (username OR email)
        $existing = $this->fetch(
            "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1",
            [$username, $email]
        );
        if ($existing) {
            return "Username or Email already exists.";
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert with default role = customer
       $success = $this->insert(
    "INSERT INTO users (firstname, lastname, username, email, password, role, created_at) 
     VALUES (?, ?, ?, ?, ?, ?, NOW())",
    [$firstname, $lastname, $username, $email, $hashedPassword, 'customer']
);

return $success ? true : "Registration failed. Please try again.";


    } catch (Exception $e) {
        error_log("Register error: " . $e->getMessage());
        return "Registration failed. Please try again.";
    }
}

public function isCustomerLoggedIn() {
    $this->startSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'customer';
}

public function getCurrentCustomerId() {
    $this->startSession();
    return $_SESSION['user_id'] ?? null;
}

public function getCurrentCustomer() {
    $customerId = $this->getCurrentCustomerId();
    if (!$customerId) {
        return null;
    }
    
    return $this->fetch(
        "SELECT * FROM users WHERE id = ? AND role = 'customer' LIMIT 1",
        [$customerId]
    );
}

public function loginCustomer($user) {
    $this->startSession();
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['customer_name'] = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
}

public function logoutCustomer() {
    $this->startSession();
    
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    
    session_destroy();
}
public function registerUser($username, $password, $role, $secret_code) {
    // Check secret code
    if ($secret_code !== "happy2025") {
        return ["success" => false, "message" => "Invalid registration code."];
    }

    if ($username === "" || $password === "") {
        return ["success" => false, "message" => "All fields are required."];
    }

    // Check if username exists
    $existing = $this->fetch("SELECT id FROM users WHERE username = ?", [$username]);
    if ($existing) {
        return ["success" => false, "message" => "Username already exists."];
    }

    // Hash password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $success = $this->insert(
        "INSERT INTO users (username, password, role) VALUES (?, ?, ?)",
        [$username, $hashed, $role]
    );

    if ($success) {
        return ["success" => true, "message" => "Account created successfully."];
    } else {
        return ["success" => false, "message" => "Error: Registration failed."];
    }
}


// ✅ Additional helper method for checking any logged-in user
public function isLoggedIn() {
    $this->startSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// ✅ Additional helper method for getting current user role
public function getCurrentUserRole() {
    $this->startSession();
    return $_SESSION['role'] ?? null;
}
// Add this temporary debug method to your Database class
public function debugUserCheck($username) {
    try {
        // Check if user exists at all
        $anyUser = $this->fetch("SELECT id, username, role FROM users WHERE username = ?", [$username]);
        
        // Check customer specifically
        $customerUser = $this->fetch("SELECT id, username, role FROM users WHERE username = ? AND role = 'customer'", [$username]);
        
        // Check all users (for debugging)
        $allUsers = $this->fetchAll("SELECT id, username, role FROM users ORDER BY id DESC LIMIT 5");
        
        return [
            'searched_username' => $username,
            'any_user_found' => $anyUser,
            'customer_user_found' => $customerUser,
            'recent_users' => $allUsers
        ];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

    // =================================================================
    // PAGINATION HELPER
    // =================================================================
    
    public function getPaginatedResults($table, $page = 1, $limit = 10, $orderBy = "id DESC") {
        try {
            $offset = ($page - 1) * $limit;

            // Count total rows
            $countStmt = $this->connection->prepare("SELECT COUNT(*) as total FROM {$table}");
            $countStmt->execute();
            $total = $countStmt->fetch()['total'];

            // Fetch paginated data  
            $stmt = $this->connection->prepare("SELECT * FROM {$table} ORDER BY {$orderBy} LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll();

            return [
                "data" => $data,
                "total" => $total,
                "total_pages" => ceil($total / $limit),
                "current_page" => $page,
                "per_page" => $limit
            ];
        } catch (PDOException $e) {
            throw new Exception("Pagination failed: " . $e->getMessage());
        }
    }

    // =================================================================
    // AUTHENTICATION METHODS
    // =================================================================
    
    public function authenticateUser($username, $password, $role = 'user') {
        try {
            // Support both email and username fields
            $stmt = $this->connection->prepare("SELECT id, password, role, email FROM users WHERE (username = ? OR email = ?) AND role = ? LIMIT 1");
            $stmt->execute([$username, $username, $role]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Authentication failed: " . $e->getMessage());
        }
    }
    
        // =================================================================
    // CUSTOMER LOGIN METHOD
    // =================================================================
    
    public function loginAdmin($username, $password) {
        if ($username === "" || $password === "") {
            return ["success" => false, "message" => "Please fill in all fields."];
        }

        try {
            $user = $this->fetch(
                "SELECT * FROM users WHERE username = ? AND role = 'admin' LIMIT 1",
                [$username]
            );

            if (!$user) {
                return ["success" => false, "message" => "Admin account not found."];
            }

            if (!password_verify($password, $user['password'])) {
                return ["success" => false, "message" => "Incorrect password."];
            }

            return ["success" => true, "user" => $user];

        } catch (Exception $e) {
            error_log("Admin login error: " . $e->getMessage());
            return ["success" => false, "message" => "Login error. Please try again."];
        }
    }

    public function loginUser($username, $password) {
        // Fetch user by username
        $user = $this->fetch("SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1", [$username]);

        // Check if user exists and password is correct
        if ($user && password_verify($password, $user['password'])) {
            return $user; // return user details if login successful
        }

        return false; // login failed
    }
    // =================================================================
// ORDER MANAGEMENT METHODS (Add these to your Database.php class)
// =================================================================

public function getAllOrders($orderBy = 'created_at DESC') {
    try {
        return $this->select("SELECT * FROM orders ORDER BY {$orderBy}");
    } catch (Exception $e) {
        error_log("Get all orders error: " . $e->getMessage());
        return [];
    }
}

public function getOrderById($order_id) {
    try {
        return $this->fetch("SELECT * FROM orders WHERE id = ? LIMIT 1", [$order_id]);
    } catch (Exception $e) {
        error_log("Get order by ID error: " . $e->getMessage());
        return null;
    }
}
public function getOrderItems($order_id) {
    return $this->select(
        "SELECT * FROM order_items WHERE order_id = ?",
        [$order_id]
    );
}
public function updateOrderStatus($order_id, $status) {
    try {
        // Validate status
        $validStatuses = ['processing', 'preparing', 'out for delivery', 'received', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }

        $success = $this->update(
            "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?",
            [$status, $order_id]
        );

        if ($success) {
            return ['success' => true, 'message' => 'Order status updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update order status'];
        }
    } catch (Exception $e) {
        error_log("Update order status error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error updating order status'];
    }
}

public function getOrderStatuses() {
    return [
        'processing' => 'Processing',
        'preparing' => 'Preparing',
        'out for delivery' => 'Out for Delivery',
        'received' => 'Received',
        'cancelled' => 'Cancelled'
    ];
}

public function getOrdersByStatus($status) {
    try {
        return $this->select("SELECT * FROM orders WHERE status = ? ORDER BY created_at DESC", [$status]);
    } catch (Exception $e) {
        error_log("Get orders by status error: " . $e->getMessage());
        return [];
    }
}

public function getOrderStats() {
    try {
        $stats = [];
        $statuses = array_keys($this->getOrderStatuses());
        
        foreach ($statuses as $status) {
            $count = $this->fetch("SELECT COUNT(*) as count FROM orders WHERE status = ?", [$status]);
            $stats[$status] = $count['count'] ?? 0;
        }
        
        $total = $this->fetch("SELECT COUNT(*) as total, SUM(total_amount) as revenue FROM orders");
        $stats['total_orders'] = $total['total'] ?? 0;
        $stats['total_revenue'] = $total['revenue'] ?? 0;
        
        return $stats;
    } catch (Exception $e) {
        error_log("Get order stats error: " . $e->getMessage());
        return [];
    }
}

public function searchOrders($search_term) {
    try {
        $search = "%{$search_term}%";
        return $this->select(
            "SELECT * FROM orders WHERE 
             customer_name LIKE ? OR 
             email LIKE ? OR 
             id LIKE ? 
             ORDER BY created_at DESC",
            [$search, $search, $search]
        );
    } catch (Exception $e) {
        error_log("Search orders error: " . $e->getMessage());
        return [];
    }
}


   // =================================================================
// CUSTOMER ORDER MANAGEMENT METHODS
// =================================================================

public function getCustomerOrders($customer_id = null, $limit = null, $offset = 0) {
    try {
        if ($customer_id === null) {
            $customer_id = $this->getCurrentCustomerId();
        }
        
        if (!$customer_id) {
            return [];
        }
        
        $sql = "SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC";
        $params = [$customer_id];
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;
        }
        
        return $this->select($sql, $params);
        
    } catch (Exception $e) {
        error_log("Get customer orders error: " . $e->getMessage());
        return [];
    }
}

public function getCustomerOrder($orderId, $customer_id = null) {
    try {
        if ($customer_id === null) {
            $customer_id = $this->getCurrentCustomerId();
        }
        
        if (!$customer_id) {
            return null;
        }
        
        $order = $this->fetch(
            "SELECT * FROM orders WHERE id = ? AND customer_id = ? LIMIT 1",
            [$orderId, $customer_id]
        );

        if (!$order) {
            return null;
        }

        // Format fields for UI
        $order['formatted_status'] = $this->formatOrderStatus($order['status']);
        $order['status_class']     = $this->getOrderStatusClass($order['status']);
        $order['formatted_total']  = $this->formatPrice($order['total_amount']);
        $order['formatted_date']   = $this->formatOrderDate($order['created_at']);

        return $order;
        
    } catch (Exception $e) {
        error_log("Get customer order error: " . $e->getMessage());
        return null;
    }
}

public function getCustomerOrderItems($orderId, $customer_id = null) {
    try {
        $order = $this->getCustomerOrder($orderId, $customer_id);
        if (!$order) {
            return [];
        }
        
        return $this->select(
            "SELECT oi.*, p.image, p.name 
             FROM order_items oi 
             LEFT JOIN perfumes p ON oi.product_id = p.id 
             WHERE oi.order_id = ? 
             ORDER BY oi.id",
            [$orderId]
        );
        
    } catch (Exception $e) {
        error_log("Get customer order items error: " . $e->getMessage());
        return [];
    }
}

public function getCustomerOrdersCount($customer_id = null) {
    try {
        if ($customer_id === null) {
            $customer_id = $this->getCurrentCustomerId();
        }
        
        if (!$customer_id) {
            return 0;
        }
        
        $result = $this->fetch(
            "SELECT COUNT(*) as total FROM orders WHERE customer_id = ?",
            [$customer_id]
        );
        
        return $result ? (int)$result['total'] : 0;
        
    } catch (Exception $e) {
        error_log("Get customer orders count error: " . $e->getMessage());
        return 0;
    }
}

public function getCustomerOrdersByStatus($status, $customer_id = null) {
    try {
        if ($customer_id === null) {
            $customer_id = $this->getCurrentCustomerId();
        }
        
        if (!$customer_id) {
            return [];
        }
        
        return $this->select(
            "SELECT * FROM orders WHERE customer_id = ? AND status = ? ORDER BY created_at DESC",
            [$customer_id, $status]
        );
        
    } catch (Exception $e) {
        error_log("Get customer orders by status error: " . $e->getMessage());
        return [];
    }
}

public function getCustomerRecentOrders($limit = 5, $customer_id = null) {
    return $this->getCustomerOrders($customer_id, $limit);
}

// =================================================================
// ORDER HELPER FORMATTING METHODS
// =================================================================

public function formatOrderStatus($status) {
    $map = [
        'pending'    => 'Pending',
        'processing' => 'Processing',
        'shipped'    => 'Shipped',
        'delivered'  => 'Delivered',
        'completed'  => 'Completed',
        'cancelled'  => 'Cancelled'
    ];
    return $map[strtolower($status)] ?? ucfirst($status);
}

public function getOrderStatusClass($status) {
    $map = [
        'pending'    => 'status-pending',
        'processing' => 'status-processing',
        'shipped'    => 'status-shipped',
        'delivered'  => 'status-delivered',
        'completed'  => 'status-completed',
        'cancelled'  => 'status-cancelled'
    ];
    return $map[strtolower($status)] ?? '';
}

public function formatPrice($amount) {
    return "₱" . number_format((float)$amount, 2);
}

public function formatOrderDate($date) {
    return date("M d, Y h:i A", strtotime($date));
}

    // =================================================================
    // CUSTOMER PROFILE MANAGEMENT METHODS
    // =================================================================
    
    public function updateCustomerProfile($data, $customer_id = null) {
        try {
            // If no customer_id provided, use current logged-in customer
            if ($customer_id === null) {
                $customer_id = $this->getCurrentCustomerId();
            }
            
            if (!$customer_id) {
                return ['success' => false, 'message' => 'Please login first.'];
            }
            
            // Validate required fields
            $requiredFields = ['firstname', 'lastname', 'username'];
            foreach ($requiredFields as $field) {
                if (empty(trim($data[$field] ?? ''))) {
                    return ['success' => false, 'message' => ucfirst($field) . ' is required.'];
                }
            }
            
            // Validate email format
            if (!filter_var($data['username'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Please enter a valid email address.'];
            }
            
            // Check if username is taken by another user
            $existing = $this->fetch(
                "SELECT id FROM users WHERE username = ? AND id != ? LIMIT 1",
                [$data['username'], $customer_id]
            );
            
            if ($existing) {
                return ['success' => false, 'message' => 'Email address already exists.'];
            }
            
            // Update profile
            $rowsAffected = $this->update(
                "UPDATE users SET firstname = ?, lastname = ?, username = ? WHERE id = ? AND role = 'customer'",
                [
                    trim($data['firstname']),
                    trim($data['lastname']),
                    trim($data['username']),
                    $customer_id
                ]
            );
            
            if ($rowsAffected > 0) {
                // Update session data if it's current user
                if ($customer_id == $this->getCurrentCustomerId()) {
                    $_SESSION['customer_username'] = $data['username'];
                    $_SESSION['customer_name'] = $data['firstname'] . ' ' . $data['lastname'];
                }
                
                return ['success' => true, 'message' => 'Profile updated successfully!'];
            } else {
                return ['success' => false, 'message' => 'No changes were made.'];
            }
            
        } catch (Exception $e) {
            error_log("Customer profile update error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Update failed. Please try again.'];
        }
    }
    
    public function changeCustomerPassword($currentPassword, $newPassword, $confirmPassword, $customer_id = null) {
        try {
            // If no customer_id provided, use current logged-in customer
            if ($customer_id === null) {
                $customer_id = $this->getCurrentCustomerId();
            }
            
            if (!$customer_id) {
                return ['success' => false, 'message' => 'Please login first.'];
            }
            
            // Validate inputs
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                return ['success' => false, 'message' => 'All password fields are required.'];
            }
            
            if ($newPassword !== $confirmPassword) {
                return ['success' => false, 'message' => 'New passwords do not match.'];
            }
            
            if (strlen($newPassword) < 6) {
                return ['success' => false, 'message' => 'Password must be at least 6 characters long.'];
            }
            
            // Verify current password
            $customer = $this->fetch(
                "SELECT password FROM users WHERE id = ? AND role = 'customer' LIMIT 1",
                [$customer_id]
            );
            
            if (!$customer || !password_verify($currentPassword, $customer['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect.'];
            }
            
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $rowsAffected = $this->update(
                "UPDATE users SET password = ? WHERE id = ? AND role = 'customer'",
                [$hashedPassword, $customer_id]
            );
            
            if ($rowsAffected > 0) {
                return ['success' => true, 'message' => 'Password changed successfully!'];
            } else {
                return ['success' => false, 'message' => 'Password change failed.'];
            }
            
        } catch (Exception $e) {
            error_log("Customer password change error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Password change failed. Please try again.'];
        }
    }

    // =================================================================
    // UTILITY/FORMATTING METHODS
    // =================================================================
  
    // =================================================================
    // DASHBOARD HELPERS
    // =================================================================
    
    public function getProductsCount() {
        try {
            $result = $this->fetch("SELECT COUNT(*) as cnt FROM perfumes");
            return $result ? (int)$result['cnt'] : 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    public function getUsersCount() {
        try {
            $tableExists = $this->fetch("SHOW TABLES LIKE 'users'");
            if ($tableExists) {
                $result = $this->fetch("SELECT COUNT(*) as cnt FROM users");
                return $result ? (int)$result['cnt'] : 0;
            }
        } catch (Exception $e) {
            return 0;
        }
        return 0;
    }
    
    public function getOrdersCount() {
        try {
            $tableExists = $this->fetch("SHOW TABLES LIKE 'orders'");
            if ($tableExists) {
                $result = $this->fetch("SELECT COUNT(*) as cnt FROM orders");
                return $result ? (int)$result['cnt'] : 0;
            }
        } catch (Exception $e) {
            return 0;
        }
        return 0;
    }
    
    public function getLowStockProducts($threshold = 10) {
        return $this->select("SELECT * FROM perfumes WHERE stock < ? ORDER BY stock ASC", [$threshold]);
    }
    
    public function getCustomerDashboardData($customer_id = null) {
        try {
            // If no customer_id provided, use current logged-in customer
            if ($customer_id === null) {
                $customer_id = $this->getCurrentCustomerId();
            }
            
            if (!$customer_id) {
                return null;
            }
            
            return [
                'customer' => $this->getCurrentCustomer(),
                'total_orders' => $this->getCustomerOrdersCount($customer_id),
                'recent_orders' => $this->getCustomerRecentOrders(3, $customer_id),
                'pending_orders' => count($this->getCustomerOrdersByStatus('pending', $customer_id)),
                'completed_orders' => count($this->getCustomerOrdersByStatus('completed', $customer_id)),
                'cancelled_orders' => count($this->getCustomerOrdersByStatus('cancelled', $customer_id))
            ];
            
        } catch (Exception $e) {
            error_log("Get customer dashboard data error: " . $e->getMessage());
            return null;
        }
    }

    // =================================================================
    // PRODUCT MANAGEMENT METHODS  
    // =================================================================
    
    public function getProductById($id) {
        return $this->fetch("SELECT * FROM perfumes WHERE id = ?", [$id]);
    }
    public function getPerfumes($gender_filter = null, $search_query = null) {
        $sql = "SELECT * FROM perfumes WHERE 1";
        $params = [];

        if ($gender_filter === 'Male' || $gender_filter === 'Female') {
            $sql .= " AND gender = ?";
            $params[] = $gender_filter;
        }

        if (!empty($search_query)) {
            $sql .= " AND (name LIKE ? OR description LIKE ? OR price LIKE ?)";
            $search_like = "%" . $search_query . "%";
            $params[] = $search_like;
            $params[] = $search_like;
            $params[] = $search_like;
        }

        return $this->select($sql, $params);
    }
    
    public function addProduct($data, $files) {
        $name = $data['name'];
        $brand = $data['brand'];
        $price = $data['price'];
        $gender = $data['gender'];
        $stock = $data['stock'] ?? 0;
        $description = $data['description'] ?? '';
        $ml_size = $data['ml_size'] ?? '';

        // Handle image uploads
        $image = $this->handleImageUpload($files, 'image');
        $image2 = $this->handleImageUpload($files, 'image2');

        return $this->insert(
            "INSERT INTO perfumes (name, brand, price, gender, image, image2, description, stock, ml_size, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [$name, $brand, $price, $gender, $image, $image2, $description, $stock, $ml_size]
        );
    }
    
    public function updateProduct($data, $files) {
        $id = intval($data['id']);
        $name = $data['name'];
        $brand = $data['brand'];
        $price = $data['price'];
        $gender = $data['gender'];
        $stock = $data['stock'];
        $description = $data['description'];
        $ml_size = $data['ml_size'];

        $params = [$name, $brand, $price, $gender, $stock, $description, $ml_size];
        $updateQuery = "UPDATE perfumes SET name = ?, brand = ?, price = ?, gender = ?, stock = ?, description = ?, ml_size = ?";

        // Handle image updates
        $newImage = $this->handleImageUpload($files, 'image');
        if ($newImage) {
            $updateQuery .= ", image = ?";
            $params[] = $newImage;
        }
        
        $newImage2 = $this->handleImageUpload($files, 'image2');
        if ($newImage2) {
            $updateQuery .= ", image2 = ?";
            $params[] = $newImage2;
        }

        $updateQuery .= " WHERE id = ?";
        $params[] = $id;

        return $this->update($updateQuery, $params);
    }
    
    // Helper method for image uploads
    private function handleImageUpload($files, $fieldName) {
        if (!empty($files[$fieldName]['name']) && $files[$fieldName]['error'] === UPLOAD_ERR_OK) {
            $fileName = time() . '_' . basename($files[$fieldName]['name']);
            $uploadPath = "images/" . $fileName;
            
            if (move_uploaded_file($files[$fieldName]['tmp_name'], $uploadPath)) {
                return $fileName;
            }
        }
        return null;
    }

    // =================================================================
    // CART MANAGEMENT METHODS (Session-based)
    // =================================================================
    
    public function getCart() {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        return $_SESSION['cart'];
    }

    public function addToCart($id, $name, $price, $image, $qty = 1) {
        $qty = max(1, (int)$qty);

        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity'] += $qty;
        } else {
            $_SESSION['cart'][$id] = [
                'name' => $name,
                'price' => (float)$price,
                'image' => $image,
                'quantity' => $qty,
            ];
        }
    }

    public function updateCartQuantity($id, $qty) {
        $qty = max(1, (int)$qty);
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity'] = $qty;
        }
    }

    public function removeFromCart($id) {
        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
        }
    }

    public function clearCart() {
        $_SESSION['cart'] = [];
    }

    public function isCartEmpty() {
        return empty($_SESSION['cart']);
    }

    public function getCartTotals() {
        $cart = $this->getCart();
        $grandTotal = 0;
        foreach ($cart as $item) {
            $grandTotal += $item['price'] * $item['quantity'];
        }
        return $grandTotal;
    }

    public function calculateGrandTotal() {
        return $this->getCartTotals(); // Same as getCartTotals
    }

    public function getCartItems() {
        return $this->getCart(); // Same as getCart
    }

    // =================================================================
    // ORDER MANAGEMENT METHODS
    // =================================================================
    
    public function createOrder($customerData, $cartItems, $totalAmount) {
        try {
            // Start transaction
            $this->connection->beginTransaction();
            
            // Insert order
            $orderId = $this->insert(
                "INSERT INTO orders (customer_name, customer_email, customer_phone, customer_address, total_amount, order_date, status) VALUES (?, ?, ?, ?, ?, NOW(), 'pending')",
                [$customerData['name'], $customerData['email'], $customerData['phone'], $customerData['address'], $totalAmount]
            );
            
            // Insert order items
            foreach ($cartItems as $productId => $item) {
                $this->insert(
                    "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)",
                    [$orderId, $productId, $item['name'], $item['price'], $item['quantity'], ($item['price'] * $item['quantity'])]
                );
                
                // Update stock
                $this->update(
                    "UPDATE perfumes SET stock = stock - ? WHERE id = ?",
                    [$item['quantity'], $productId]
                );
            }
            
            // Commit transaction
            $this->connection->commit();
            return $orderId;
            
        } catch (Exception $e) {
            // Rollback on error
            $this->connection->rollBack();
            throw new Exception("Order creation failed: " . $e->getMessage());
        }
    }
    
    // =================================================================
    // CHECKOUT HELPER METHODS
    // =================================================================
    
    public function validateCheckoutData($data) {
        $errors = [];
        
        // Required fields
        $required = ['name', 'email', 'street', 'city', 'province', 'postal', 'payment'];
        foreach ($required as $field) {
            if (empty(trim($data[$field] ?? ''))) {
                $errors[] = ucfirst($field) . " is required.";
            }
        }
        
        // Email validation
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }
        
        // Payment method validation
        if (!in_array($data['payment'] ?? '', ['cod', 'gcash'])) {
            $errors[] = "Please select a valid payment method.";
        }
        
        // GCash validation
        if (($data['payment'] ?? '') === 'gcash') {
            if (empty($_FILES['gcash_ref']['name'])) {
                $errors[] = "Please upload proof of payment for GCash.";
            } elseif ($_FILES['gcash_ref']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = "Error uploading proof of payment.";
            }
        }
        
        return $errors;
    }
    
    public function processCheckout($data, $files) {
        // Validate cart is not empty
        if ($this->isCartEmpty()) {
            throw new Exception("Your cart is empty.");
        }
        
        // Validate form data
        $errors = $this->validateCheckoutData($data);
        if (!empty($errors)) {
            throw new Exception(implode(' ', $errors));
        }
        
        // Get cart data
        $cartItems = $this->getCartItems();
        $grandTotal = $this->calculateGrandTotal();
        
        // Handle GCash proof upload
        $proofFileName = null;
        if ($data['payment'] === 'gcash' && !empty($files['gcash_ref']['name'])) {
            $proofFileName = $this->handleProofUpload($files['gcash_ref']);
        }
        
        // Prepare customer data
        $customerData = [
            'name' => trim($data['name']),
            'email' => trim($data['email']),
            'phone' => trim($data['phone'] ?? ''),
            'address' => $this->formatAddress($data),
            'payment_method' => $data['payment'],
            'proof_of_payment' => $proofFileName
        ];
        
        // Create order
        $orderId = $this->createOrderWithDetails($customerData, $cartItems, $grandTotal);
        
        // Clear cart after successful order
        $this->clearCart();
        
        return $orderId;
    }
    
    private function formatAddress($data) {
        return trim($data['street']) . ', ' . 
               trim($data['city']) . ', ' . 
               trim($data['province']) . ' ' . 
               trim($data['postal']);
    }
    
    private function handleProofUpload($file) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception("Only JPEG, PNG files are allowed for proof of payment.");
        }
        
        if ($file['size'] > $maxSize) {
            throw new Exception("Proof of payment file is too large. Maximum 5MB allowed.");
        }
        
        $fileName = 'proof_' . time() . '_' . uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $uploadPath = "uploads/proofs/" . $fileName;
        
        // Create directory if it doesn't exist
        if (!file_exists("uploads/proofs/")) {
            mkdir("uploads/proofs/", 0755, true);
        }
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return $fileName;
        } else {
            throw new Exception("Failed to upload proof of payment.");
        }
    }
    
    private function createOrderWithDetails($customerData, $cartItems, $totalAmount) {
        try {
            // Start transaction
            $this->connection->beginTransaction();
            
            // Insert order with additional fields
            $orderId = $this->insert(
                "INSERT INTO orders (customer_name, customer_email, customer_phone, customer_address, total_amount, payment_method, proof_of_payment, order_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')",
                [
                    $customerData['name'],
                    $customerData['email'], 
                    $customerData['phone'],
                    $customerData['address'],
                    $totalAmount,
                    $customerData['payment_method'],
                    $customerData['proof_of_payment']
                ]
            );
            
            // Insert order items and update stock
            foreach ($cartItems as $productId => $item) {
                // Check if enough stock available
                $product = $this->getProductById($productId);
                if (!$product || $product['stock'] < $item['quantity']) {
                    throw new Exception("Insufficient stock for " . $item['name']);
                }
                
                $this->insert(
                    "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)",
                    [$orderId, $productId, $item['name'], $item['price'], $item['quantity'], ($item['price'] * $item['quantity'])]
                );
                
                // Update stock
                $this->update(
                    "UPDATE perfumes SET stock = stock - ? WHERE id = ?",
                    [$item['quantity'], $productId]
                );
            }
            
            // Commit transaction
            $this->connection->commit();
            return $orderId;
            
        } catch (Exception $e) {
            // Rollback on error
            $this->connection->rollBack();
            throw new Exception("Order creation failed: " . $e->getMessage());
        }
    }
    
    public function isUserLoggedIn() {
        return isset($_SESSION['customer_id']) || isset($_SESSION['user_id']);
    }
    
    public function getCheckoutSummary() {
        if ($this->isCartEmpty()) {
            return null;
        }
        
        return [
            'items' => $this->getCartItems(),
            'total' => $this->calculateGrandTotal(),
            'item_count' => array_sum(array_column($this->getCartItems(), 'quantity'))
        ];
    }
    function getImagePath($imageName) {
    if (empty($imageName)) {
        return 'images/placeholder.jpg';
    }
   
    $cleanImageName = basename($imageName);
    $imagePath = "images/" . $cleanImageName;
   
    if (file_exists($imagePath)) {
        return $imagePath;
    } else {
        return 'images/placeholder.jpg';
        }
    }
}
?>