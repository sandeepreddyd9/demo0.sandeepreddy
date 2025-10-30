<?php
require_once 'config.php';

echo "<h1>🛒 Quick Cart Fix</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{background:#d4edda;padding:15px;border-radius:5px;color:#155724;margin:10px 0;} .error{background:#f8d7da;padding:15px;border-radius:5px;color:#721c24;margin:10px 0;} .info{background:#d1ecf1;padding:15px;border-radius:5px;color:#0c5460;margin:10px 0;}</style>";

// First, let's add the basic products that are commonly used
$basicProducts = [
    ['id' => 1, 'name' => 'Organic Tomatoes', 'price' => 80.00, 'category' => 'vegetables'],
    ['id' => 2, 'name' => 'Organic Carrots', 'price' => 60.00, 'category' => 'vegetables'], 
    ['id' => 3, 'name' => 'Organic Potatoes', 'price' => 40.00, 'category' => 'vegetables'],
    ['id' => 4, 'name' => 'Organic Spinach', 'price' => 30.00, 'category' => 'vegetables'],
    ['id' => 5, 'name' => 'Organic Onions', 'price' => 35.00, 'category' => 'vegetables'],
    ['id' => 6, 'name' => 'Organic Broccoli', 'price' => 120.00, 'category' => 'vegetables'],
    ['id' => 7, 'name' => 'Organic Bell Peppers', 'price' => 100.00, 'category' => 'vegetables'],
    ['id' => 8, 'name' => 'Organic Cucumber', 'price' => 45.00, 'category' => 'vegetables'],
    ['id' => 9, 'name' => 'Organic Lettuce', 'price' => 50.00, 'category' => 'vegetables'],
    ['id' => 10, 'name' => 'Organic Cauliflower', 'price' => 70.00, 'category' => 'vegetables']
];

$addedCount = 0;
$updatedCount = 0;

foreach ($basicProducts as $product) {
    try {
        // Check if product exists
        $checkStmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
        $checkStmt->bind_param('i', $product['id']);
        $checkStmt->execute();
        $exists = $checkStmt->get_result()->num_rows > 0;
        
        if ($exists) {
            // Update existing product
            $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, category = ? WHERE id = ?");
            $stmt->bind_param('sdsi', $product['name'], $product['price'], $product['category'], $product['id']);
            if ($stmt->execute()) {
                echo "<div class='info'>✅ Updated: {$product['name']}</div>";
                $updatedCount++;
            }
        } else {
            // Insert new product
            $stmt = $conn->prepare("
                INSERT INTO products (id, name, description, price, category, stock_quantity, rating, farmer_info, image_url, created_at)
                VALUES (?, ?, ?, ?, ?, 50, 4.5, 'Local Farm', 'https://via.placeholder.com/300x300/4CAF50/ffffff?text=' || ?, NOW())
            ");
            $description = "Fresh organic " . strtolower($product['name']);
            $imageText = urlencode($product['name']);
            
            $stmt->bind_param('issdss', 
                $product['id'], 
                $product['name'], 
                $description, 
                $product['price'], 
                $product['category'],
                $imageText
            );
            
            if ($stmt->execute()) {
                echo "<div class='success'>✅ Added: {$product['name']}</div>";
                $addedCount++;
            }
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error with {$product['name']}: " . $e->getMessage() . "</div>";
    }
}

echo "<div class='success'>";
echo "<h3>📊 Results:</h3>";
echo "<p>➕ Added: $addedCount products</p>";
echo "<p>🔄 Updated: $updatedCount products</p>";
echo "</div>";

// Test cart functionality
echo "<h2>🧪 Test Cart API</h2>";

// Check if we have users
$userResult = $conn->query("SELECT id, name, email FROM users LIMIT 1");
if ($userResult->num_rows > 0) {
    $user = $userResult->fetch_assoc();
    echo "<div class='info'>👤 Testing with user: {$user['name']} (ID: {$user['id']})</div>";
    
    // Try to add product to cart via direct database insert
    try {
        $testProductId = 3; // Potato
        
        // Check if already in cart
        $checkCart = $conn->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
        $checkCart->bind_param('ii', $user['id'], $testProductId);
        $checkCart->execute();
        
        if ($checkCart->get_result()->num_rows > 0) {
            echo "<div class='info'>ℹ️ Product already in cart for this user</div>";
        } else {
            // Add to cart
            $addCart = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, 1, NOW())");
            $addCart->bind_param('ii', $user['id'], $testProductId);
            
            if ($addCart->execute()) {
                echo "<div class='success'>✅ Successfully added product to cart via database</div>";
            } else {
                echo "<div class='error'>❌ Failed to add to cart: " . $addCart->error . "</div>";
            }
        }
        
        // Show current cart
        $cartItems = $conn->query("
            SELECT c.*, p.name as product_name, p.price 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = {$user['id']}
        ");
        
        if ($cartItems->num_rows > 0) {
            echo "<h3>🛍️ Current Cart:</h3>";
            echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
            echo "<tr style='background:#f2f2f2;'><th>Product</th><th>Quantity</th><th>Price</th></tr>";
            
            while ($item = $cartItems->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$item['product_name']}</td>";
                echo "<td>{$item['quantity']}</td>";
                echo "<td>₹{$item['price']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Cart test failed: " . $e->getMessage() . "</div>";
    }
    
} else {
    echo "<div class='error'>❌ No users found. Please register first.</div>";
}

echo "<div class='info'>";
echo "<h3>🎯 Next Steps:</h3>";
echo "<ol>";
echo "<li>Products are now in database</li>";
echo "<li>Go to <a href='products_simple.html'>products page</a></li>";
echo "<li>Login with your account</li>";
echo "<li>Try adding different products to cart</li>";
echo "<li>Check <a href='cart_static.html'>cart page</a></li>";
echo "<li>For checkout issues, check browser console (F12) for validation errors</li>";
echo "</ol>";
echo "</div>";

$conn->close();
?>
