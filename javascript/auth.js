// Page switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const loginSection = document.getElementById('login');
    const signUpSection = document.getElementById('signUp');
    const goToSignUpLink = document.getElementById('goToSignUp');
    const goToLoginLink = document.getElementById('goToLogin');

    function showLoginPage() {
        if (loginSection) loginSection.style.display = 'block';
        if (signUpSection) signUpSection.style.display = 'none';
        clearErrors();
        sessionStorage.setItem('currentAuthPage', 'login');
    }
    
    function showSignUpPage() {
        if (loginSection) loginSection.style.display = 'none';
        if (signUpSection) signUpSection.style.display = 'block';
        clearErrors();
        sessionStorage.setItem('currentAuthPage', 'signup');
    }
    
    if (goToSignUpLink) {
        goToSignUpLink.addEventListener('click', function(e) {
            e.preventDefault();
            showSignUpPage();
        });
    }
    
    if (goToLoginLink) {
        goToLoginLink.addEventListener('click', function(e) {
            e.preventDefault();
            showLoginPage();
        });
    }
    
    // check session storage for last viewed page
    function initializePage() {
        const lastPage = sessionStorage.getItem('currentAuthPage');
        
        if (lastPage === 'login') {
            showLoginPage();
        } else if (lastPage === 'signup') {
            showSignUpPage();
        } else {
            // Default
            showSignUpPage();
        }
    }
    initializePage();
});

// Password visibility
document.addEventListener('DOMContentLoaded', function() {
    // All toggle buttons 
    const toggleButtons = [
        { buttonId: 'toggleButton', passwordId: 'loginPassword' },
        { buttonId: 'toggleButton1', passwordId: 'registerPassword' },
        { buttonId: 'toggleButton2', passwordId: 'registerConfirmPassword' }
    ];
    
    toggleButtons.forEach(toggle => {
        const toggleButton = document.getElementById(toggle.buttonId);
        const passwordInput = document.getElementById(toggle.passwordId);
        
        if (toggleButton && passwordInput) {
            toggleButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Toggle password visibility
                const isPasswordVisible = passwordInput.type === 'password';
                passwordInput.type = isPasswordVisible ? 'text' : 'password';
                
                // Update the eye icon
                const eyeIcon = toggleButton.querySelector('img');
                if (eyeIcon) {
                    eyeIcon.src = isPasswordVisible ? '../../assets/images/visibility-off-btn.svg' : '../../assets/images/visibility-on-btn.svg';
                    eyeIcon.alt = isPasswordVisible ? 'Hide Password' : 'Show Password';
                }
            });
        }
    });
});

// Validation error handling
// Clear all previous error messages
function clearErrors() {
    const loginError = document.getElementById('loginError');
    const registerError = document.getElementById('registerError');
    
    if (loginError) {
        loginError.classList.remove('show');
        const loginErrorMessage = document.getElementById('loginErrorMessage');
        if (loginErrorMessage) loginErrorMessage.textContent = '';
    }
    
    if (registerError) {
        registerError.classList.remove('show');
        const registerErrorMessage = document.getElementById('registerErrorMessage');
        if (registerErrorMessage) registerErrorMessage.textContent = '';
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    
    // Login 
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            console.log("Login form submitted");
            e.preventDefault();
            clearErrors(); 
            
            const username = document.getElementById('loginUsername').value.trim();
            const password = document.getElementById('loginPassword').value.trim();

            const loginError = document.getElementById('loginError');
            const loginErrorMessage = document.getElementById('loginErrorMessage');

            let isValid = true;
            let errorMessage = '';

            if (!isValid) {
                if (loginError && loginErrorMessage) {
                    loginErrorMessage.textContent = errorMessage;
                    loginError.classList.add('show');
                }
                return;
            }
            
            alert('Login successful!');

            // GO TO MEMBER INDEX AFTER SUCCESSFUL REGISTER 
        });
    }
    
    // Register
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearErrors(); 
            
            const fullname = document.getElementById('registerFullname').value.trim();
            const username = document.getElementById('registerUsername').value.trim();
            const email = document.getElementById('registerEmail').value.trim();
            const location = document.getElementById('registerLocation').value;
            const password = document.getElementById('registerPassword').value.trim();
            const confirmPassword = document.getElementById('registerConfirmPassword').value.trim();

            const registerError = document.getElementById('registerError');
            const registerErrorMessage = document.getElementById('registerErrorMessage');
            
            let isValid = true;
            let errorMessage = '';
            
            // ADD MORE VALIDATION AS NEEDED

            // CHECK IF USERNAME OR EMAIL ALREADY EXISTS IN DATABASE !!!!!!!!!!!!!!!!!

            if (password.length < 8) {
                isValid = false;
                errorMessage = 'Password must be at least 8 characters long';
            } else if (password !== confirmPassword) {
                isValid = false;
                errorMessage = 'Passwords do not match';
            }
            
            if (!isValid) {
                if (registerError && registerErrorMessage) {
                    registerErrorMessage.textContent = errorMessage;
                    registerError.classList.add('show');
                }
                return;
            }
            
            // ADD USER TO DATABASE !!!!!!!!!!!!!!!!!

            alert('Registration successful!');

            // GO TO MEMBER INDEX AFTER SUCCESSFUL REGISTER 
        });
    }
});