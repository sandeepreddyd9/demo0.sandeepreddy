<?php
require_once 'config.php';

echo "<h1>🔍 Cart Debug Tool</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{background:#d4edda;padding:15px;border-radius:5px;color:#155724;margin:10px 0;} .error{background:#f8d7da;padding:15px;border-radius:5px;color:#721c24;margin:10px 0;} .info{background:#d1ecf1;padding:15px;border-radius:5px;color:#0c5460;margin:10px 0;} table{border-collapse:collapse;width:100%;margin:10px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;}</style>";

// Check all users
echo "<h2>👥 All Users</h2>";
$users = $conn->query("SELECT id, name, email FROM users ORDER BY id");
if ($users->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Cart Items</th><th>Action</th></tr>";
    
    while ($user = $users->fetch_assoc()) {
        $cartCount = $conn->query("SELECT COUNT(*) as count FROM cart WHERE user_id = {$user['id']}")->fetch_assoc()['count'];
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['name']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>$cartCount items</td>";
        echo "<td><a href='?view_cart={$user['id']}'>View Cart</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='error'>❌ No users found</div>";
}

// Show specific user's cart if requested
if (isset($_GET['view_cart'])) {
    $userId = (int)$_GET['view_cart'];
    
    echo "<h2>🛒 Cart for User ID: $userId</h2>";
    
    $cartItems = $conn->query("
        SELECT c.*, p.name as product_name, p.price as product_price, p.image_url
        FROM cart c
        LEFT JOIN products p ON c.product_id = p.id
        WHERE c.user_id = $userId
        ORDER BY c.created_at DESC
    ");
    
    if ($cartItems->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>Product ID</th><th>Product Name</th><th>Quantity</th><th>Price</th><th>Total</th><th>Added</th></tr>";
        
        $total = 0;
        while ($item = $cartItems->fetch_assoc()) {
            $itemTotal = $item['quantity'] * $item['product_price'];
            $total += $itemTotal;
            
            echo "<tr>";
            echo "<td>{$item['product_id']}</td>";
            echo "<td>{$item['product_name']}</td>";
            echo "<td>{$item['quantity']}</td>";
            echo "<td>₹{$item['product_price']}</td>";
            echo "<td>₹" . number_format($itemTotal, 2) . "</td>";
            echo "<td>{$item['created_at']}</td>";
            echo "</tr>";
        }
        
        echo "<tr style='background:#f8f9fa; font-weight:bold;'>";
        echo "<td colspan='4'>Total</td>";
        echo "<td>₹" . number_format($total, 2) . "</td>";
        echo "<td></td>";
        echo "</tr>";
        echo "</table>";
        
        echo "<div class='success'>✅ Cart has {$cartItems->num_rows} items totaling ₹" . number_format($total, 2) . "</div>";
    } else {
        echo "<div class='info'>ℹ️ Cart is empty for this user</div>";
    }
}

// Show all cart items
echo "<h2>🛍️ All Cart Items</h2>";
$allCartItems = $conn->query("
    SELECT c.*, u.name as user_name, p.name as product_name, p.price
    FROM cart c
    LEFT JOIN users u ON c.user_id = u.id
    LEFT JOIN products p ON c.product_id = p.id
    ORDER BY c.created_at DESC
    LIMIT 20
");

if ($allCartItems->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>User</th><th>Product</th><th>Quantity</th><th>Price</th><th>Added</th></tr>";
    
    while ($item = $allCartItems->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$item['user_name']} (ID: {$item['user_id']})</td>";
        echo "<td>{$item['product_name']} (ID: {$item['product_id']})</td>";
        echo "<td>{$item['quantity']}</td>";
        echo "<td>₹{$item['price']}</td>";
        echo "<td>{$item['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='info'>ℹ️ No cart items found in database</div>";
}

// Test cart API
echo "<h2>🧪 Test Cart API</h2>";
echo "<div class='info'>";
echo "<p><strong>To test the cart system:</strong></p>";
echo "<ol>";
echo "<li>Login to your website</li>";
echo "<li>Add some products to cart</li>";
echo "<li>Refresh this page to see if items appear in database</li>";
echo "<li>Check the cart page to see if items display</li>";
echo "</ol>";
echo "</div>";

// Show recent products for testing
echo "<h2>🥬 Available Products (for testing)</h2>";
$products = $conn->query("SELECT id, name, price FROM products LIMIT 10");
if ($products->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Price</th></tr>";
    
    while ($product = $products->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$product['id']}</td>";
        echo "<td>{$product['name']}</td>";
        echo "<td>₹{$product['price']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='error'>❌ No products found. Cart system needs products to work.</div>";
}

$conn->close();
?>
