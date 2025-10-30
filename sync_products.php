<?php
require_once 'config.php';

echo "<h1>🔄 Sync Static Products to Database</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{background:#d4edda;padding:15px;border-radius:5px;color:#155724;margin:10px 0;} .error{background:#f8d7da;padding:15px;border-radius:5px;color:#721c24;margin:10px 0;} .info{background:#d1ecf1;padding:15px;border-radius:5px;color:#0c5460;margin:10px 0;}</style>";

// Read the static products data from JavaScript file
$jsFile = file_get_contents('static-products-data.js');

// Extract the products array using regex
preg_match('/const staticProducts = \[(.*?)\];/s', $jsFile, $matches);

if (!$matches) {
    echo "<div class='error'>❌ Could not parse static-products-data.js</div>";
    exit;
}

// Parse the JavaScript array (simplified parsing)
$productsText = $matches[1];

// Split by product objects
$productBlocks = explode('},', $productsText);

$syncedCount = 0;
$errorCount = 0;

echo "<div class='info'>📦 Found " . count($productBlocks) . " product blocks to process</div>";

foreach ($productBlocks as $block) {
    // Extract product data using regex
    if (preg_match('/id:\s*(\d+)/', $block, $idMatch) &&
        preg_match('/name:\s*[\'"]([^\'"]+)[\'"]/', $block, $nameMatch) &&
        preg_match('/price:\s*([\d.]+)/', $block, $priceMatch) &&
        preg_match('/category:\s*[\'"]([^\'"]+)[\'"]/', $block, $categoryMatch)) {
        
        $id = (int)$idMatch[1];
        $name = $nameMatch[1];
        $price = (float)$priceMatch[1];
        $category = $categoryMatch[1];
        
        // Extract other fields
        $description = '';
        if (preg_match('/description:\s*[\'"]([^\'"]+)[\'"]/', $block, $descMatch)) {
            $description = $descMatch[1];
        }
        
        $stock = 50; // default
        if (preg_match('/stock_quantity:\s*(\d+)/', $block, $stockMatch)) {
            $stock = (int)$stockMatch[1];
        }
        
        $rating = 4.0; // default
        if (preg_match('/rating:\s*([\d.]+)/', $block, $ratingMatch)) {
            $rating = (float)$ratingMatch[1];
        }
        
        $farmer = 'Local Farm'; // default
        if (preg_match('/farmer_info:\s*[\'"]([^\'"]+)[\'"]/', $block, $farmerMatch)) {
            $farmer = $farmerMatch[1];
        }
        
        $image = '';
        if (preg_match('/image_url:\s*[\'"]([^\'"]+)[\'"]/', $block, $imageMatch)) {
            $image = $imageMatch[1];
        }
        
        try {
            // Check if product exists
            $checkStmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
            $checkStmt->bind_param('i', $id);
            $checkStmt->execute();
            $exists = $checkStmt->get_result()->num_rows > 0;
            
            if ($exists) {
                // Update existing product
                $stmt = $conn->prepare("
                    UPDATE products 
                    SET name = ?, description = ?, price = ?, category = ?, 
                        stock_quantity = ?, rating = ?, farmer_info = ?, image_url = ?
                    WHERE id = ?
                ");
                $stmt->bind_param('ssdsiissi', $name, $description, $price, $category, 
                                $stock, $rating, $farmer, $image, $id);
            } else {
                // Insert new product
                $stmt = $conn->prepare("
                    INSERT INTO products (id, name, description, price, category, 
                                        stock_quantity, rating, farmer_info, image_url, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->bind_param('issdsiiss', $id, $name, $description, $price, $category, 
                                $stock, $rating, $farmer, $image);
            }
            
            if ($stmt->execute()) {
                echo "<div class='success'>✅ " . ($exists ? 'Updated' : 'Added') . " product: $name (ID: $id)</div>";
                $syncedCount++;
            } else {
                echo "<div class='error'>❌ Failed to sync product: $name - " . $stmt->error . "</div>";
                $errorCount++;
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error syncing product $name: " . $e->getMessage() . "</div>";
            $errorCount++;
        }
    }
}

echo "<div class='info'>";
echo "<h3>📊 Sync Results:</h3>";
echo "<p>✅ Successfully synced: $syncedCount products</p>";
echo "<p>❌ Errors: $errorCount products</p>";
echo "</div>";

// Show current products in database
echo "<h2>📋 Products in Database</h2>";
$result = $conn->query("SELECT id, name, price, category, stock_quantity FROM products ORDER BY id");

if ($result->num_rows > 0) {
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
} else {
    echo "<div class='error'>❌ No products found in database</div>";
}

echo "<div class='success'>";
echo "<h3>🎯 Next Steps:</h3>";
echo "<ol>";
echo "<li>Products are now synced to database</li>";
echo "<li>Go to <a href='products_simple.html'>products page</a> and try adding items to cart</li>";
echo "<li>Check <a href='debug_cart.php'>cart debug</a> to see if items are added</li>";
echo "<li>Visit <a href='cart_static.html'>cart page</a> to see items</li>";
echo "</ol>";
echo "</div>";

$conn->close();
?>
