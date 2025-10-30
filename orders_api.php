<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'POST':
        if (isset($input['action'])) {
            switch ($input['action']) {
                case 'create_order':
                    createOrder($conn, $input);
                    break;
                case 'get_orders':
                    getOrders($conn, $input);
                    break;
                default:
                    echo json_encode(['success' => false, 'error' => 'Invalid action']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'No action specified']);
        }
        break;
    
    case 'GET':
        if (!empty($_GET['order_id'])) {
            getOrderById($conn, intval($_GET['order_id']));
        } elseif (isset($_GET['user_id'])) {
            getUserOrders($conn, $_GET['user_id']);
        } else {
            getAllOrders($conn);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        break;
}

function createOrder($conn, $data) {
    try {
        $user_id = intval($data['user_id'] ?? 0);
        $shipping_address_input = trim($data['shipping_address'] ?? '');
        $shipping_details = isset($data['shipping_details']) && is_array($data['shipping_details']) ? $data['shipping_details'] : null;
        $payment_method = trim($data['payment_method'] ?? 'cod');
        $total_amount = floatval($data['total_amount'] ?? 0);
        $items = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];

        if ($user_id <= 0) {
            throw new Exception('Invalid user');
        }

        if (empty($items)) {
            throw new Exception('No order items provided');
        }

        $shipping_address_to_store = $shipping_address_input;
        if ($shipping_details) {
            $shipping_payload = [
                'formatted' => $shipping_address_input,
                'details' => $shipping_details
            ];
            $encoded_shipping = json_encode($shipping_payload, JSON_UNESCAPED_UNICODE);
            if ($encoded_shipping !== false) {
                $shipping_address_to_store = $encoded_shipping;
            }
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, payment_method, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
        $stmt->bind_param('idss', $user_id, $total_amount, $shipping_address_to_store, $payment_method);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create order');
        }
        
        $order_id = $conn->insert_id;
        
        // Add order items
        $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        
        foreach ($items as $item) {
            $product_id = intval($item['product_id'] ?? 0);
            $quantity = intval($item['quantity'] ?? 0);
            $price = floatval($item['price'] ?? 0);

            if ($product_id <= 0 || $quantity <= 0) {
                throw new Exception('Invalid order item data');
            }

            $item_stmt->bind_param('iiid', $order_id, $product_id, $quantity, $price);
            if (!$item_stmt->execute()) {
                throw new Exception('Failed to add order item');
            }
        }
        
        // Clear user's cart
        $clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clear_cart->bind_param('i', $user_id);
        $clear_cart->execute();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'order_id' => $order_id,
            'message' => 'Order placed successfully!',
            'shipping_address_stored' => $shipping_address_to_store
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

function getOrderById($conn, $order_id) {
    try {
        $stmt = $conn->prepare("SELECT o.*, u.name as user_name, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
        $stmt->bind_param('i', $order_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to fetch order');
        }

        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'error' => 'Order not found'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $order = $result->fetch_assoc();
        $stmt->close();

        $items_stmt = $conn->prepare("SELECT oi.*, p.name, p.image_url FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $items_stmt->bind_param('i', $order_id);
        if (!$items_stmt->execute()) {
            throw new Exception('Failed to fetch order items');
        }

        $items_result = $items_stmt->get_result();
        $items = [];
        while ($row = $items_result->fetch_assoc()) {
            $items[] = $row;
        }
        $items_stmt->close();

        $shipping_formatted = $order['shipping_address'] ?? '';
        $shipping_parsed = null;

        if (!empty($order['shipping_address'])) {
            $decoded = json_decode($order['shipping_address'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                if (isset($decoded['details'])) {
                    $shipping_parsed = $decoded['details'];
                    $shipping_formatted = $decoded['formatted'] ?? $shipping_formatted;
                } else {
                    $shipping_parsed = $decoded;
                }
            }
        }

        $order['items'] = $items;
        $order['shipping_address_formatted'] = $shipping_formatted;
        $order['shipping_address_parsed'] = $shipping_parsed;

        echo json_encode(['success' => true, 'order' => $order], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

function getUserOrders($conn, $user_id) {
    try {
        $stmt = $conn->prepare("
            SELECT o.*, 
                   GROUP_CONCAT(
                       CONCAT(oi.quantity, 'x ', p.name, ' (₹', oi.price, ')') 
                       SEPARATOR ', '
                   ) as items
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE o.user_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ");
        
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        
        echo json_encode(['success' => true, 'orders' => $orders]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getAllOrders($conn) {
    try {
        $stmt = $conn->prepare("
            SELECT o.*, u.name as user_name, u.email,
                   GROUP_CONCAT(
                       CONCAT(oi.quantity, 'x ', p.name, ' (₹', oi.price, ')') 
                       SEPARATOR ', '
                   ) as items
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN products p ON oi.product_id = p.id
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ");
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        
        echo json_encode(['success' => true, 'orders' => $orders]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

$conn->close();
?>
