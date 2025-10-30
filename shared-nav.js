// shared-nav.js - Consistent navigation across all pages
let currentUser = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeNavigation();
});

function initializeNavigation() {
    loadCurrentUser();
    setupNavigationListeners();
    updateNavigationUI();
    updateCartCount();
}

function loadCurrentUser() {
    try {
        const savedUser = localStorage.getItem('currentUser');
        if (savedUser) {
            currentUser = JSON.parse(savedUser);
            console.log('Navigation: Loaded user', currentUser);
        } else {
            currentUser = null;
        }
    } catch (error) {
        console.error('Error loading user in navigation:', error);
        currentUser = null;
    }
}

function setupNavigationListeners() {
    // Remove any existing listeners to prevent duplicates
    const loginBtn = document.getElementById('login-btn');
    const registerBtn = document.getElementById('register-btn');
    
    // Clone and replace elements to remove all event listeners
    if (loginBtn) {
        const newLoginBtn = loginBtn.cloneNode(true);
        loginBtn.parentNode.replaceChild(newLoginBtn, loginBtn);
        newLoginBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (typeof showAuthForm === 'function') {
                showAuthForm('login');
            } else {
                alert('Please use the login form on the homepage');
            }
        });
    }

    if (registerBtn) {
        const newRegisterBtn = registerBtn.cloneNode(true);
        registerBtn.parentNode.replaceChild(newRegisterBtn, registerBtn);
        newRegisterBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (typeof showAuthForm === 'function') {
                showAuthForm('register');
            } else {
                alert('Please use the registration form on the homepage');
            }
        });
    }

    // Logout button - use event delegation for dynamically created elements
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'logout-btn') {
            e.preventDefault();
            logout();
        }
    });
}

function updateNavigationUI() {
    const loginBtn = document.getElementById('login-btn');
    const registerBtn = document.getElementById('register-btn');
    const userProfile = document.getElementById('user-profile');
    const userName = document.getElementById('user-name');

    console.log('Updating navigation UI, current user:', currentUser);

    if (currentUser && currentUser.name) {
        // User is logged in - hide login/register, show profile
        if (loginBtn) loginBtn.style.display = 'none';
        if (registerBtn) registerBtn.style.display = 'none';
        if (userProfile) {
            userProfile.style.display = 'flex';
            userProfile.style.alignItems = 'center';
            userProfile.style.gap = '1rem';
        }
        if (userName) {
            userName.textContent = currentUser.name;
            console.log('Setting username to:', currentUser.name);
        }
    } else {
        // User is not logged in - show login/register, hide profile
        if (loginBtn) loginBtn.style.display = 'inline-block';
        if (registerBtn) registerBtn.style.display = 'inline-block';
        if (userProfile) userProfile.style.display = 'none';
    }
}

async function updateCartCount() {
    const cartCount = document.getElementById('cart-count');
    if (!cartCount) return;

    if (currentUser && currentUser.id) {
        try {
            const cartItems = await getCartItems(currentUser.id);
            const totalItems = cartItems.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
            cartCount.textContent = totalItems;
        } catch (error) {
            console.error('Error updating cart count:', error);
            cartCount.textContent = '0';
        }
    } else {
        cartCount.textContent = '0';
    }
}

function logout() {
    currentUser = null;
    localStorage.removeItem('currentUser');
    updateNavigationUI();
    updateCartCount();
    
    // Show logout confirmation
    alert('Logged out successfully!');
    
    // Redirect to home if on cart page
    if (window.location.pathname.includes('cart.html')) {
        window.location.href = 'index.html';
    }
}

// Export functions for other scripts to use
window.updateNavigationFromAuth = function(user) {
    currentUser = user;
    updateNavigationUI();
    updateCartCount();
};

// Listen for storage changes (login/logout from other tabs)
window.addEventListener('storage', function(e) {
    if (e.key === 'currentUser') {
        loadCurrentUser();
        updateNavigationUI();
        updateCartCount();
    }
});