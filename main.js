// Simple authentication management - COMPLETE STANDALONE VERSION
let currentUser = null;

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded - initializing authentication');
    initializeAuth();
    setupEventListeners();
    updateAuthUI();
});

function initializeAuth() {
    // Load user from localStorage
    const savedUser = localStorage.getItem('currentUser');
    if (savedUser) {
        try {
            currentUser = JSON.parse(savedUser);
            console.log('User found:', currentUser.name);
        } catch (e) {
            console.error('Error parsing user data:', e);
            currentUser = null;
            localStorage.removeItem('currentUser');
        }
    } else {
        console.log('No user found in localStorage');
        currentUser = null;
    }
}

function setupEventListeners() {
    console.log('Setting up event listeners');
    
    // Login button
    const loginBtn = document.getElementById('login-btn');
    if (loginBtn) {
        loginBtn.addEventListener('click', function() {
            console.log('Login button clicked');
            showAuthForm('login');
        });
    }

    // Register button
    const registerBtn = document.getElementById('register-btn');
    if (registerBtn) {
        registerBtn.addEventListener('click', function() {
            console.log('Register button clicked');
            showAuthForm('register');
        });
    }

    // Modal close
    const closeBtn = document.querySelector('.close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            document.getElementById('auth-modal').style.display = 'none';
        });
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('auth-modal');
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
}

function updateAuthUI() {
    console.log('Updating auth UI, current user:', currentUser);
    
    const loginBtn = document.getElementById('login-btn');
    const registerBtn = document.getElementById('register-btn');
    const userProfile = document.getElementById('user-profile');
    const userName = document.getElementById('user-name');

    if (currentUser) {
        // User is logged in
        if (loginBtn) loginBtn.style.display = 'none';
        if (registerBtn) registerBtn.style.display = 'none';
        if (userProfile) userProfile.style.display = 'flex';
        if (userName) userName.textContent = currentUser.name || 'User';
    } else {
        // User is logged out
        if (loginBtn) loginBtn.style.display = 'inline-block';
        if (registerBtn) registerBtn.style.display = 'inline-block';
        if (userProfile) userProfile.style.display = 'none';
    }
}

function showAuthForm(type) {
    console.log('Showing auth form:', type);
    const modal = document.getElementById('auth-modal');
    const authForms = document.getElementById('auth-forms');
    
    if (type === 'login') {
        authForms.innerHTML = `
            <h2>Login to GreenCart</h2>
            <form id="login-form">
                <div class="form-group">
                    <label for="login-email">Email:</label>
                    <input type="email" id="login-email" required placeholder="Enter your email">
                </div>
                <div class="form-group">
                    <label for="login-password">Password:</label>
                    <input type="password" id="login-password" required placeholder="Enter your password">
                </div>
                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 1rem;">Login</button>
            </form>
            <p style="margin-top: 1rem; text-align: center;">
                Don't have an account? 
                <a href="#" id="switch-to-register" style="color: #27ae60;">Register here</a>
            </p>
        `;
        
        // Add event listeners after form is created
        setTimeout(() => {
            const loginForm = document.getElementById('login-form');
            const switchLink = document.getElementById('switch-to-register');
            
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    handleLogin();
                });
            }
            if (switchLink) {
                switchLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    showAuthForm('register');
                });
            }
        }, 100);
        
    } else {
        authForms.innerHTML = `
            <h2>Join GreenCart</h2>
            <form id="register-form">
                <div class="form-group">
                    <label for="register-name">Full Name:</label>
                    <input type="text" id="register-name" required placeholder="Enter your full name">
                </div>
                <div class="form-group">
                    <label for="register-email">Email:</label>
                    <input type="email" id="register-email" required placeholder="Enter your email">
                </div>
                <div class="form-group">
                    <label for="register-password">Password:</label>
                    <input type="password" id="register-password" required placeholder="Create a password">
                </div>
                <div class="form-group">
                    <label for="register-phone">Phone:</label>
                    <input type="tel" id="register-phone" required placeholder="Enter your phone number">
                </div>
                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 1rem;">Create Account</button>
            </form>
            <p style="margin-top: 1rem; text-align: center;">
                Already have an account? 
                <a href="#" id="switch-to-login" style="color: #27ae60;">Login here</a>
            </p>
        `;
        
        // Add event listeners after form is created
        setTimeout(() => {
            const registerForm = document.getElementById('register-form');
            const switchLink = document.getElementById('switch-to-login');
            
            if (registerForm) {
                registerForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    handleRegister();
                });
            }
            if (switchLink) {
                switchLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    showAuthForm('login');
                });
            }
        }, 100);
    }
    
    modal.style.display = 'block';
}

// SIMPLE LOGIN FUNCTION - NO EXTERNAL DEPENDENCIES
function handleLogin() {
    console.log('Login form submitted');
    
    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;
    
    console.log('Login attempt with:', email);

    // Simple validation
    if (!email || !password) {
        alert('Please fill in all fields');
        return;
    }

    // Create mock user data
    currentUser = {
        id: Date.now(),
        name: email.split('@')[0], // Use part of email as name
        email: email,
        phone: '123-456-7890'
    };
    
    // Save to localStorage
    localStorage.setItem('currentUser', JSON.stringify(currentUser));
    
    // Update UI
    updateAuthUI();
    
    // Close modal
    document.getElementById('auth-modal').style.display = 'none';
    
    // Show success message
    alert('✅ Login successful! Welcome to GreenCart 🎉');
    
    console.log('Login successful, user:', currentUser);
}

// SIMPLE REGISTER FUNCTION - NO EXTERNAL DEPENDENCIES
function handleRegister() {
    console.log('Register form submitted');
    
    const name = document.getElementById('register-name').value;
    const email = document.getElementById('register-email').value;
    const password = document.getElementById('register-password').value;
    const phone = document.getElementById('register-phone').value;
    
    console.log('Registration attempt:', { name, email, phone });

    // Simple validation
    if (!name || !email || !password || !phone) {
        alert('Please fill in all fields');
        return;
    }

    if (password.length < 6) {
        alert('Password should be at least 6 characters long');
        return;
    }

    // Create mock user data
    currentUser = {
        id: Date.now(),
        name: name,
        email: email,
        phone: phone
    };
    
    // Save to localStorage
    localStorage.setItem('currentUser', JSON.stringify(currentUser));
    
    // Update UI
    updateAuthUI();
    
    // Close modal
    document.getElementById('auth-modal').style.display = 'none';
    
    // Show success message
    alert('✅ Registration successful! Welcome to GreenCart 🎉');
    
    console.log('Registration successful, user:', currentUser);
}

function logout() {
    console.log('Logging out user');
    
    currentUser = null;
    localStorage.removeItem('currentUser');
    
    // Update UI
    updateAuthUI();
    
    // Show logout message
    alert('👋 You have been logged out successfully!');
    
    console.log('Logout successful');
}

// Setup logout listener
document.addEventListener('click', function(e) {
    if (e.target && e.target.id === 'logout-btn') {
        logout();
    }
});

// Make functions available globally
window.showAuthForm = showAuthForm;
window.handleLogin = handleLogin;
window.handleRegister = handleRegister;
window.logout = logout;