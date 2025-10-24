<?php
session_start();
include("../../php/dbConn.php");

$countries = [
    'Afghanistan', 'Åland Islands', 'Albania', 'Algeria', 'American Samoa', 'Andorra',
    'Angola', 'Anguilla', 'Antarctica', 'Antigua and Barbuda', 'Argentina', 'Armenia',
    'Aruba', 'Australia', 'Austria', 'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh',
    'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bermuda', 'Bhutan', 'Bolivia',
    'Bosnia and Herzegovina', 'Botswana', 'Bouvet Island', 'Brazil', 'British Indian Ocean Territory',
    'Brunei Darussalam', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cambodia', 'Cameroon',
    'Canada', 'Cape Verde', 'Cayman Islands', 'Central African Republic', 'Chad', 'Chile',
    'China', 'Christmas Island', 'Cocos (Keeling) Islands', 'Colombia', 'Comoros', 'Congo',
    'Congo, The Democratic Republic of The', 'Cook Islands', 'Costa Rica', "Cote D'ivoire",
    'Croatia', 'Cuba', 'Cyprus', 'Czech Republic', 'Denmark', 'Djibouti', 'Dominica',
    'Dominican Republic', 'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea',
    'Estonia', 'Ethiopia', 'Falkland Islands (Malvinas)', 'Faroe Islands', 'Fiji', 'Finland',
    'France', 'French Guiana', 'French Polynesia', 'French Southern Territories', 'Gabon',
    'Gambia', 'Georgia', 'Germany', 'Ghana', 'Gibraltar', 'Greece', 'Greenland', 'Grenada',
    'Guadeloupe', 'Guam', 'Guatemala', 'Guernsey', 'Guinea', 'Guinea-bissau', 'Guyana',
    'Haiti', 'Heard Island and Mcdonald Islands', 'Holy See (Vatican City State)', 'Honduras',
    'Hong Kong', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran, Islamic Republic of',
    'Iraq', 'Ireland', 'Isle of Man', 'Israel', 'Italy', 'Jamaica', 'Japan', 'Jersey',
    'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', "Korea, Democratic People's Republic of",
    'Korea, Republic of', 'Kuwait', 'Kyrgyzstan', "Lao People's Democratic Republic",
    'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libyan Arab Jamahiriya', 'Liechtenstein',
    'Lithuania', 'Luxembourg', 'Macao', 'Macedonia, The Former Yugoslav Republic of',
    'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands',
    'Martinique', 'Mauritania', 'Mauritius', 'Mayotte', 'Mexico', 'Micronesia, Federated States of',
    'Moldova, Republic of', 'Monaco', 'Mongolia', 'Montenegro', 'Montserrat', 'Morocco',
    'Mozambique', 'Myanmar', 'Namibia', 'Nauru', 'Nepal', 'Netherlands', 'Netherlands Antilles',
    'New Caledonia', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'Niue', 'Norfolk Island',
    'Northern Mariana Islands', 'Norway', 'Oman', 'Pakistan', 'Palau', 'Palestinian Territory, Occupied',
    'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Pitcairn', 'Poland',
    'Portugal', 'Puerto Rico', 'Qatar', 'Reunion', 'Romania', 'Russian Federation', 'Rwanda',
    'Saint Helena', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Pierre and Miquelon',
    'Saint Vincent and The Grenadines', 'Samoa', 'San Marino', 'Sao Tome and Principe',
    'Saudi Arabia', 'Senegal', 'Serbia', 'Seychelles', 'Sierra Leone', 'Singapore',
    'Slovakia', 'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa',
    'South Georgia and The South Sandwich Islands', 'Spain', 'Sri Lanka', 'Sudan',
    'Suriname', 'Svalbard and Jan Mayen', 'Swaziland', 'Sweden', 'Switzerland',
    'Syrian Arab Republic', 'Taiwan', 'Tajikistan', 'Tanzania, United Republic of',
    'Thailand', 'Timor-leste', 'Togo', 'Tokelau', 'Tonga', 'Trinidad and Tobago',
    'Tunisia', 'Turkey', 'Turkmenistan', 'Turks and Caicos Islands', 'Tuvalu',
    'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States',
    'United States Minor Outlying Islands', 'Uruguay', 'Uzbekistan', 'Vanuatu',
    'Venezuela', 'Viet Nam', 'Virgin Islands, British', 'Virgin Islands, U.S.',
    'Wallis and Futuna', 'Western Sahara', 'Yemen', 'Zambia', 'Zimbabwe'
];

