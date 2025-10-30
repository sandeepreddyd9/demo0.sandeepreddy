// products.js - Complete file
let allProducts = [];
let currentUser = null;

document.addEventListener('DOMContentLoaded', async function() {
    const productsGrid = document.getElementById('products-grid');
    if (productsGrid) {
        productsGrid.innerHTML = `
            <div class="empty-cart" style="grid-column: 1/-1;">
                <h2>Loading products...</h2>
                <p>Please wait a moment</p>
            </div>
        `;
    }
    await loadProducts();
    setupFilters();
    updateCartCount();
    loadCategoriesFilter();
    updateAuthUI();
});

async function loadProducts() {
    try {
        const params = new URLSearchParams(window.location.search);
        const categoryParam = params.get('category');

        if (categoryParam) {
            allProducts = await getProductsByCategory(categoryParam);
            const categoryFilter = document.getElementById('category-filter');
            if (categoryFilter) categoryFilter.value = categoryParam;
        } else {
            allProducts = await getProducts();
        }

        displayProducts(allProducts);
    } catch (error) {
        console.error('Error loading products:', error);
    }
}

function displayProducts(productsToShow) {
    const productsGrid = document.getElementById('products-grid');
    if (!productsGrid) return;

    if (productsToShow.length === 0) {
        productsGrid.innerHTML = `
            <div class="empty-cart" style="grid-column: 1/-1;">
                <h2>No products found</h2>
                <p>Try adjusting your search or filters</p>
            </div>
        `;
        return;
    }

    productsGrid.innerHTML = productsToShow.map(product => `
        <div class="product-card">
            <div class="product-image">
                ${createProductImage(product)}
            </div>
            <div class="product-info">
                <h3>${product.name}</h3>
                <div class="product-price">₹${product.price}</div>
                <div class="product-rating">⭐ ${product.rating || 4.5}</div>
                <p class="farmer">By ${product.farmer_info || 'Trusted Farmer'}</p>
                <p>${product.description}</p>
                <button class="btn-primary" onclick="addToCart(${product.id})" aria-label="Add ${product.name} to cart">
                    Add to Cart
                </button>
            </div>
        </div>
    `).join('');
}

function createProductImage(product) {
    const fallbackImage = `https://via.placeholder.com/400x400/4CAF50/ffffff?text=${encodeURIComponent(product.name)}`;
    
    if (product.image_url) {
        return `
            <img 
                src="${product.image_url}" 
                alt="${product.name}" 
                style="width:100%; height:100%; object-fit:cover; border-radius:8px;" 
                loading="lazy"
                onerror="this.onerror=null; this.src='${fallbackImage}'; console.log('Image failed to load for ${product.name}');"
                onload="console.log('Image loaded successfully for ${product.name}');"
            >
        `;
    } else {
        return `
            <img 
                src="${fallbackImage}" 
                alt="${product.name}" 
                style="width:100%; height:100%; object-fit:cover; border-radius:8px;" 
                loading="lazy"
            >
        `;
    }
}

function setupFilters() {
    const searchInput = document.getElementById('search-input');
    const categoryFilter = document.getElementById('category-filter');
    const sortBy = document.getElementById('sort-by');

    if (searchInput) {
        searchInput.addEventListener('input', filterProducts);
    }
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterProducts);
    }
    if (sortBy) {
        sortBy.addEventListener('change', filterProducts);
    }
}

function filterProducts() {
    const searchTerm = document.getElementById('search-input').value.toLowerCase();
    const category = document.getElementById('category-filter').value;
    const sortBy = document.getElementById('sort-by').value;

    let filtered = allProducts.filter(product => {
        const matchesSearch = product.name.toLowerCase().includes(searchTerm) || 
                            product.description.toLowerCase().includes(searchTerm);
        const matchesCategory = !category || product.category_id == category;
        return matchesSearch && matchesCategory;
    });

    // Sort products
    switch(sortBy) {
        case 'price-low':
            filtered.sort((a, b) => a.price - b.price);
            break;
        case 'price-high':
            filtered.sort((a, b) => b.price - a.price);
            break;
        case 'rating':
            filtered.sort((a, b) => b.rating - a.rating);
            break;
        default:
            filtered.sort((a, b) => a.name.localeCompare(b.name));
    }

    displayProducts(filtered);
}

async function loadCategoriesFilter() {
    const categoryFilter = document.getElementById('category-filter');
    if (!categoryFilter) return;

    const categories = await getCategories();
    
    // Add categories to filter dropdown
    categories.forEach(category => {
        const option = document.createElement('option');
        option.value = category.id;
        option.textContent = category.name;
        categoryFilter.appendChild(option);
    });
}

// Add to cart function
async function addToCart(productId) {
    const savedUser = localStorage.getItem('currentUser');
    currentUser = savedUser ? JSON.parse(savedUser) : null;

    if (!currentUser) {
        alert('Please login to add items to cart');
        return;
    }

    try {
        const result = await addToCartDB(currentUser.id, productId, 1);
        
        if (result.success) {
            updateCartCount();
            const product = allProducts.find(p => Number(p.id) === Number(productId));
            alert(`${product ? product.name : 'Item'} added to cart!`);
        } else {
            alert('Failed to add item to cart: ' + result.error);
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        alert('Error adding item to cart');
    }
}

async function updateCartCount() {
    const cartCount = document.getElementById('cart-count');
    const savedUser = localStorage.getItem('currentUser');
    currentUser = savedUser ? JSON.parse(savedUser) : null;

    if (cartCount) {
        if (currentUser) {
            try {
                const cartItems = await getCartItems(currentUser.id);
                const totalItems = cartItems.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
                cartCount.textContent = totalItems || '0';
            } catch (error) {
                console.error('Error updating cart count:', error);
                cartCount.textContent = '0';
            }
        } else {
            cartCount.textContent = '0';
        }
    }
}

function updateAuthUI() {
    // Use the shared navigation function instead of duplicating logic
    if (typeof updateNavigationFromAuth === 'function') {
        const savedUser = localStorage.getItem('currentUser');
        const user = savedUser ? JSON.parse(savedUser) : null;
        updateNavigationFromAuth(user);
    } else {
        // Fallback if shared function is not available
        const loginBtn = document.getElementById('login-btn');
        const registerBtn = document.getElementById('register-btn');
        const userProfile = document.getElementById('user-profile');
        const userName = document.getElementById('user-name');

        const savedUser = localStorage.getItem('currentUser');
        currentUser = savedUser ? JSON.parse(savedUser) : null;

        if (currentUser) {
            if (loginBtn) loginBtn.style.display = 'none';
            if (registerBtn) registerBtn.style.display = 'none';
            if (userProfile) userProfile.style.display = 'flex';
            if (userName) userName.textContent = currentUser.name;
        } else {
            if (loginBtn) loginBtn.style.display = 'block';
            if (registerBtn) registerBtn.style.display = 'block';
            if (userProfile) userProfile.style.display = 'none';
        }
    }
}