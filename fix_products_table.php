<?php
require_once 'config.php';

echo "<h1>🔧 Fix Products Table</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{background:#d4edda;padding:15px;border-radius:5px;color:#155724;margin:10px 0;} .error{background:#f8d7da;padding:15px;border-radius:5px;color:#721c24;margin:10px 0;} .info{background:#d1ecf1;padding:15px;border-radius:5px;color:#0c5460;margin:10px 0;}</style>";

// Check if products table exists and show its structure
$result = $conn->query("SHOW TABLES LIKE 'products'");
if ($result->num_rows > 0) {
    echo "<div class='info'>📋 Products table exists. Checking structure...</div>";
    
    // Show current table structure
    $structure = $conn->query("DESCRIBE products");
    echo "<h3>Current Table Structure:</h3>";
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    echo "<tr style='background:#f2f2f2;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $columns = [];
    while ($row = $structure->fetch_assoc()) {
        $columns[] = $row['Field'];
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for missing columns and add them
    $requiredColumns = [
        'category' => 'VARCHAR(100)',
        'stock_quantity' => 'INT DEFAULT 0',
        'rating' => 'DECIMAL(3,2) DEFAULT 0',
        'farmer_info' => 'VARCHAR(255)',
        'image_url' => 'TEXT',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ];
    
    $addedColumns = 0;
    foreach ($requiredColumns as $column => $definition) {
        if (!in_array($column, $columns)) {
            $alterSQL = "ALTER TABLE products ADD COLUMN $column $definition";
            if ($conn->query($alterSQL)) {
                echo "<div class='success'>✅ Added column: $column</div>";
                $addedColumns++;
            } else {
                echo "<div class='error'>❌ Failed to add column $column: " . $conn->error . "</div>";
            }
        } else {
            echo "<div class='info'>ℹ️ Column exists: $column</div>";
        }
    }
    
    if ($addedColumns > 0) {
        echo "<div class='success'>🎉 Added $addedColumns missing columns!</div>";
    }
    
} else {
    echo "<div class='error'>❌ Products table does not exist. Creating it...</div>";
    
    // Create the complete products table
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

// Now add the products
echo "<h2>📦 Adding Products</h2>";

$products = [
    [1, 'Organic Tomatoes', 'Fresh, juicy organic tomatoes', 80.00, 'vegetables', 50, 4.5, 'Green Valley Farm'],
    [2, 'Organic Carrots', 'Sweet and crunchy organic carrots', 60.00, 'vegetables', 40, 4.7, 'Sunny Acres'],
    [3, 'Organic Potatoes', 'Versatile organic potatoes', 40.00, 'vegetables', 60, 4.3, 'Mountain View Farm'],
    [4, 'Organic Spinach', 'Nutrient-packed organic spinach', 30.00, 'vegetables', 35, 4.6, 'Leafy Greens Co'],
    [5, 'Organic Onions', 'Flavorful organic onions', 35.00, 'vegetables', 45, 4.4, 'Valley Farms'],
    [6, 'Organic Broccoli', 'Fresh organic broccoli crowns', 120.00, 'vegetables', 25, 4.8, 'Green Thumb Gardens'],
    [7, 'Organic Bell Peppers', 'Colorful organic bell peppers', 100.00, 'vegetables', 30, 4.5, 'Rainbow Harvest'],
    [8, 'Organic Cucumber', 'Crisp and refreshing organic cucumbers', 45.00, 'vegetables', 40, 4.2, 'Fresh Fields'],
    [9, 'Organic Lettuce', 'Fresh organic lettuce leaves', 50.00, 'vegetables', 35, 4.3, 'Crispy Greens Farm'],
    [10, 'Organic Cauliflower', 'Fresh organic cauliflower heads', 70.00, 'vegetables', 20, 4.4, 'White Cloud Farm']
];

$restored = 0;

foreach ($products as $product) {
    list($id, $name, $description, $price, $category, $stock, $rating, $farmer) = $product;
    
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
        $imageUrl = "https://via.placeholder.com/300x300/4CAF50/ffffff?text=" . urlencode($name);
        $stmt->bind_param('ssdsiissi', $name, $description, $price, $category, $stock, $rating, $farmer, $imageUrl, $id);
        
        if ($stmt->execute()) {
            echo "<div class='info'>🔄 Updated: $name</div>";
            $restored++;
        }
    } else {
        // Insert new product
        $stmt = $conn->prepare("
            INSERT INTO products (id, name, description, price, category, stock_quantity, rating, farmer_info, image_url, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $imageUrl = "https://via.placeholder.com/300x300/4CAF50/ffffff?text=" . urlencode($name);
        $stmt->bind_param('issdsiiss', $id, $name, $description, $price, $category, $stock, $rating, $farmer, $imageUrl);
        
        if ($stmt->execute()) {
            echo "<div class='success'>✅ Added: $name</div>";
            $restored++;
        } else {
            echo "<div class='error'>❌ Failed to add $name: " . $stmt->error . "</div>";
        }
    }
}

echo "<div class='success'><h3>🎉 Processed $restored products!</h3></div>";

// Show final table structure
echo "<h2>📋 Final Table Structure</h2>";
$finalStructure = $conn->query("DESCRIBE products");
echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
echo "<tr style='background:#f2f2f2;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while ($row = $finalStructure->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "<td>{$row['Extra']}</td>";
    echo "</tr>";
}
echo "</table>";

// Test the API
echo "<h2>🧪 Testing Products API</h2>";
try {
    $apiTest = file_get_contents('http://localhost/mini%20project/products_api.php?action=list');
    $apiResult = json_decode($apiTest, true);
    
    if ($apiResult && $apiResult['success']) {
        $apiProductCount = count($apiResult['products']);
        echo "<div class='success'>✅ API working! Returns $apiProductCount products</div>";
    } else {
        echo "<div class='error'>❌ API returned error: " . ($apiResult['error'] ?? 'Unknown error') . "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ API test failed: " . $e->getMessage() . "</div>";
}

// Final count
$finalCount = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
echo "<div class='success'>📦 Total products in database: $finalCount</div>";

echo "<div class='success'>";
echo "<h3>🚀 All Fixed! Next Steps:</h3>";
echo "<ol>";
echo "<li><strong>Refresh products page:</strong> <a href='products_simple.html'>products_simple.html</a></li>";
echo "<li><strong>Products should now load properly</strong></li>";
echo "<li><strong>Try adding items to cart</strong></li>";
echo "<li>If issues persist, check browser console (F12)</li>";
echo "</ol>";
echo "</div>";

$conn->close();
?>