$loginError = '';
$registerError = '';
$currentPage = isset($_SESSION['currentAuthPage']) ? $_SESSION['currentAuthPage'] : 'signup';
$registrationSuccess = false;

if (isset($_SESSION['registration_success'])) {
    $registrationSuccess = true;
    $currentPage = 'login';
    unset($_SESSION['registration_success']);
    unset($_SESSION['currentAuthPage']);
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = mysqli_real_escape_string($connection, trim($_POST['username']));
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $loginError = 'Please fill in all fields';
    } else {
        // Query to check if user exists
        $query = "SELECT * FROM tblusers WHERE username = '$username'";
        $result = mysqli_query($connection, $query);
        
        if (!$result) {
            $loginError = 'Database error occurred';
        } elseif (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            if (password_verify($password, $user['password'])) {
                // Update lastLogin timestamp
                $updateLoginQuery = "UPDATE tblusers SET lastLogin = NOW() WHERE userID = " . $user['userID'];
                mysqli_query($connection, $updateLoginQuery);

                // Set session variables
                $_SESSION['userID'] = $user['userID'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullName'] = $user['fullName'];
                $_SESSION['userType'] = $user['userType'];
                
                $_SESSION['login_success'] = true;
                // Redirect based on user type
                if ($user['userType'] == 'admin') {
                    header("Location: ../../pages/adminPages/adminIndex.php");
                } else {
                    header("Location: ../../pages/memberPages/memberIndex.php");
                }
                exit();
            } else {
                $loginError = 'Invalid username or password';
            }
        } else {
            $loginError = 'Invalid username or password';
        }
    }
    $currentPage = 'login';
}

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $fullname = mysqli_real_escape_string($connection, trim($_POST['fullname']));
    $username = mysqli_real_escape_string($connection, trim($_POST['username']));
    $email = mysqli_real_escape_string($connection, trim($_POST['email']));
    $country = mysqli_real_escape_string($connection, $_POST['country']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm-password']);
    
    // Capitalize first letter of each word for country
    $country = ucwords(str_replace('-', ' ', $country));
    
    // Validation
    if (empty($fullname) || empty($username) || empty($email) || empty($country) || empty($password) || empty($confirmPassword)) {
        $registerError = 'Please fill in all fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registerError = 'Invalid email format';
    } elseif (strlen($password) < 8) {
        $registerError = 'Password must be at least 8 characters long';
    } elseif ($password !== $confirmPassword) {
        $registerError = 'Passwords do not match';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $registerError = 'Password must contain at least one uppercase letter, one lowercase letter, and one number';
    } else {
        // Check if username already exists
        $checkUsername = "SELECT username FROM tblusers WHERE username = '$username'";
        $resultUsername = mysqli_query($connection, $checkUsername);
        
        // Check if email already exists
        $checkEmail = "SELECT email FROM tblusers WHERE email = '$email'";
        $resultEmail = mysqli_query($connection, $checkEmail);
        
        if (mysqli_num_rows($resultUsername) > 0) {
            $registerError = 'Username already exists';
        } elseif (mysqli_num_rows($resultEmail) > 0) {
            $registerError = 'Email already exists';
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user 
            $insertQuery = "INSERT INTO tblusers (fullName, username, email, password, country, userType, point, bio, gender, tradesCompleted, lastLogin, createdAt) 
               VALUES ('$fullname', '$username', '$email', '$hashedPassword', '$country', 'member', 0, '', '', 0, NOW(), NOW())";
            
            if (mysqli_query($connection, $insertQuery)) {
                $_SESSION['registration_success'] = true;
                $_SESSION['currentAuthPage'] = 'login';
                
                // Redirect to clear POST data and show success message
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $registerError = 'Registration failed. Please try again. Error: ' . mysqli_error($connection);
            }
        }
    }
    if (!empty($registerError)) {
        $currentPage = 'signup';
    }
}

mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sign In - ReLeaf</title>
        <link rel="icon" type="image/png" href="../../assets/images/Logo.png">

        <link rel="stylesheet" href="../../style/style.css">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
            rel="stylesheet">

        <style>
            .c-navbar-more {
                display: flex;
            }

            /* Additional styling unique to page */
            .auth-container {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 3rem;
                flex-wrap: nowrap;
                max-width: 1100px;
                margin: 2rem auto;
                padding: 2rem;
                box-sizing: border-box;
            }

            .auth-box {
                background: var(--bg-color);
                border-radius: 24px;
                box-shadow: 0 4px 32px var(--Gray);
                padding: 3rem;
                width: 100%;
                max-width: 480px;
            }

            .auth-illustration {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                flex: 1;
                max-width: 400px;
            }

            .auth-illustration img {
                width: 100%;
                max-width: 400px;
                height: auto;
                z-index: 1;
                position: relative;
            }

            .auth-illustration::before {
                content: "";
                position: absolute;
                width: 280px;
                height: 280px;
                background: var(--LightGreen);
                border-radius: 50%;
                filter: blur(100px);
                z-index: -1;
            }

            .auth-title {
                text-align: center;
                font-size: 2rem;
                font-weight: 700;
                margin-bottom: 2rem;
            }

            .auth-form {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }

            .auth-input-group {
                display: flex;
                flex-direction: column;
            }

            .auth-password-group {
                position: relative;
            }

            .auth-password-wrapper {
                position: relative;
                display: flex;
                align-items: center;
            }

            .auth-password-wrapper input {
                padding-right: 3rem;
                flex: 1;
            }

            .auth-password-wrapper .password-toggle-btn {
                position: absolute;
                right: 0.5rem;
                background: transparent;
                border: none;
                padding: 0.25rem;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: opacity 0.2s ease;
            }

            .password-toggle-btn img {
                width: 18px;
                height: 18px;
                object-fit: contain;
            }

            .password-toggle-btn img {
                content: url('../../assets/images/visibility-on-btn.svg');
            }

            .dark-mode .password-toggle-btn img {
                content: url('../../assets/images/visibility-on-btn-dark.svg');
            }

            .password-toggle-btn.showing img {
                content: url('../../assets/images/visibility-off-btn.svg');
            }

            .dark-mode .password-toggle-btn.showing img {
                content: url('../../assets/images/visibility-off-btn-dark.svg');
            }

            .auth-submit-btn {
                background: var(--MainGreen);
                color: var(--White);
                padding: 1rem 1.5rem;
                font-size: 1.1rem;
                font-weight: 600;
                border-radius: 12px;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .auth-submit-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px var(--LightGreen);
            }

            .auth-submit-btn:active {
                transform: translateY(0);
            }

            .auth-bottom-text {
                text-align: center;
                font-size: 0.8rem;
                color: var(--DarkerGray);
            }

            .dark-mode .auth-bottom-text {
                color: var(--Gray);
            }

            .auth-bottom-text a {
                color: var(--text-color);
                font-size: 0.8rem;
                font-weight: 600;
                text-decoration: none;
            }

            .auth-bottom-text a:hover {
                color: var(--MainGreen);
                text-decoration: underline;
            }

            /* Error msg */
            .error-message-wrapper {
                display: none;
                align-items: center;
                gap: 0.5rem;
                background: var(--Red);
                color: var(--White);
                padding: 1rem;
                border-radius: 8px;
                margin-bottom: 1rem;
                font-size: 0.9rem;
            }

            .error-message {
                margin: 0;
                flex: 1;
            }

            .error-message-wrapper img {
                width: 18px;
                height: 18px;
                object-fit: contain;
            }

            .error-message.show,
            .error-message-wrapper.show {
                display: flex;
                animation: shake 0.3s ease;
            }

            /* Success msg */
            .success-message-wrapper {
                display: none;
                align-items: center;
                gap: 0.5rem;
                background: var(--MainGreen);
                color: var(--White);
                padding: 1rem;
                border-radius: 8px;
                margin-bottom: 1rem;
                font-size: 0.9rem;
            }

            .success-message {
                margin: 0;
                flex: 1;
            }

            .success-message-wrapper img {
                width: 18px;
                height: 18px;
                object-fit: contain;
            }

            .success-message.show,
            .success-message-wrapper.show {
                display: flex;
                animation: shake 0.3s ease;
            }

            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }

            /* Select dropdown styles */
            .input-select-wrapper {
                position: relative;
                display: flex;
                align-items: center;
            }

            /* Tablet Styles */
            @media (max-width: 1024px) {
                .auth-container {
                    flex-direction: column;
                    padding: 1.5rem 1rem;
                    gap: 1rem;
                }
            }

            /* Mobile Styles */
            @media (max-width: 760px) {
                .auth-box {
                    padding: 2rem 1.5rem;
                    max-width: 100%;
                }

                .auth-title {
                    font-size: 1.75rem;
                }

                .auth-password-wrapper input {
                    padding-right: 2.5rem;
                }

                .password-toggle-btn img {
                    width: 18px;
                    height: 18px;
                }

                .error-message-wrapper img {
                    width: 18px;
                    height: 18px;
                }

                .auth-form {
                    gap: 1.25rem;
                }
            }

            /* Small Mobile */
            @media (max-width: 480px) {
                .auth-container {
                    padding: 1rem 0.75rem;
                }

                .auth-box {
                    padding: 1.5rem 1rem;
                    border-radius: 20px;
                }

                .auth-illustration {
                    order: 1;
                    max-width: 300px;
                    margin-top: 0;
                }

                .auth-illustration img {
                    max-width: 250px;
                }

                .auth-illustration::before {
                    width: 230px;
                    height: 230px;
                    filter: blur(80px);
                }
            }
        </style>
    </head>

    <body>
        <div id="cover" class="" onclick="hideMenu()"></div>

        <!-- Logo + Name & Navbar -->
        <header>
            <!-- Logo + Name -->
            <section class="c-logo-section">
                <a href="../../index.php" class="c-logo-link">
                    <img src="../../assets/images/Logo.png" alt="Logo" class="c-logo">
                    <div class="c-text">ReLeaf</div>
                </a>
            </section>  
            <section class="c-navbar-more">
                <button id="themeToggle2">
                    <img src="../../assets/images/light-mode-icon.svg" alt="Light Mode Icon">
                </button>
            </section>      
        </header>

        <hr>
        
        <!-- Main Content -->
        <main class="auth-container">

            <!--=== Log In Box ===-->
            <section id="login" class="auth-box" style="display: <?php echo $currentPage === 'login' ? 'block' : 'none'; ?>;">
                <h2 class="c-text auth-title">Login</h2>
                
                <?php if ($registrationSuccess): ?>
                <section class="success-message-wrapper show">
                    <img src="../../assets/images/circle-check-icon.svg" alt="Success">
                    <section class="success-message">Registration successful! Please login to proceed.</section>
                </section>
                <?php endif; ?>
                
                <?php if (!empty($loginError)): ?>
                <section class="error-message-wrapper show">
                    <img src="../../assets/images/warning-icon.svg" alt="Warning">
                    <section class="error-message"><?php echo htmlspecialchars($loginError); ?></section>
                </section>
                <?php endif; ?>

                <form id="loginForm" method="POST" action="" class="auth-form">
                    <!-- Username -->
                    <section class="auth-input-group">
                        <label for="loginUsername">Username</label>
                        <input class="c-input" type="text" id="loginUsername" name="username" 
                               placeholder="Enter your Username" 
                               value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                               required>
                    </section>

                    <!-- Password -->
                    <section class="auth-input-group auth-password-group">
                        <label for="loginPassword">Password</label>
                        <section class="auth-password-wrapper">
                            <input class="c-input" type="password" id="loginPassword" name="password" placeholder="Enter your Password" required>
                            <button class="password-toggle-btn" id="showPw1" type="button"> 
                                <img src="../../assets/images/visibility-on-btn.svg" alt="Show Password">
                            </button>   
                        </section>
                    </section>
                    <br>

                    <!-- Submit Button -->
                    <button type="submit" name="login" class="c-btn auth-submit-btn">Log In</button>

                    <!-- Switch to Register -->
                    <p class="auth-bottom-text">
                        Don't have an account? <a href="#" id="goToSignUp">Sign Up</a>
                    </p>
                </form>
            </section>


            <!--=== Sign Up Box ====-->
            <section id="signUp" class="auth-box" style="display: <?php echo $currentPage === 'signup' ? 'block' : 'none'; ?>;">
                <h2 class="auth-title">Sign Up</h2>
                
                <?php if (!empty($registerError)): ?>
                <section class="error-message-wrapper show">
                    <img src="../../assets/images/warning-icon.svg" alt="Warning">
                    <section class="error-message"><?php echo htmlspecialchars($registerError); ?></section>
                </section>
                <?php endif; ?>
                
                <form id="registerForm" method="POST" action="" class="auth-form">
                    
                    <!-- Full Name -->
                    <section class="auth-input-group">
                        <label for="registerFullname">Full Name</label>
                        <input class="c-input" type="text" id="registerFullname" name="fullname" 
                               placeholder="Enter Full Name" 
                               value="<?php echo isset($_POST['register']) ? htmlspecialchars($_POST['fullname']) : ''; ?>" 
                               required>
                    </section>

                    <!-- Username -->
                    <section class="auth-input-group">
                        <label for="registerUsername">Username</label>
                        <input class="c-input" type="text" id="registerUsername" name="username" 
                               placeholder="Choose your Username" 
                               value="<?php echo isset($_POST['register']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                               required>
                    </section>

                    <!-- Email -->
                    <section class="auth-input-group">
                        <label for="registerEmail">Email Address</label>
                        <input class="c-input" type="email" id="registerEmail" name="email" 
                               placeholder="Enter your Email" 
                               value="<?php echo isset($_POST['register']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                               required>
                    </section>

                    <!-- Country -->
                    <section class="auth-input-group">
                        <label for="registerCountry">Country</label>
                        <div class="input-select-wrapper">
                            <select class="c-input c-input-select" id="registerCountry" name="country" required>
                                <option value="" disabled selected>Select your Country</option>
                                <?php 
                                foreach ($countries as $country) {
                                    echo "<option value=\"$country\">$country</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </section>

                    <!-- Password -->
                    <section class="auth-input-group auth-password-group">
                        <label for="registerPassword">Password</label>
                        <section class="auth-password-wrapper">
                            <input class="c-input" type="password" id="registerPassword" name="password" 
                                placeholder="Enter your Password (min 8 characters)" required>
                            <button class="password-toggle-btn" id="showPw2" type="button"> 
                                <img src="../../assets/images/visibility-on-btn.svg" alt="Show Password">
                            </button>   
                        </section>
                    </section>

                    <!-- Confirm Password -->
                    <section class="auth-input-group auth-password-group">
                        <label for="registerConfirmPassword">Confirm Password</label>
                        <section class="auth-password-wrapper">
                            <input class="c-input" type="password" id="registerConfirmPassword" name="confirm-password" 
                                placeholder="Re-enter your Password" required>
                            <button class="password-toggle-btn" id="showPw3" type="button"> 
                                <img src="../../assets/images/visibility-on-btn.svg" alt="Show Password">
                            </button>   
                        </section>
                    </section>
                    <br>

                    <!-- Submit Button -->
                    <button type="submit" name="register" class="c-btn auth-submit-btn">Register</button>

                    <!-- Switch to Login -->
                    <p class="auth-bottom-text">
                        Already have an account? <a href="#" id="goToLogin">Login</a>
                    </p>
                </form>
            </section>
            <section class="auth-illustration">
                <img src="../../assets/images/plant.png" alt="illutration">
            </section>
            <br>
        </main>

        <script src="../../javascript/mainScript.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const loginSection = document.getElementById('login');
                const signUpSection = document.getElementById('signUp');
                const goToSignUpLink = document.getElementById('goToSignUp');
                const goToLoginLink = document.getElementById('goToLogin');
                const loginForm = document.getElementById('loginForm');
                const registerForm = document.getElementById('registerForm');

                function clearForm(form) {
                    if (form) {
                        form.reset();
                        const errorWrappers = form.parentElement.querySelectorAll('.error-message-wrapper, .success-message-wrapper');
                        errorWrappers.forEach(wrapper => {
                            wrapper.classList.remove('show');
                        });
                    }
                }

                function showLoginPage() {
                    if (loginSection) loginSection.style.display = 'block';
                    if (signUpSection) signUpSection.style.display = 'none';
                }
                
                function showSignUpPage() {
                    if (loginSection) loginSection.style.display = 'none';
                    if (signUpSection) signUpSection.style.display = 'block';
                }
                
                if (goToSignUpLink) {
                    goToSignUpLink.addEventListener('click', function(e) {
                        e.preventDefault();
                        clearForm(loginForm);
                        showSignUpPage();
                    });
                }
                
                if (goToLoginLink) {
                    goToLoginLink.addEventListener('click', function(e) {
                        e.preventDefault();
                        clearForm(registerForm);
                        showLoginPage();
                    });
                }
            });

            // Password visibility toggle
            document.addEventListener('DOMContentLoaded', function() {    
                const toggleButtons = [
                    { buttonId: 'showPw1', passwordId: 'loginPassword' },
                    { buttonId: 'showPw2', passwordId: 'registerPassword' },
                    { buttonId: 'showPw3', passwordId: 'registerConfirmPassword' }
                ];

                toggleButtons.forEach(toggle => {
                    const toggleButton = document.getElementById(toggle.buttonId);
                    const passwordInput = document.getElementById(toggle.passwordId);
                    
                    if (toggleButton && passwordInput) {
                        toggleButton.classList.add('password-toggle-btn');
                        
                        toggleButton.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            // Toggle password visibility
                            const isPasswordVisible = passwordInput.type === 'password';
                            passwordInput.type = isPasswordVisible ? 'text' : 'password';
                            
                            toggleButton.classList.toggle('showing', isPasswordVisible);
                        });
                    }
                });
            });
        </script>
    </body>
</html>