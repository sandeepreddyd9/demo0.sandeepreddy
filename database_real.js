// Real Database Connection - Uses your organic_store database
const API_BASE = window.location.origin + '/mini project/';

// Generic API call function
async function callAPI(endpoint, data = null) {
    try {
        const options = {
            method: data ? 'POST' : 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        };
        
        if (data) {
            options.body = JSON.stringify(data);
        }
        
        const response = await fetch(API_BASE + endpoint, options);
        const result = await response.json();
        
        return result;
    } catch (error) {
        console.error('API call failed:', error);
        return { success: false, error: 'Network error' };
    }
}

// User Authentication
async function registerUser(userData) {
    return await callAPI('auth_api.php', {
        action: 'register',
        ...userData
    });
}

async function loginUser(credentials) {
    return await callAPI('auth_api.php', {
        action: 'login',
        ...credentials
    });
}

// Products
async function getProducts() {
    const result = await callAPI('products_api.php?action=list');
    return result.success ? result.products : [];
}

async function getAllProducts() {
    try {
        const response = await fetch('products_api.php?action=list');
        const result = await response.json();
        return result.success ? result.products : [];
    } catch (error) {
        console.error('Error fetching all products:', error);
        return [];
    }
}

async function addProductToDB(productData) {
    try {
        const response = await fetch('products_api.php?action=add_admin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(productData)
        });

        const result = await response.json();
        return result;
    } catch (error) {
        console.error('Error adding product:', error);
        return { success: false, error: error.message };
    }
}

async function updateProductInDB(productId, productData) {
    try {
        const response = await fetch('products_api.php?action=update_admin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: productId, ...productData })
        });

        const result = await response.json();
        return result;
    } catch (error) {
        console.error('Error updating product:', error);
        return { success: false, error: error.message };
    }
}

async function deleteProductFromDB(productId) {
    try {
        const response = await fetch('products_api.php?action=delete_admin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: productId })
        });

        const result = await response.json();
        return result;
    } catch (error) {
        console.error('Error deleting product:', error);
        return { success: false, error: error.message };
    }
}

async function getCategories() {
    const result = await callAPI('categories_api.php');
    return result.success ? result.categories : [];
}

async function getProductsByCategory(categoryId) {
    const result = await callAPI(`products_api.php?action=list&category_id=${categoryId}`);
    return result.success ? result.products : [];
}

// Order Management Functions
async function createOrder(orderData) {
    try {
        const response = await fetch('orders_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'create_order',
                ...orderData
            })
        });
        
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('Error creating order:', error);
        return { success: false, error: error.message };
    }
}

async function getUserOrders(userId) {
    try {
        const response = await fetch(`orders_api.php?user_id=${userId}`);
        const result = await response.json();
        return result.success ? result.orders : [];
    } catch (error) {
        console.error('Error fetching user orders:', error);
        return [];
    }
}

async function getAllOrders() {
    try {
        const response = await fetch('orders_api.php');
        const result = await response.json();
        return result.success ? result.orders : [];
    } catch (error) {
        console.error('Error fetching all orders:', error);
        return [];
    }
}

// Cart Operations
async function addToCartDB(userId, productId, quantity = 1) {
    return await callAPI('cart_api.php', {
        action: 'add_to_cart',
        user_id: userId,
        product_id: productId,
        quantity: quantity
    });
}

async function getCartItems(userId) {
    const result = await callAPI('cart_api.php', {
        action: 'get_cart',
        user_id: userId
    });
    return result.success ? result.cartItems : [];
}

async function removeFromCartDB(userId, productId) {
    return await callAPI('cart_api.php', {
        action: 'remove_from_cart',
        user_id: userId,
        product_id: productId
    });
}

async function updateCartQuantityDB(userId, productId, quantity) {
    return await callAPI('cart_api.php', {
        action: 'update_cart_quantity',
        user_id: userId,
        product_id: productId,
        quantity: quantity
    });
}

// Orders
async function createOrder(orderData) {
    return await callAPI('orders_api.php', {
        action: 'create_order',
        ...orderData
    });
}

async function getOrders(userId = null) {
    const endpoint = userId ? `orders_api.php?user_id=${userId}` : 'orders_api.php';
    const result = await callAPI(endpoint);
    return result.success ? result.orders : [];
}

// Utility functions
function getCurrentUser() {
    try {
        const user = localStorage.getItem('currentUser');
        return user ? JSON.parse(user) : null;
    } catch (error) {
        console.error('Error getting current user:', error);
        return null;
    }
}

function logoutUser() {
    localStorage.removeItem('currentUser');
    return { success: true };
}
