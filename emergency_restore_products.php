<?php
require_once 'config.php';

echo "<h1>🚨 Emergency Product Restore</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{background:#d4edda;padding:15px;border-radius:5px;color:#155724;margin:10px 0;} .error{background:#f8d7da;padding:15px;border-radius:5px;color:#721c24;margin:10px 0;} .info{background:#d1ecf1;padding:15px;border-radius:5px;color:#0c5460;margin:10px 0;}</style>";

// Check current products count
$currentCount = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
echo "<div class='info'>📦 Current products in database: $currentCount</div>";

// Essential products to restore immediately
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
    
    // Use INSERT IGNORE to avoid duplicates
    $stmt = $conn->prepare("
        INSERT IGNORE INTO products (id, name, description, price, category, stock_quantity, rating, farmer_info, image_url, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'https://via.placeholder.com/300x300/4CAF50/ffffff?text=' || ?, NOW())
    ");
    
    $imageText = urlencode($name);
    $stmt->bind_param('issdsiiss', $id, $name, $description, $price, $category, $stock, $rating, $farmer, $imageText);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo "<div class='success'>✅ Restored: $name</div>";
        $restored++;
    } else {
        echo "<div class='info'>ℹ️ Already exists: $name</div>";
    }
}

echo "<div class='success'><h3>🎉 Restored $restored products!</h3></div>";

// Test the products API
echo "<h2>🧪 Testing Products API</h2>";
$apiTest = file_get_contents('http://localhost/mini%20project/products_api.php?action=list');
$apiResult = json_decode($apiTest, true);

if ($apiResult && $apiResult['success']) {
    $apiProductCount = count($apiResult['products']);
    echo "<div class='success'>✅ API working! Returns $apiProductCount products</div>";
    
    echo "<h3>📋 API Response Sample:</h3>";
    echo "<pre style='background:#f8f9fa;padding:10px;border-radius:5px;overflow:auto;'>";
    echo json_encode(array_slice($apiResult['products'], 0, 2), JSON_PRETTY_PRINT);
    echo "</pre>";
} else {
    echo "<div class='error'>❌ API not working properly</div>";
    echo "<pre style='background:#f8f9fa;padding:10px;border-radius:5px;'>";
    echo htmlspecialchars($apiTest);
    echo "</pre>";
}

// Final count
$finalCount = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
echo "<div class='info'>📦 Final products in database: $finalCount</div>";

echo "<div class='success'>";
echo "<h3>🚀 Next Steps:</h3>";
echo "<ol>";
echo "<li><strong>Refresh products page:</strong> <a href='products_simple.html'>products_simple.html</a></li>";
echo "<li><strong>Open browser console</strong> (F12) to see loading messages</li>";
echo "<li><strong>Products should appear now</strong> - either from database or static fallback</li>";
echo "<li>If still empty, check console for error messages</li>";
echo "</ol>";
echo "</div>";

$conn->close();
?>
