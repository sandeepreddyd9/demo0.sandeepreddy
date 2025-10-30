// Static Database - No Server Required
// All data stored in localStorage

// User Authentication - Static Version
async function registerUser(userData) {
    try {
        // Get existing users
        const users = JSON.parse(localStorage.getItem('users')) || [];
        
        // Check if email already exists
        if (users.find(user => user.email === userData.email)) {
            return { success: false, error: 'Email already registered' };
        }
        
        // Create new user
        const newUser = {
            id: Date.now(), // Simple ID generation
            name: userData.name,
            email: userData.email,
            password: userData.password, // In real app, this should be hashed
            phone: userData.phone || '',
            created_at: new Date().toISOString()
        };
        
        // Add to users array
        users.push(newUser);
        localStorage.setItem('users', JSON.stringify(users));
        
        return { success: true, user: { id: newUser.id, name: newUser.name, email: newUser.email } };
    } catch (error) {
        console.error('Registration failed:', error);
        return { success: false, error: 'Registration failed' };
    }
}

async function loginUser(credentials) {
    try {
        const users = JSON.parse(localStorage.getItem('users')) || [];
        const user = users.find(u => u.email === credentials.email && u.password === credentials.password);
        
        if (user) {
            // Store current user
            const userSession = { id: user.id, name: user.name, email: user.email };
            localStorage.setItem('currentUser', JSON.stringify(userSession));
            return { success: true, user: userSession };
        } else {
            return { success: false, error: 'Invalid email or password' };
        }
    } catch (error) {
        console.error('Login failed:', error);
        return { success: false, error: 'Login failed' };
    }
}

// Products - Static Version
async function getProducts() {
    try {
        // Load static products data
        if (typeof staticProducts !== 'undefined') {
            // Get custom products from localStorage
            const customProducts = JSON.parse(localStorage.getItem('customProducts')) || [];
            return [...staticProducts, ...customProducts];
        } else {
            // Fallback if staticProducts not loaded
            return JSON.parse(localStorage.getItem('customProducts')) || [];
        }
    } catch (error) {
        console.error('Error loading products:', error);
        return [];
    }
}

async function getProductsByCategory(categoryName) {
    try {
        const allProducts = await getProducts();
        return allProducts.filter(product => product.category === categoryName);
    } catch (error) {
        console.error('Error loading products by category:', error);
        return [];
    }
}

// Categories - Static Version
async function getCategories() {
    return [
        { id: 1, name: 'Vegetables' },
        { id: 2, name: 'Fruits' },
        { id: 3, name: 'Grains' },
        { id: 4, name: 'Dairy' },
        { id: 5, name: 'Herbs' },
        { id: 6, name: 'Beverages' },
        { id: 7, name: 'Oils & Spices' }
    ];
}

// Cart - Static Version
async function addToCartDB(userId, productId, quantity) {
    try {
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        
        // Find existing item
        const existingItemIndex = cart.findIndex(item => 
            item.user_id === userId && item.product_id === productId
        );
        
        if (existingItemIndex !== -1) {
            // Update quantity
            cart[existingItemIndex].quantity += quantity;
        } else {
            // Get product details
            const allProducts = await getProducts();
            const product = allProducts.find(p => p.id === productId);
            
            if (product) {
                // Add new item
                cart.push({
                    user_id: userId,
                    product_id: productId,
                    quantity: quantity,
                    name: product.name,
                    price: product.price,
                    image_url: product.image_url
                });
            }
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        return { success: true };
    } catch (error) {
        console.error('Error adding to cart:', error);
        return { success: false, error: 'Failed to add to cart' };
    }
}

async function getCartItems(userId) {
    try {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        return cart.filter(item => item.user_id === userId);
    } catch (error) {
        console.error('Error loading cart:', error);
        return [];
    }
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
