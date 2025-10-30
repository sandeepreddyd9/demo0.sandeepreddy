// Auth UI helper - robust modal handling shared across pages
(function() {
    function byId(id){ return document.getElementById(id); }

    function openModal(){ const m = byId('auth-modal'); if (m) m.style.display = 'block'; }
    function closeModal(){ const m = byId('auth-modal'); if (m) m.style.display = 'none'; }

    window.showAuthForm = function(type){
        const forms = byId('auth-forms');
        if (!forms) return;
        if (type === 'login') {
            forms.innerHTML = `
                <h2>Login</h2>
                <form onsubmit="handleLogin(event)">
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" id="login-email" required>
                    </div>
                    <div class="form-group">
                        <label>Password:</label>
                        <input type="password" id="login-password" required>
                    </div>
                    <button type="submit" class="btn-primary">Login</button>
                </form>
                <p style="margin-top: 1rem;">Don't have an account? <a href="#" onclick="showAuthForm('register')" style="color:#27ae60;">Register here</a></p>
            `;
        } else {
            forms.innerHTML = `
                <h2>Register</h2>
                <form onsubmit="handleRegister(event)">
                    <div class="form-group">
                        <label>Full Name:</label>
                        <input type="text" id="register-name" required>
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" id="register-email" required>
                    </div>
                    <div class="form-group">
                        <label>Password:</label>
                        <input type="password" id="register-password" required>
                    </div>
                    <div class="form-group">
                        <label>Phone:</label>
                        <input type="tel" id="register-phone" required>
                    </div>
                    <button type="submit" class="btn-primary">Register</button>
                </form>
                <p style="margin-top: 1rem;">Already have an account? <a href="#" onclick="showAuthForm('login')" style="color:#27ae60;">Login here</a></p>
            `;
        }
        openModal();
    };

    window.handleLogin = async function(event){
        event.preventDefault();
        try {
            const email = byId('login-email').value;
            const password = byId('login-password').value;
            const result = await loginUser({ email, password });
            if (result && result.success && result.user) {
                localStorage.setItem('currentUser', JSON.stringify(result.user));
                if (typeof updateAuthUI === 'function') updateAuthUI();
                if (typeof updateCartCount === 'function') updateCartCount();
                if (typeof updateNavigationFromAuth === 'function') updateNavigationFromAuth(result.user);
                closeModal();
                alert('Login successful!');
            } else {
                alert('Login failed: ' + (result && result.error ? result.error : 'Unknown error'));
            }
        } catch (e) {
            alert('Login error: ' + e.message);
        }
    };

    window.handleRegister = async function(event){
        event.preventDefault();
        try {
            const name = byId('register-name').value;
            const email = byId('register-email').value;
            const password = byId('register-password').value;
            const phone = byId('register-phone').value;
            const result = await registerUser({ name, email, password, phone });
            if (result && result.success && result.user) {
                localStorage.setItem('currentUser', JSON.stringify(result.user));
                if (typeof updateAuthUI === 'function') updateAuthUI();
                if (typeof updateCartCount === 'function') updateCartCount();
                if (typeof updateNavigationFromAuth === 'function') updateNavigationFromAuth(result.user);
                closeModal();
                alert('Registration successful!');
            } else {
                alert('Registration failed: ' + (result && result.error ? result.error : 'Unknown error'));
            }
        } catch (e) {
            alert('Registration error: ' + e.message);
        }
    };

    document.addEventListener('DOMContentLoaded', function(){
        // Small delay to ensure all scripts are loaded
        setTimeout(() => {
            setupAuthButtons();
        }, 100);
        
        const closeBtn = document.querySelector('.modal .close');
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        window.addEventListener('click', (e) => { if (e.target && e.target.id === 'auth-modal') closeModal(); });
    });

    // Function to setup auth buttons (can be called when buttons are dynamically created)
    window.setupAuthButtons = function() {
        const loginBtns = document.querySelectorAll('.btn-login');
        const registerBtns = document.querySelectorAll('.btn-register');
        
        loginBtns.forEach(btn => {
            btn.removeEventListener('click', loginClickHandler); // Remove existing listener
            btn.addEventListener('click', loginClickHandler);
        });
        
        registerBtns.forEach(btn => {
            btn.removeEventListener('click', registerClickHandler); // Remove existing listener
            btn.addEventListener('click', registerClickHandler);
        });
    };

    function loginClickHandler() {
        window.showAuthForm('login');
    }

    function registerClickHandler() {
        window.showAuthForm('register');
    }
})();


