<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        $search = $_GET['search'] ?? null;
        $categoryFilter = $_GET['category'] ?? null;

        $sql = "SELECT * FROM products WHERE 1=1";
        $params = [];
        $types = '';

        if ($categoryFilter) {
            $sql .= " AND category = ?";
            $params[] = $categoryFilter;
            $types .= 's';
        }

        if ($search) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'ss';
        }

        $sql .= " ORDER BY created_at DESC";

        if (!empty($params)) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }

        $products = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                if (!isset($row['rating']) || $row['rating'] === null) {
                    $row['rating'] = 4.5;
                }
                if (!isset($row['farmer_info']) || $row['farmer_info'] === null) {
                    $row['farmer_info'] = 'Trusted Farmer';
                }
                if (!isset($row['image_url']) || !$row['image_url']) {
                    $row['image_url'] = 'https://via.placeholder.com/400x400/4CAF50/ffffff?text=' . urlencode($row['name']);
                }
                if (!isset($row['category']) || $row['category'] === null) {
                    $row['category'] = isset($row['category_id']) ? $row['category_id'] : 'general';
                }
                $products[] = $row;
            }
        }

        echo json_encode(['success' => true, 'products' => $products]);
        break;

    case 'add':
        $input = json_decode(file_get_contents('php://input'), true);

        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $price = floatval($input['price'] ?? 0);
        $categoryId = $input['category_id'] ?? null;
        $stockQuantity = intval($input['stock_quantity'] ?? 0);
        $imageUrl = trim($input['image_url'] ?? '');

        if ($name === '' || $description === '' || $price <= 0 || !$categoryId) {
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO products (name, description, price, category_id, stock_quantity, image_url, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param('ssdiii', $name, $description, $price, $categoryId, $stockQuantity, $imageUrl);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'product_id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to add product']);
        }
        break;

    case 'add_admin':
        $input = json_decode(file_get_contents('php://input'), true);

        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $price = floatval($input['price'] ?? 0);
        $category = trim($input['category'] ?? 'general');
        $stockQuantity = intval($input['stock_quantity'] ?? 0);
        $rating = floatval($input['rating'] ?? 4.5);
        $farmerInfo = trim($input['farmer_info'] ?? 'Local Farm');
        $imageUrl = trim($input['image_url'] ?? '');

        if ($name === '' || $description === '' || $price <= 0) {
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            exit;
        }

        if ($imageUrl === '') {
            $imageUrl = 'https://via.placeholder.com/400x400/4CAF50/ffffff?text=' . urlencode($name);
        }

        $stmt = $conn->prepare("INSERT INTO products (name, description, price, category, stock_quantity, rating, farmer_info, image_url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param('ssdsiiss', $name, $description, $price, $category, $stockQuantity, $rating, $farmerInfo, $imageUrl);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'product_id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to add product']);
        }
        break;

    case 'update_admin':
        $input = json_decode(file_get_contents('php://input'), true);

        $id = intval($input['id'] ?? 0);
        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $price = floatval($input['price'] ?? 0);
        $category = trim($input['category'] ?? 'general');
        $stockQuantity = intval($input['stock_quantity'] ?? 0);
        $rating = floatval($input['rating'] ?? 4.5);
        $farmerInfo = trim($input['farmer_info'] ?? 'Local Farm');
        $imageUrl = trim($input['image_url'] ?? '');

        if ($id <= 0 || $name === '' || $description === '' || $price <= 0) {
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            exit;
        }

        if ($imageUrl === '') {
            $imageUrl = 'https://via.placeholder.com/400x400/4CAF50/ffffff?text=' . urlencode($name);
        }

        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, category = ?, stock_quantity = ?, rating = ?, farmer_info = ?, image_url = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('ssdsiissi', $name, $description, $price, $category, $stockQuantity, $rating, $farmerInfo, $imageUrl, $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update product']);
        }
        break;

    case 'delete_admin':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid product id']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete product']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$conn->close();
?>
