<?php
require_once 'config.php';

echo "<h1>🛒 Quick Product Fix</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{background:#d4edda;padding:15px;border-radius:5px;color:#155724;margin:10px 0;} .error{background:#f8d7da;padding:15px;border-radius:5px;color:#721c24;margin:10px 0;} .info{background:#d1ecf1;padding:15px;border-radius:5px;color:#0c5460;margin:10px 0;}</style>";

// Ensure products table exists with required structure
$result = $conn->query("SHOW TABLES LIKE 'products'");
if ($result->num_rows === 0) {
    echo "<div class='info'>ℹ️ Products table not found. Creating table...</div>";
    $createSql = "
        CREATE TABLE products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            category VARCHAR(100) DEFAULT 'general',
            category_id INT NULL,
            stock_quantity INT DEFAULT 0,
            rating DECIMAL(3,2) DEFAULT 4.5,
            farmer_info VARCHAR(255) DEFAULT 'Local Farm',
            image_url TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    if ($conn->query($createSql)) {
        echo "<div class='success'>✅ Products table created successfully.</div>";
    } else {
        echo "<div class='error'>❌ Failed to create products table: " . $conn->error . "</div>";
        exit;
    }
} else {
    // Ensure required columns exist
    $columns = [];
    $structure = $conn->query("DESCRIBE products");
    while ($row = $structure->fetch_assoc()) {
        $columns[] = $row['Field'];
    }

    $requiredColumns = [
        'category' => "ALTER TABLE products ADD COLUMN category VARCHAR(100) DEFAULT 'general' AFTER price",
        'category_id' => "ALTER TABLE products ADD COLUMN category_id INT NULL AFTER category",
        'stock_quantity' => "ALTER TABLE products ADD COLUMN stock_quantity INT DEFAULT 0 AFTER category_id",
        'rating' => "ALTER TABLE products ADD COLUMN rating DECIMAL(3,2) DEFAULT 4.5 AFTER stock_quantity",
        'farmer_info' => "ALTER TABLE products ADD COLUMN farmer_info VARCHAR(255) DEFAULT 'Local Farm' AFTER rating",
        'image_url' => "ALTER TABLE products ADD COLUMN image_url TEXT AFTER farmer_info",
        'created_at' => "ALTER TABLE products ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER image_url",
        'updated_at' => "ALTER TABLE products ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at"
    ];

    foreach ($requiredColumns as $column => $sql) {
        if (!in_array($column, $columns)) {
            if ($conn->query($sql)) {
                echo "<div class='success'>✅ Added missing column: $column</div>";
            } else {
                echo "<div class='error'>❌ Failed to add column $column: " . $conn->error . "</div>";
                exit;
            }
        }
    }
}

