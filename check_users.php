<?php
require_once 'config.php';

echo "<h1>🔍 User Database Check</h1>";
echo "<style>body{font-family:Arial;margin:20px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;}</style>";

// Check if users table exists
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows == 0) {
    echo "<div style='background:#f8d7da;padding:15px;border-radius:5px;color:#721c24;'>";
    echo "<h3>❌ Users table does not exist!</h3>";
    echo "<p>Please create the users table first.</p>";
    echo "</div>";
    exit;
}

// Get all users
$result = $conn->query("SELECT id, name, email, password, phone, created_at FROM users ORDER BY id");

if ($result->num_rows > 0) {
    echo "<div style='background:#d4edda;padding:15px;border-radius:5px;color:#155724;margin-bottom:20px;'>";
    echo "<h3>✅ Found " . $result->num_rows . " users in database</h3>";
    echo "</div>";
    
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Password Type</th><th>Phone</th><th>Created</th><th>Action</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        $passwordType = (strlen($row['password']) > 50) ? "Hashed ✅" : "Plain Text ⚠️";
        $passwordColor = (strlen($row['password']) > 50) ? "#28a745" : "#dc3545";
        
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td style='color:$passwordColor;font-weight:bold;'>" . $passwordType . "</td>";
        echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>";
        if (strlen($row['password']) <= 50) {
            echo "<a href='?hash_user=" . $row['id'] . "' style='background:#28a745;color:white;padding:5px 10px;text-decoration:none;border-radius:3px;'>Hash Password</a>";
        } else {
            echo "<span style='color:#28a745;'>✅ Secure</span>";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Handle password hashing
    if (isset($_GET['hash_user'])) {
        $userId = (int)$_GET['hash_user'];
        
        // Get user's current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && strlen($user['password']) <= 50) {
            // Hash the plain text password
            $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
            
            // Update in database
            $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updateStmt->bind_param('si', $hashedPassword, $userId);
            
            if ($updateStmt->execute()) {
                echo "<div style='background:#d4edda;padding:15px;border-radius:5px;color:#155724;margin-top:20px;'>";
                echo "<h3>✅ Password hashed successfully for user ID: $userId</h3>";
                echo "<p>The user can now login with their original password.</p>";
                echo "<a href='check_users.php'>Refresh Page</a>";
                echo "</div>";
            } else {
                echo "<div style='background:#f8d7da;padding:15px;border-radius:5px;color:#721c24;margin-top:20px;'>";
                echo "<h3>❌ Failed to hash password</h3>";
                echo "</div>";
            }
        }
    }
    
} else {
    echo "<div style='background:#fff3cd;padding:15px;border-radius:5px;color:#856404;'>";
    echo "<h3>⚠️ No users found in database</h3>";
    echo "<p>The users table is empty.</p>";
    echo "</div>";
}

echo "<div style='margin-top:30px;padding:20px;background:#e3f2fd;border-radius:5px;'>";
echo "<h3>🔧 Test Login for sandeep@gmail.com</h3>";
echo "<form method='post'>";
echo "<input type='password' name='test_password' placeholder='Enter password for sandeep@gmail.com' style='padding:10px;margin:10px;width:200px;'>";
echo "<button type='submit' style='padding:10px 20px;background:#007bff;color:white;border:none;border-radius:5px;'>Test Login</button>";
echo "</form>";

if (isset($_POST['test_password'])) {
    $testPassword = $_POST['test_password'];
    
    // Get sandeep's user data
    $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = 'sandeep@gmail.com'");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        echo "<div style='margin-top:15px;padding:15px;background:#f8f9fa;border-radius:5px;'>";
        echo "<h4>Test Results:</h4>";
        echo "<p><strong>Email:</strong> " . $user['email'] . "</p>";
        echo "<p><strong>Stored Password:</strong> " . substr($user['password'], 0, 20) . "...</p>";
        echo "<p><strong>Password Length:</strong> " . strlen($user['password']) . " characters</p>";
        
        // Test both methods
        $hashMatch = password_verify($testPassword, $user['password']);
        $plainMatch = ($testPassword === $user['password']);
        
        echo "<p><strong>Hash Verify:</strong> " . ($hashMatch ? "✅ Match" : "❌ No Match") . "</p>";
        echo "<p><strong>Plain Text:</strong> " . ($plainMatch ? "✅ Match" : "❌ No Match") . "</p>";
        
        if ($hashMatch || $plainMatch) {
            echo "<div style='background:#d4edda;padding:10px;border-radius:3px;color:#155724;margin-top:10px;'>";
            echo "🎉 <strong>Login would succeed!</strong>";
            echo "</div>";
        } else {
            echo "<div style='background:#f8d7da;padding:10px;border-radius:3px;color:#721c24;margin-top:10px;'>";
            echo "❌ <strong>Login would fail - password doesn't match</strong>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<div style='background:#f8d7da;padding:15px;border-radius:5px;color:#721c24;margin-top:15px;'>";
        echo "❌ User sandeep@gmail.com not found in database";
        echo "</div>";
    }
}

echo "</div>";

$conn->close();
?>
