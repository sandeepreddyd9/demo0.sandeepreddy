// Modern Navigation Bar JavaScript
class ModernNavbar {
    constructor() {
        this.currentUser = null;
        this.isMenuOpen = false;
        this.init();
    }

    init() {
        this.loadCurrentUser();
        this.setupEventListeners();
        this.updateAuthUI();
        this.updateCartCount();
        this.handleScroll();
    }

    loadCurrentUser() {
        try {
            const savedUser = localStorage.getItem('currentUser');
            if (savedUser) {
                this.currentUser = JSON.parse(savedUser);
                console.log('Modern Navbar: Loaded user', this.currentUser);
            } else {
                this.currentUser = null;
            }
        } catch (error) {
            console.error('Error loading user in modern navbar:', error);
            this.currentUser = null;
        }
    }

    setupEventListeners() {
        // Mobile menu toggle
        const mobileToggle = document.querySelector('.mobile-toggle');
        if (mobileToggle) {
            mobileToggle.addEventListener('click', () => this.toggleMobileMenu());
        }

        // Auth buttons
        const loginBtn = document.querySelector('.btn-login');
        const registerBtn = document.querySelector('.btn-register');
        const logoutBtn = document.querySelector('.btn-logout');

        // Setup auth buttons using the global function
        if (typeof window.setupAuthButtons === 'function') {
            window.setupAuthButtons();
        }

        if (logoutBtn) {
            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.logout();
            });
        }

        // Search functionality
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.performSearch(e.target.value);
                }
            });
        }

        // Notification bell
        const notificationBell = document.querySelector('.notification-bell');
        if (notificationBell) {
            notificationBell.addEventListener('click', () => this.showNotifications());
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.modern-navbar') && this.isMenuOpen) {
                this.closeMobileMenu();
            }
        });

        // Listen for storage changes (login/logout from other tabs)
        window.addEventListener('storage', (e) => {
            if (e.key === 'currentUser') {
                this.loadCurrentUser();
                this.updateAuthUI();
                this.updateCartCount();
            }
        });
    }

    handleScroll() {
        const navbar = document.querySelector('.modern-navbar');
        if (!navbar) return;

        let lastScrollTop = 0;
        window.addEventListener('scroll', () => {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }

            // Hide navbar when scrolling down, show when scrolling up
            if (scrollTop > lastScrollTop && scrollTop > 200) {
                navbar.style.transform = 'translateY(-100%)';
            } else {
                navbar.style.transform = 'translateY(0)';
            }
            
            lastScrollTop = scrollTop;
        });
    }

    toggleMobileMenu() {
        const navMenu = document.querySelector('.navbar-nav');
        const mobileToggle = document.querySelector('.mobile-toggle');
        
        this.isMenuOpen = !this.isMenuOpen;
        
        if (navMenu) {
            navMenu.classList.toggle('active', this.isMenuOpen);
        }
        
        if (mobileToggle) {
            mobileToggle.classList.toggle('active', this.isMenuOpen);
        }
    }

    closeMobileMenu() {
        const navMenu = document.querySelector('.navbar-nav');
        const mobileToggle = document.querySelector('.mobile-toggle');
        
        this.isMenuOpen = false;
        
        if (navMenu) {
            navMenu.classList.remove('active');
        }
        
        if (mobileToggle) {
            mobileToggle.classList.remove('active');
        }
    }

    showAuthForm(type) {
        if (typeof showAuthForm === 'function') {
            showAuthForm(type);
        } else {
            // Fallback - redirect to home page
            window.location.href = 'index.html';
        }
    }

    logout() {
        this.currentUser = null;
        localStorage.removeItem('currentUser');
        this.updateAuthUI();
        this.updateCartCount();
        
        // Show logout confirmation
        this.showNotification('Logged out successfully!', 'success');
        
        // Redirect to home if on protected pages
        if (window.location.pathname.includes('cart.html')) {
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1000);
        }
    }

    updateAuthUI() {
        const authSection = document.querySelector('.navbar-auth');
        if (!authSection) return;

        if (this.currentUser && this.currentUser.name) {
            // User is logged in - show profile
            authSection.innerHTML = `
                <div class="user-profile">
                    <div class="user-avatar">${this.currentUser.name.charAt(0).toUpperCase()}</div>
                    <div class="user-info">
                        <div class="user-name">${this.currentUser.name}</div>
                        <div class="user-role">Customer</div>
                    </div>
                    <button class="auth-btn btn-logout">
                        <span>🚪</span> Logout
                    </button>
                </div>
            `;
            
            // Re-attach logout event listener
            const logoutBtn = authSection.querySelector('.btn-logout');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.logout();
                });
            }
        } else {
            // User is not logged in - show login/register
            authSection.innerHTML = `
                <button class="auth-btn btn-login">
                    <span>👤</span> Login
                </button>
                <button class="auth-btn btn-register">
                    <span>✨</span> Register
                </button>
            `;
            
            // Re-attach auth event listeners using the global setupAuthButtons function
            if (typeof window.setupAuthButtons === 'function') {
                window.setupAuthButtons();
            }
        }
    }

    async updateCartCount() {
        const cartBadge = document.querySelector('.cart-badge');
        if (!cartBadge) return;

        if (this.currentUser && this.currentUser.id) {
            try {
                if (typeof getCartItems === 'function') {
                    const cartItems = await getCartItems(this.currentUser.id);
                    const totalItems = cartItems.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
                    cartBadge.textContent = totalItems;
                    cartBadge.style.display = totalItems > 0 ? 'block' : 'none';
                } else {
                    cartBadge.textContent = '0';
                    cartBadge.style.display = 'none';
                }
            } catch (error) {
                console.error('Error updating cart count:', error);
                cartBadge.textContent = '0';
                cartBadge.style.display = 'none';
            }
        } else {
            cartBadge.textContent = '0';
            cartBadge.style.display = 'none';
        }
    }

    handleSearch(query) {
        // Debounce search
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            if (query.length > 2) {
                this.showSearchSuggestions(query);
            } else {
                this.hideSearchSuggestions();
            }
        }, 300);
    }

    showSearchSuggestions(query) {
        // This would typically make an API call to get suggestions
        console.log('Searching for:', query);
        // Implementation would depend on your search API
    }

    hideSearchSuggestions() {
        // Hide search suggestions dropdown
    }

    performSearch(query) {
        if (query.trim()) {
            // Redirect to products page with search query
            window.location.href = `products.php?search=${encodeURIComponent(query)}`;
        }
    }

    showNotifications() {
        // Show notifications dropdown or modal
        this.showNotification('No new notifications', 'info');
    }

    showNotification(message, type = 'info') {
        // Create and show notification toast
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-icon">${this.getNotificationIcon(type)}</span>
                <span class="notification-message">${message}</span>
            </div>
        `;
        
        // Add notification styles
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            z-index: 10000;
            transform: translateX(400px);
            transition: all 0.3s ease;
            border-left: 4px solid ${this.getNotificationColor(type)};
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(400px)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    getNotificationIcon(type) {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        return icons[type] || icons.info;
    }

    getNotificationColor(type) {
        const colors = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8'
        };
        return colors[type] || colors.info;
    }

    // Public method to update from external auth
    updateFromAuth(user) {
        this.currentUser = user;
        this.updateAuthUI();
        this.updateCartCount();
    }
}

// Initialize modern navbar when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.modernNavbar = new ModernNavbar();
});

// Export for external use
window.updateNavigationFromAuth = function(user) {
    if (window.modernNavbar) {
        window.modernNavbar.updateFromAuth(user);
    }
};
