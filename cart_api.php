<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'add_to_cart':
        $userId = $input['user_id'] ?? 0;
        $productId = $input['product_id'] ?? 0;
        $quantity = $input['quantity'] ?? 1;
        
        if (!$userId || !$productId) {
            echo json_encode(['success' => false, 'error' => 'User ID and Product ID required']);
            exit;
        }
        
        // Check if item already in cart
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param('ii', $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing item
            $row = $result->fetch_assoc();
            $newQuantity = $row['quantity'] + $quantity;
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->bind_param('ii', $newQuantity, $row['id']);
        } else {
            // Add new item
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param('iii', $userId, $productId, $quantity);
        }
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to add to cart']);
        }
        break;
        
    case 'get_cart':
        $userId = $input['user_id'] ?? 0;
        
        if (!$userId) {
            echo json_encode(['success' => false, 'error' => 'User ID required']);
            exit;
        }
        
        $stmt = $conn->prepare("
            SELECT c.*, p.name, p.price, p.image_url 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $cartItems = [];
        while ($row = $result->fetch_assoc()) {
            $cartItems[] = $row;
        }
        
        echo json_encode(['success' => true, 'cartItems' => $cartItems]);
        break;
        
    case 'remove_from_cart':
        $userId = $input['user_id'] ?? 0;
        $productId = $input['product_id'] ?? 0;
        
        if (!$userId || !$productId) {
            echo json_encode(['success' => false, 'error' => 'User ID and Product ID required']);
            exit;
        }
        
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param('ii', $userId, $productId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to remove from cart']);
        }
        break;
        
    case 'update_cart_quantity':
        $userId = $input['user_id'] ?? 0;
        $productId = $input['product_id'] ?? 0;
        $quantity = $input['quantity'] ?? 0;
        
        if (!$userId || !$productId) {
            echo json_encode(['success' => false, 'error' => 'User ID and Product ID required']);
            exit;
        }
        
        if ($quantity <= 0) {
            // Remove item if quantity is 0 or less
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param('ii', $userId, $productId);
        } else {
            // Update quantity
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param('iii', $quantity, $userId, $productId);
        }
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update cart']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$conn->close();
?>