// Complete product list with proper data
$products = [
    [1, 'Organic Tomatoes', 'Fresh, juicy organic tomatoes perfect for salads and cooking. Rich in lycopene and vitamin C.', 80.00, 'vegetables', 50, 4.5, 'Green Valley Farm', 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [2, 'Organic Carrots', 'Sweet and crunchy organic carrots, rich in beta-carotene and perfect for snacking or cooking.', 60.00, 'vegetables', 40, 4.7, 'Sunny Acres', 'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [3, 'Organic Potatoes', 'Versatile organic potatoes, perfect for roasting, mashing, or frying. Grown without chemicals.', 40.00, 'vegetables', 60, 4.3, 'Mountain View Farm', 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [4, 'Organic Spinach', 'Nutrient-packed organic spinach leaves, perfect for salads, smoothies, and cooking.', 30.00, 'vegetables', 35, 4.6, 'Leafy Greens Co', 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [5, 'Organic Onions', 'Flavorful organic onions that add depth to any dish. Grown using sustainable methods.', 35.00, 'vegetables', 45, 4.4, 'Valley Farms', 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [6, 'Organic Broccoli', 'Fresh organic broccoli crowns, packed with vitamins and minerals for a healthy diet.', 120.00, 'vegetables', 25, 4.8, 'Green Thumb Gardens', 'https://images.unsplash.com/photo-1459411621453-7b03977f4bfc?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [7, 'Organic Bell Peppers', 'Colorful organic bell peppers, sweet and crunchy, perfect for stir-fries and salads.', 100.00, 'vegetables', 30, 4.5, 'Rainbow Harvest', 'https://images.unsplash.com/photo-1563565375-f3fdfdbefa83?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [8, 'Organic Cucumber', 'Crisp and refreshing organic cucumbers, perfect for salads and healthy snacking.', 45.00, 'vegetables', 40, 4.2, 'Fresh Fields', 'https://images.unsplash.com/photo-1449300079323-02e209d9d3a6?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [9, 'Organic Lettuce', 'Fresh organic lettuce leaves, perfect base for salads and sandwiches.', 50.00, 'vegetables', 35, 4.3, 'Crispy Greens Farm', 'https://images.unsplash.com/photo-1622206151226-18ca2c9ab4a1?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [10, 'Organic Cauliflower', 'Fresh organic cauliflower heads, versatile and nutritious for various cooking methods.', 70.00, 'vegetables', 20, 4.4, 'White Cloud Farm', 'https://images.unsplash.com/photo-1568584711271-946d4d46b7d5?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [11, 'Organic Apples', 'Crisp and sweet organic apples, perfect for snacking or baking.', 90.00, 'fruits', 45, 4.6, 'Orchard Hills', 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [12, 'Organic Bananas', 'Creamy organic bananas, rich in potassium and perfect for smoothies.', 50.00, 'fruits', 55, 4.4, 'Tropical Farms', 'https://images.unsplash.com/photo-1543286986-842a6e0b4fbd?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [13, 'Organic Oranges', 'Juicy organic oranges, packed with vitamin C and perfect for fresh juice.', 75.00, 'fruits', 40, 4.7, 'Citrus Grove', 'https://images.unsplash.com/photo-1547514701-42782101795e?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [14, 'Organic Mangoes', 'Sweet and tropical organic mangoes, perfect for desserts and smoothies.', 120.00, 'fruits', 30, 4.8, 'Sunshine Farms', 'https://images.unsplash.com/photo-1553279768-865429fa0078?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [15, 'Organic Grapes', 'Sweet organic grapes, perfect for snacking or making wine.', 150.00, 'fruits', 25, 4.5, 'Vineyard Estate', 'https://images.unsplash.com/photo-1537640538966-79f369143f8f?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [16, 'Organic Milk', 'Fresh organic whole milk from grass-fed cows.', 60.00, 'dairy', 35, 4.6, 'Happy Cows Dairy', 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [17, 'Organic Yogurt', 'Creamy organic yogurt with live cultures.', 45.00, 'dairy', 30, 4.4, 'Probiotic Farms', 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [18, 'Organic Cheese', 'Artisanal organic cheese made from fresh milk.', 200.00, 'dairy', 20, 4.7, 'Cheese Crafters', 'https://images.unsplash.com/photo-1486477633814-6a0630239e6c?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [19, 'Organic Cilantro', 'Fresh organic cilantro for authentic flavors in Mexican and Asian cuisine.', 30.00, 'herbs', 30, 4.2, 'Spice Garden', 'https://images.unsplash.com/photo-1618375569909-3c8616cf7733?w=500&h=500&fit=crop&crop=center&auto=format&q=80'],
    [20, 'Organic Basil', 'Aromatic organic basil perfect for Italian dishes and pesto.', 35.00, 'herbs', 25, 4.5, 'Herb Haven', 'https://images.unsplash.com/photo-1581375321224-79da6fd32f6e?w=500&h=500&fit=crop&crop=center&auto=format&q=80']
];

echo "<h2>🔄 Updating Products...</h2>";

$updated = 0;
$added = 0;

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
            $updated++;
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
            $added++;
        }
    }
}

echo "<div class='success'>";
echo "<h3>📊 Results:</h3>";
echo "<p>➕ Added: $added products</p>";
echo "<p>🔄 Updated: $updated products</p>";
echo "</div>";

// Show current products
echo "<h2>📦 Current Products in Database</h2>";
$result = $conn->query("SELECT id, name, price, category, stock_quantity FROM products ORDER BY category, id");
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

// Test API
echo "<h2>🧪 Testing Products API</h2>";
try {
    $apiTest = file_get_contents('http://localhost/mini%20project/products_api.php?action=list');
    $apiResult = json_decode($apiTest, true);
    
    if ($apiResult && $apiResult['success']) {
        $apiProductCount = count($apiResult['products']);
        echo "<div class='success'>✅ API working! Returns $apiProductCount products</div>";
    } else {
        echo "<div class='error'>❌ API error: " . ($apiResult['error'] ?? 'Unknown') . "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ API test failed: " . $e->getMessage() . "</div>";
}

echo "<div class='success'>";
echo "<h3>🚀 Admin Panel Links:</h3>";
echo "<p><strong>📊 Admin Dashboard:</strong> <a href='admin_static.html'>admin_static.html</a></p>";
echo "<p><strong>🛒 Products Management:</strong> <a href='products_db.html'>products_db.html</a></p>";
echo "<p><strong>📦 Orders Management:</strong> <a href='orders_api.php'>orders_api.php</a> (API)</p>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>🎯 Next Steps:</h3>";
echo "<ol>";
echo "<li><strong>Refresh products page:</strong> <a href='products_simple.html'>products_simple.html</a></li>";
echo "<li><strong>Visit admin panel:</strong> <a href='admin_static.html'>admin_static.html</a></li>";
echo "<li><strong>Test cart functionality</strong></li>";
echo "<li><strong>Check orders in admin panel</strong></li>";
echo "</ol>";
echo "</div>";

$conn->close();
?>
