<?php
require_once 'config.php';

echo "<h1>🔄 Ensure Products in Database</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{background:#d4edda;padding:15px;border-radius:5px;color:#155724;margin:10px 0;} .error{background:#f8d7da;padding:15px;border-radius:5px;color:#721c24;margin:10px 0;} .info{background:#d1ecf1;padding:15px;border-radius:5px;color:#0c5460;margin:10px 0;}</style>";

// Check if products table exists
$result = $conn->query("SHOW TABLES LIKE 'products'");
if ($result->num_rows == 0) {
    echo "<div class='error'>❌ Products table does not exist!</div>";
    
    // Create products table
    $createTable = "
    CREATE TABLE products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        category VARCHAR(100),
        stock_quantity INT DEFAULT 0,
        rating DECIMAL(3,2) DEFAULT 0,
        farmer_info VARCHAR(255),
        image_url TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($createTable)) {
        echo "<div class='success'>✅ Products table created successfully!</div>";
    } else {
        echo "<div class='error'>❌ Failed to create products table: " . $conn->error . "</div>";
        exit;
    }
}

// Insert essential products
$products = [
    [1, 'Organic Tomatoes', 'Fresh, juicy organic tomatoes perfect for salads and cooking. Rich in lycopene and vitamin C.', 80.00, 'vegetables', 50, 4.5, 'Green Valley Farm', 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [2, 'Organic Carrots', 'Sweet and crunchy organic carrots, rich in beta-carotene and perfect for snacking.', 60.00, 'vegetables', 40, 4.7, 'Sunny Acres', 'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [3, 'Organic Potatoes', 'Versatile organic potatoes, perfect for roasting, mashing, or frying. Grown without chemicals.', 40.00, 'vegetables', 60, 4.3, 'Mountain View Farm', 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [4, 'Organic Spinach', 'Nutrient-packed organic spinach leaves, perfect for salads, smoothies, and cooking.', 30.00, 'vegetables', 35, 4.6, 'Leafy Greens Co', 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [5, 'Organic Onions', 'Flavorful organic onions that add depth to any dish. Grown using sustainable methods.', 35.00, 'vegetables', 45, 4.4, 'Valley Farms', 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [6, 'Organic Broccoli', 'Fresh organic broccoli crowns, packed with vitamins and minerals for a healthy diet.', 120.00, 'vegetables', 25, 4.8, 'Green Thumb Gardens', 'https://images.unsplash.com/photo-1459411621453-7b03977f4bfc?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [7, 'Organic Bell Peppers', 'Colorful organic bell peppers, sweet and crunchy, perfect for stir-fries and salads.', 100.00, 'vegetables', 30, 4.5, 'Rainbow Harvest', 'https://images.unsplash.com/photo-1563565375-f3fdfdbefa83?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [8, 'Organic Cucumber', 'Crisp and refreshing organic cucumbers, perfect for salads and healthy snacking.', 45.00, 'vegetables', 40, 4.2, 'Fresh Fields', 'https://images.unsplash.com/photo-1449300079323-02e209d9d3a6?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [9, 'Organic Lettuce', 'Fresh organic lettuce leaves, perfect base for salads and sandwiches.', 50.00, 'vegetables', 35, 4.3, 'Crispy Greens Farm', 'https://images.unsplash.com/photo-1622206151226-18ca2c9ab4a1?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [10, 'Organic Cauliflower', 'Fresh organic cauliflower heads, versatile and nutritious for various cooking methods.', 70.00, 'vegetables', 20, 4.4, 'White Cloud Farm', 'https://images.unsplash.com/photo-1568584711271-946d4d46b7d5?w=500&h=500&fit=crop&crop=center&auto=format&q=80']
];

$addedCount = 0;
$updatedCount = 0;

foreach ($products as $product) {
    list($id, $name, $description, $price, $category, $stock, $rating, $farmer, $image) = $product;
    
    // Check if product exists
    $checkStmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $checkStmt->bind_param('i', $id);
    $checkStmt->execute();
    $exists = $checkStmt->get_result()->num_rows > 0;
    
    if ($exists) {
        // Update existing product
        $stmt = $conn->prepare("
            UPDATE products 
            SET name = ?, description = ?, price = ?, category = ?, stock_quantity = ?, rating = ?, farmer_info = ?, image_url = ?
            WHERE id = ?
        ");
        $stmt->bind_param('ssdsiissi', $name, $description, $price, $category, $stock, $rating, $farmer, $image, $id);
        
        if ($stmt->execute()) {
            echo "<div class='info'>🔄 Updated: $name</div>";
            $updatedCount++;
        }
    } else {
        // Insert new product
        $stmt = $conn->prepare("
            INSERT INTO products (id, name, description, price, category, stock_quantity, rating, farmer_info, image_url, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param('issdsiiss', $id, $name, $description, $price, $category, $stock, $rating, $farmer, $image);
        
        if ($stmt->execute()) {
            echo "<div class='success'>✅ Added: $name</div>";
            $addedCount++;
        }
    }
}

echo "<div class='success'>";
echo "<h3>📊 Summary:</h3>";
echo "<p>➕ Added: $addedCount products</p>";
echo "<p>🔄 Updated: $updatedCount products</p>";
echo "</div>";

// Test products API
echo "<h2>🧪 Test Products API</h2>";
$testUrl = "http://localhost/mini project/products_api.php?action=list";
echo "<div class='info'>Testing: <a href='$testUrl' target='_blank'>$testUrl</a></div>";

// Show current products
$result = $conn->query("SELECT id, name, price, category, stock_quantity FROM products ORDER BY id LIMIT 10");
if ($result->num_rows > 0) {
    echo "<h3>📦 Products in Database:</h3>";
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    echo "<tr style='background:#f2f2f2;'><th>ID</th><th>Name</th><th>Price</th><th>Category</th><th>Stock</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td>₹{$row['price']}</td>";
        echo "<td>{$row['category']}</td>";
        echo "<td>{$row['stock_quantity']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<div class='success'>";
echo "<h3>🎯 Next Steps:</h3>";
echo "<ol>";
echo "<li>Products are now in database</li>";
echo "<li>Go to <a href='products_simple.html'>products page</a></li>";
echo "<li>Products should now load from database</li>";
echo "<li>Try adding products to cart - they should work now!</li>";
echo "<li>Check browser console (F12) for any errors</li>";
echo "</ol>";
echo "</div>";

$conn->close();
?>
