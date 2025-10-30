<?php
require_once 'config.php';

echo "<h1>🛒 Cart System Test</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{background:#d4edda;padding:15px;border-radius:5px;color:#155724;margin:10px 0;} .error{background:#f8d7da;padding:15px;border-radius:5px;color:#721c24;margin:10px 0;} .info{background:#d1ecf1;padding:15px;border-radius:5px;color:#0c5460;margin:10px 0;}</style>";

// Check if all required tables exist
$tables = ['users', 'products', 'cart', 'orders', 'order_items'];
$missingTables = [];

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows == 0) {
        $missingTables[] = $table;
    }
}

if (!empty($missingTables)) {
    echo "<div class='error'>❌ Missing tables: " . implode(', ', $missingTables) . "</div>";
    echo "<p>Please run the setup scripts first:</p>";
    echo "<ul>";
    echo "<li><a href='setup_orders_table.php'>Setup Orders Tables</a></li>";
    echo "</ul>";
    exit;
}

echo "<div class='success'>✅ All required tables exist</div>";

// Check if there are users
$userResult = $conn->query("SELECT COUNT(*) as count FROM users");
$userCount = $userResult->fetch_assoc()['count'];

echo "<div class='info'>👥 Users in database: $userCount</div>";

if ($userCount == 0) {
    echo "<div class='error'>❌ No users found. Please register a user first.</div>";
} else {
    // Show sample user
    $sampleUser = $conn->query("SELECT id, name, email FROM users LIMIT 1")->fetch_assoc();
    echo "<div class='info'>📝 Sample user: {$sampleUser['name']} ({$sampleUser['email']}) - ID: {$sampleUser['id']}</div>";
    
    // Check cart for this user
    $cartResult = $conn->query("SELECT COUNT(*) as count FROM cart WHERE user_id = {$sampleUser['id']}");
    $cartCount = $cartResult->fetch_assoc()['count'];
    echo "<div class='info'>🛒 Cart items for {$sampleUser['name']}: $cartCount</div>";
    
    if ($cartCount > 0) {
        // Show cart contents
        $cartItems = $conn->query("
            SELECT c.*, p.name as product_name, p.price 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = {$sampleUser['id']}
        ");
        
        echo "<h3>🛍️ Cart Contents:</h3>";
        echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
        echo "<tr style='background:#f2f2f2;'><th>Product</th><th>Quantity</th><th>Price</th><th>Total</th></tr>";
        
        $total = 0;
        while ($item = $cartItems->fetch_assoc()) {
            $itemTotal = $item['quantity'] * $item['price'];
            $total += $itemTotal;
            echo "<tr>";
            echo "<td>{$item['product_name']}</td>";
            echo "<td>{$item['quantity']}</td>";
            echo "<td>₹{$item['price']}</td>";
            echo "<td>₹" . number_format($itemTotal, 2) . "</td>";
            echo "</tr>";
        }
        echo "<tr style='background:#f8f9fa; font-weight:bold;'>";
        echo "<td colspan='3'>Total</td>";
        echo "<td>₹" . number_format($total, 2) . "</td>";
        echo "</tr>";
        echo "</table>";
    }
}

// Check if there are products
$productResult = $conn->query("SELECT COUNT(*) as count FROM products");
$productCount = $productResult->fetch_assoc()['count'];

echo "<div class='info'>🥬 Products in database: $productCount</div>";

// Check orders
$orderResult = $conn->query("SELECT COUNT(*) as count FROM orders");
$orderCount = $orderResult->fetch_assoc()['count'];

echo "<div class='info'>📦 Orders in database: $orderCount</div>";

if ($orderCount > 0) {
    // Show recent orders
    $recentOrders = $conn->query("
        SELECT o.*, u.name as user_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC 
        LIMIT 5
    ");
    
    echo "<h3>📋 Recent Orders:</h3>";
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    echo "<tr style='background:#f2f2f2;'><th>Order ID</th><th>User</th><th>Total</th><th>Status</th><th>Date</th></tr>";
    
    while ($order = $recentOrders->fetch_assoc()) {
        echo "<tr>";
        echo "<td>#{$order['id']}</td>";
        echo "<td>{$order['user_name']}</td>";
        echo "<td>₹{$order['total_amount']}</td>";
        echo "<td>{$order['status']}</td>";
        echo "<td>{$order['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<div class='success'>";
echo "<h3>🎯 Testing Steps:</h3>";
echo "<ol>";
echo "<li><strong>Login:</strong> Go to <a href='products_simple.html'>products page</a> and login</li>";
echo "<li><strong>Add to Cart:</strong> Click 'Add to Cart' on any product</li>";
echo "<li><strong>View Cart:</strong> Go to <a href='cart_static.html'>cart page</a> to see items</li>";
echo "<li><strong>Checkout:</strong> Click 'Proceed to Checkout' and fill details</li>";
echo "<li><strong>Place Order:</strong> Complete the order process</li>";
echo "<li><strong>Verify:</strong> Refresh this page to see the new order</li>";
echo "</ol>";
echo "</div>";

$conn->close();
?>
