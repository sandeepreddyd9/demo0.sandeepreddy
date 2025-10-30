<?php
require_once 'config.php';

echo "<h1>🛒 Setting up Orders Tables</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{background:#d4edda;padding:15px;border-radius:5px;color:#155724;margin:10px 0;} .error{background:#f8d7da;padding:15px;border-radius:5px;color:#721c24;margin:10px 0;}</style>";

// Create orders table
$createOrdersTable = "
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_address TEXT NOT NULL,
    payment_method VARCHAR(50) NOT NULL DEFAULT 'cod',
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($createOrdersTable) === TRUE) {
    echo "<div class='success'>✅ Orders table created/verified successfully</div>";
} else {
    echo "<div class='error'>❌ Error creating orders table: " . $conn->error . "</div>";
}

// Create order_items table
$createOrderItemsTable = "
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)";

if ($conn->query($createOrderItemsTable) === TRUE) {
    echo "<div class='success'>✅ Order Items table created/verified successfully</div>";
} else {
    echo "<div class='error'>❌ Error creating order_items table: " . $conn->error . "</div>";
}

// Check if tables exist and show structure
echo "<h2>📋 Database Structure</h2>";

$tables = ['orders', 'order_items'];
foreach ($tables as $table) {
    $result = $conn->query("DESCRIBE $table");
    if ($result && $result->num_rows > 0) {
        echo "<h3>Table: $table</h3>";
        echo "<table border='1' style='border-collapse:collapse; width:100%; margin-bottom:20px;'>";
        echo "<tr style='background:#f2f2f2;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>❌ Table $table does not exist</div>";
    }
}

echo "<div class='success'>";
echo "<h3>🎉 Setup Complete!</h3>";
echo "<p>Your database is now ready for order management. You can now:</p>";
echo "<ul>";
echo "<li>✅ Place orders through the checkout system</li>";
echo "<li>✅ View orders in the admin panel</li>";
echo "<li>✅ Track order history for users</li>";
echo "</ul>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>Go to your website and add items to cart</li>";
echo "<li>Proceed to checkout</li>";
echo "<li>Fill in shipping details and place order</li>";
echo "<li>Check admin panel to see the order</li>";
echo "</ol>";
echo "</div>";

$conn->close();
?>
