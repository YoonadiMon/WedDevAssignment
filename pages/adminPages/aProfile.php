<?php
session_start();
include("../../php/dbConn.php");
include("../../php/sessionCheck.php");

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: ../../pages/CommonPages/signUpPage.php");
    exit();
}

$userID = $_SESSION['userID'];
$message = '';
$messageType = '';

// Fetch user data
$userQuery = "SELECT fullName, username, gender, email, password, bio, country FROM tblusers WHERE userID = " . intval($userID);
$userResult = mysqli_query($connection, $userQuery);
$user = mysqli_fetch_assoc($userResult);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = mysqli_real_escape_string($connection, trim($_POST['fullName']));
    $username = mysqli_real_escape_string($connection, trim($_POST['username']));
    $email = mysqli_real_escape_string($connection, trim($_POST['email']));
    $gender = mysqli_real_escape_string($connection, $_POST['gender']);
    $oldPassword = trim($_POST['oldPassword']);
    $newPassword = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);
    $bio = mysqli_real_escape_string($connection, trim($_POST['bio']));
    $country = mysqli_real_escape_string($connection, $_POST['country']);

    // Validation
    if (empty($fullName) || empty($username)) {
        $message = "Full name and username are required.";
        $messageType = "error";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $messageType = "error";
    } elseif (strlen($bio) > 500) {
        $message = "Bio cannot exceed 500 characters.";
        $messageType = "error";
    } else {
        $passwordChanged = false;
        $updateSuccess = false;

        // ASSWORD CHANGE 
        if (!empty($newPassword) || !empty($confirmPassword)) {
            if (empty($oldPassword)) {
                $message = "Current password is required to change password.";
                $messageType = "error";
            } elseif (!password_verify($oldPassword, $user['password'])) {
                $message = "Current password is incorrect!";
                $messageType = "error";
            } elseif ($newPassword !== $confirmPassword) {
                $message = "New passwords do not match.";
                $messageType = "error";
            } elseif (strlen($newPassword) < 8) {
                $message = "New password must be at least 8 characters.";
                $messageType = "error";
            } elseif (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
                $message = "Password must contain uppercase, lowercase, and numbers.";
                $messageType = "error";
            } else {
                // Update with new password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                $updateQuery = "
                    UPDATE tblusers 
                    SET fullName = '$fullName', 
                        username = '$username', 
                        gender = '$gender', 
                        email = '$email', 
                        password = '$hashedPassword', 
                        bio = '$bio', 
                        country = '$country'
                    WHERE userID = $userID
                ";

                if (mysqli_query($connection, $updateQuery)) {
                    $updateSuccess = true;
                    $passwordChanged = true;
                    $message = "Password updated successfully ✓";
                    $messageType = "success";
                } else {
                    $message = "Error updating profile: " . mysqli_error($connection);
                    $messageType = "error";
                }
            }
        } 
        
        else {
            $updateQuery = "
                UPDATE tblusers 
                SET fullName = '$fullName', 
                    username = '$username', 
                    gender = '$gender', 
                    email = '$email', 
                    bio = '$bio', 
                    country = '$country'
                WHERE userID = $userID
            ";

            if (mysqli_query($connection, $updateQuery)) {
                $updateSuccess = true;
                $message = "Profile updated successfully ✓";
                $messageType = "success";
            } else {
                $message = "Error updating profile: " . mysqli_error($connection);
                $messageType = "error";
            }
        }

        // Refresh user data after successful update
        if ($updateSuccess) {
            $refreshQuery = "SELECT fullName, username, gender, email, password, bio, country FROM tblusers WHERE userID = $userID";
            $refreshResult = mysqli_query($connection, $refreshQuery);
            $user = mysqli_fetch_assoc($refreshResult);
        }
    }
}

mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - ReLeaf</title>

    <link rel="stylesheet" href="../../style/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">

    <style>
        /* Password toggle styling */
        .password-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-wrap .input {
            padding-right: 60px;
            width: 100%;
            box-sizing: border-box;
        }

        .toggle-password {
            position: absolute;
            right: 8px;
            background: transparent;
            border: none;
            padding: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            line-height: 0;
            color: inherit;
        }

        .toggle-password:focus {
            outline: 2px solid rgba(0, 0, 0, 0.12);
            border-radius: 6px;
        }

        .toggle-password[aria-pressed="false"] svg {
            opacity: 0.7;
        }

        .toggle-password[aria-pressed="true"] svg {
            opacity: 1;
        }

        /* Enhanced toast notification */
        .toast {
            position: fixed;
            bottom: -100px;
            left: 50%;
            transform: translateX(-50%);
            background: #16a34a;
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: bottom 0.3s ease-in-out;
            z-index: 1000;
            max-width: 90%;
            text-align: center;
            min-width: 250px;
        }

        .toast.show {
            bottom: 20px;
        }

        .toast.error {
            background: #dc2626;
        }

        .toast.success {
            background: #16a34a;
        }
    </style>
</head>

<body>
    <div id="cover" class="" onclick="hideMenu()"></div>

    <!-- Logo + Name & Navbar -->
    <header>
        <!-- Logo + Name -->
        <section class="c-logo-section">
            <a href="../../pages/adminPages/adminIndex.php" class="c-logo-link">
                <img src="../../assets/images/Logo.png" alt="Logo" class="c-logo">
                <div class="c-text">ReLeaf</div>
            </a>
        </section>

        <!-- Menu Links -->

        <!-- Menu Links Mobile -->
        <nav class="c-navbar-side">
            <input type="text" placeholder="Search..." id="searchBar" class="search-bar">
            <img src="../../assets/images/icon-menu.svg" alt="icon-menu" onclick="showMenu()" class="c-icon-btn" id="menuBtn">
            <div id="sidebarNav" class="c-navbar-side-menu">

                <img src="../../assets/images/icon-menu-close.svg" alt="icon-menu-close" onclick="hideMenu()" class="close-btn">
                <div class="c-navbar-side-items">
                    <section class="c-navbar-side-more">
                        <button id="themeToggle1">
                            <img src="../../assets/images/light-mode-icon.svg" alt="Light Mode Icon">
                        </button>
                        <a href="../../pages/adminPages/aProfile.php">
                            <img src="../../assets/images/profile-light.svg" alt="Profile">
                        </a>
                    </section>

                    <a href="../../pages/adminPages/adminIndex.php">Dashboard</a>
                    <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
                    <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                    <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                    <a href="../../pages/CommonPages/mainFAQ.php">FAQs</a>
                    <a href="../../pages/adminPages/aHelpTicket.php">Help</a>
                </div>
            </div>

        </nav>

        <!-- Menu Links Desktop + Tablet -->
        <nav class="c-navbar-desktop">
            <a href="../../pages/adminPages/adminIndex.php">Dashboard</a>
            <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
            <a href="../../pages/CommonPages/mainEvent.php">Event</a>
            <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
            <a href="../../pages/CommonPages/mainFAQ.php">FAQs</a>
            <a href="../../pages/adminPages/aHelpTicket.php">Help</a>
        </nav>
        <section class="c-navbar-more">
            <input type="text" placeholder="Search..." id="searchBar" class="search-bar">
            <button id="themeToggle2">
                <img src="../../assets/images/light-mode-icon.svg" alt="Light Mode Icon">
            </button>
            <a href="../../pages/adminPages/aProfile.php">
                <img src="../../assets/images/profile-light.svg" alt="Profile" id="profileImg">
            </a>
        </section>
    </header>
    <hr>

    <!-- Main Content -->
    <main class="content" id="content">
        <div class="mprofile-container">
            <main>
                <div class="left">

                    <div class="avatar" id="avatar">
                        <img id="avatarImg" src="" alt="avatar" style="display:none">
                    </div>

                    <div class="change-wrap">
                        <!-- <div class="hint" id="fileHint">Generated Avatar</div> -->
                    </div>
                </div>

                <div class="right">
                    <h1>Editing Profile</h1>

                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="label">Full Name:</div>
                            <input id="fullName" name="fullName" class="input" type="text"
                                placeholder="Enter your full name"
                                value="<?php echo htmlspecialchars($user['fullName'] ?? ''); ?>" required />
                        </div>

                        <div class="form-row">
                            <div class="label">Username:</div>
                            <input id="username" name="username" class="input" type="text"
                                placeholder="Enter your username"
                                value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required />
                        </div>

                        <div class="form-row">
                            <div class="label">Email:</div>
                            <input id="email" name="email" class="input" type="email"
                                placeholder="Enter your email address"
                                value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" />
                        </div>

                        <div class="form-row">
                            <div class="label">Gender:</div>
                            <select id="gender" name="gender" class="input">
                                <option value="">Select gender</option>
                                <option value="male" <?php echo (($user['gender'] ?? '') === 'male') ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo (($user['gender'] ?? '') === 'female') ? 'selected' : ''; ?>>Female</option>
                                <option value="rather not to say" <?php echo (($user['gender'] ?? '') === 'rather not to say') ? 'selected' : ''; ?>>Rather not to say</option>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="label">Country:</div>
                            <select id="country" name="country" class="input">
                                <option value="">Select country</option>
                                <option value="Afghanistan" <?php echo ($user['country'] === 'Afghanistan') ? 'selected' : ''; ?>>Afghanistan</option>
                                <option value="Åland Islands" <?php echo ($user['country'] === 'Åland Islands') ? 'selected' : ''; ?>>Åland Islands</option>
                                <option value="Albania" <?php echo ($user['country'] === 'Albania') ? 'selected' : ''; ?>>Albania</option>
                                <option value="Algeria" <?php echo ($user['country'] === 'Algeria') ? 'selected' : ''; ?>>Algeria</option>
                                <option value="American Samoa" <?php echo ($user['country'] === 'American Samoa') ? 'selected' : ''; ?>>American Samoa</option>
                                <option value="Andorra" <?php echo ($user['country'] === 'Andorra') ? 'selected' : ''; ?>>Andorra</option>
                                <option value="Angola" <?php echo ($user['country'] === 'Angola') ? 'selected' : ''; ?>>Angola</option>
                                <option value="Anguilla" <?php echo ($user['country'] === 'Anguilla') ? 'selected' : ''; ?>>Anguilla</option>
                                <option value="Antarctica" <?php echo ($user['country'] === 'Antarctica') ? 'selected' : ''; ?>>Antarctica</option>
                                <option value="Antigua and Barbuda" <?php echo ($user['country'] === 'Antigua and Barbuda') ? 'selected' : ''; ?>>Antigua and Barbuda</option>
                                <option value="Argentina" <?php echo ($user['country'] === 'Argentina') ? 'selected' : ''; ?>>Argentina</option>
                                <option value="Armenia" <?php echo ($user['country'] === 'Armenia') ? 'selected' : ''; ?>>Armenia</option>
                                <option value="Aruba" <?php echo ($user['country'] === 'Aruba') ? 'selected' : ''; ?>>Aruba</option>
                                <option value="Australia" <?php echo ($user['country'] === 'Australia') ? 'selected' : ''; ?>>Australia</option>
                                <option value="Austria" <?php echo ($user['country'] === 'Austria') ? 'selected' : ''; ?>>Austria</option>
                                <option value="Azerbaijan" <?php echo ($user['country'] === 'Azerbaijan') ? 'selected' : ''; ?>>Azerbaijan</option>
                                <option value="Bahamas" <?php echo ($user['country'] === 'Bahamas') ? 'selected' : ''; ?>>Bahamas</option>
                                <option value="Bahrain" <?php echo ($user['country'] === 'Bahrain') ? 'selected' : ''; ?>>Bahrain</option>
                                <option value="Bangladesh" <?php echo ($user['country'] === 'Bangladesh') ? 'selected' : ''; ?>>Bangladesh</option>
                                <option value="Barbados" <?php echo ($user['country'] === 'Barbados') ? 'selected' : ''; ?>>Barbados</option>
                                <option value="Belarus" <?php echo ($user['country'] === 'Belarus') ? 'selected' : ''; ?>>Belarus</option>
                                <option value="Belgium" <?php echo ($user['country'] === 'Belgium') ? 'selected' : ''; ?>>Belgium</option>
                                <option value="Belize" <?php echo ($user['country'] === 'Belize') ? 'selected' : ''; ?>>Belize</option>
                                <option value="Benin" <?php echo ($user['country'] === 'Benin') ? 'selected' : ''; ?>>Benin</option>
                                <option value="Bermuda" <?php echo ($user['country'] === 'Bermuda') ? 'selected' : ''; ?>>Bermuda</option>
                                <option value="Bhutan" <?php echo ($user['country'] === 'Bhutan') ? 'selected' : ''; ?>>Bhutan</option>
                                <option value="Bolivia" <?php echo ($user['country'] === 'Bolivia') ? 'selected' : ''; ?>>Bolivia</option>
                                <option value="Bosnia and Herzegovina" <?php echo ($user['country'] === 'Bosnia and Herzegovina') ? 'selected' : ''; ?>>Bosnia and Herzegovina</option>
                                <option value="Botswana" <?php echo ($user['country'] === 'Botswana') ? 'selected' : ''; ?>>Botswana</option>
                                <option value="Bouvet Island" <?php echo ($user['country'] === 'Bouvet Island') ? 'selected' : ''; ?>>Bouvet Island</option>
                                <option value="Brazil" <?php echo ($user['country'] === 'Brazil') ? 'selected' : ''; ?>>Brazil</option>
                                <option value="British Indian Ocean Territory" <?php echo ($user['country'] === 'British Indian Ocean Territory') ? 'selected' : ''; ?>>British Indian Ocean Territory</option>
                                <option value="Brunei Darussalam" <?php echo ($user['country'] === 'Brunei Darussalam') ? 'selected' : ''; ?>>Brunei Darussalam</option>
                                <option value="Bulgaria" <?php echo ($user['country'] === 'Bulgaria') ? 'selected' : ''; ?>>Bulgaria</option>
                                <option value="Burkina Faso" <?php echo ($user['country'] === 'Burkina Faso') ? 'selected' : ''; ?>>Burkina Faso</option>
                                <option value="Burundi" <?php echo ($user['country'] === 'Burundi') ? 'selected' : ''; ?>>Burundi</option>
                                <option value="Cambodia" <?php echo ($user['country'] === 'Cambodia') ? 'selected' : ''; ?>>Cambodia</option>
                                <option value="Cameroon" <?php echo ($user['country'] === 'Cameroon') ? 'selected' : ''; ?>>Cameroon</option>
                                <option value="Canada" <?php echo ($user['country'] === 'Canada') ? 'selected' : ''; ?>>Canada</option>
                                <option value="Cape Verde" <?php echo ($user['country'] === 'Cape Verde') ? 'selected' : ''; ?>>Cape Verde</option>
                                <option value="Cayman Islands" <?php echo ($user['country'] === 'Cayman Islands') ? 'selected' : ''; ?>>Cayman Islands</option>
                                <option value="Central African Republic" <?php echo ($user['country'] === 'Central African Republic') ? 'selected' : ''; ?>>Central African Republic</option>
                                <option value="Chad" <?php echo ($user['country'] === 'Chad') ? 'selected' : ''; ?>>Chad</option>
                                <option value="Chile" <?php echo ($user['country'] === 'Chile') ? 'selected' : ''; ?>>Chile</option>
                                <option value="China" <?php echo ($user['country'] === 'China') ? 'selected' : ''; ?>>China</option>
                                <option value="Christmas Island" <?php echo ($user['country'] === 'Christmas Island') ? 'selected' : ''; ?>>Christmas Island</option>
                                <option value="Cocos (Keeling) Islands" <?php echo ($user['country'] === 'Cocos (Keeling) Islands') ? 'selected' : ''; ?>>Cocos (Keeling) Islands</option>
                                <option value="Colombia" <?php echo ($user['country'] === 'Colombia') ? 'selected' : ''; ?>>Colombia</option>
                                <option value="Comoros" <?php echo ($user['country'] === 'Comoros') ? 'selected' : ''; ?>>Comoros</option>
                                <option value="Congo" <?php echo ($user['country'] === 'Congo') ? 'selected' : ''; ?>>Congo</option>
                                <option value="Congo, The Democratic Republic of The" <?php echo ($user['country'] === 'Congo, The Democratic Republic of The') ? 'selected' : ''; ?>>Congo, The Democratic Republic of The</option>
                                <option value="Cook Islands" <?php echo ($user['country'] === 'Cook Islands') ? 'selected' : ''; ?>>Cook Islands</option>
                                <option value="Costa Rica" <?php echo ($user['country'] === 'Costa Rica') ? 'selected' : ''; ?>>Costa Rica</option>
                                <option value="Cote D'ivoire" <?php echo ($user['country'] === "Cote D'ivoire") ? 'selected' : ''; ?>>Cote D'ivoire</option>
                                <option value="Croatia" <?php echo ($user['country'] === 'Croatia') ? 'selected' : ''; ?>>Croatia</option>
                                <option value="Cuba" <?php echo ($user['country'] === 'Cuba') ? 'selected' : ''; ?>>Cuba</option>
                                <option value="Cyprus" <?php echo ($user['country'] === 'Cyprus') ? 'selected' : ''; ?>>Cyprus</option>
                                <option value="Czech Republic" <?php echo ($user['country'] === 'Czech Republic') ? 'selected' : ''; ?>>Czech Republic</option>
                                <option value="Denmark" <?php echo ($user['country'] === 'Denmark') ? 'selected' : ''; ?>>Denmark</option>
                                <option value="Djibouti" <?php echo ($user['country'] === 'Djibouti') ? 'selected' : ''; ?>>Djibouti</option>
                                <option value="Dominica" <?php echo ($user['country'] === 'Dominica') ? 'selected' : ''; ?>>Dominica</option>
                                <option value="Dominican Republic" <?php echo ($user['country'] === 'Dominican Republic') ? 'selected' : ''; ?>>Dominican Republic</option>
                                <option value="Ecuador" <?php echo ($user['country'] === 'Ecuador') ? 'selected' : ''; ?>>Ecuador</option>
                                <option value="Egypt" <?php echo ($user['country'] === 'Egypt') ? 'selected' : ''; ?>>Egypt</option>
                                <option value="El Salvador" <?php echo ($user['country'] === 'El Salvador') ? 'selected' : ''; ?>>El Salvador</option>
                                <option value="Equatorial Guinea" <?php echo ($user['country'] === 'Equatorial Guinea') ? 'selected' : ''; ?>>Equatorial Guinea</option>
                                <option value="Eritrea" <?php echo ($user['country'] === 'Eritrea') ? 'selected' : ''; ?>>Eritrea</option>
                                <option value="Estonia" <?php echo ($user['country'] === 'Estonia') ? 'selected' : ''; ?>>Estonia</option>
                                <option value="Ethiopia" <?php echo ($user['country'] === 'Ethiopia') ? 'selected' : ''; ?>>Ethiopia</option>
                                <option value="Falkland Islands (Malvinas)" <?php echo ($user['country'] === 'Falkland Islands (Malvinas)') ? 'selected' : ''; ?>>Falkland Islands (Malvinas)</option>
                                <option value="Faroe Islands" <?php echo ($user['country'] === 'Faroe Islands') ? 'selected' : ''; ?>>Faroe Islands</option>
                                <option value="Fiji" <?php echo ($user['country'] === 'Fiji') ? 'selected' : ''; ?>>Fiji</option>
                                <option value="Finland" <?php echo ($user['country'] === 'Finland') ? 'selected' : ''; ?>>Finland</option>
                                <option value="France" <?php echo ($user['country'] === 'France') ? 'selected' : ''; ?>>France</option>
                                <option value="French Guiana" <?php echo ($user['country'] === 'French Guiana') ? 'selected' : ''; ?>>French Guiana</option>
                                <option value="French Polynesia" <?php echo ($user['country'] === 'French Polynesia') ? 'selected' : ''; ?>>French Polynesia</option>
                                <option value="French Southern Territories" <?php echo ($user['country'] === 'French Southern Territories') ? 'selected' : ''; ?>>French Southern Territories</option>
                                <option value="Gabon" <?php echo ($user['country'] === 'Gabon') ? 'selected' : ''; ?>>Gabon</option>
                                <option value="Gambia" <?php echo ($user['country'] === 'Gambia') ? 'selected' : ''; ?>>Gambia</option>
                                <option value="Georgia" <?php echo ($user['country'] === 'Georgia') ? 'selected' : ''; ?>>Georgia</option>
                                <option value="Germany" <?php echo ($user['country'] === 'Germany') ? 'selected' : ''; ?>>Germany</option>
                                <option value="Ghana" <?php echo ($user['country'] === 'Ghana') ? 'selected' : ''; ?>>Ghana</option>
                                <option value="Gibraltar" <?php echo ($user['country'] === 'Gibraltar') ? 'selected' : ''; ?>>Gibraltar</option>
                                <option value="Greece" <?php echo ($user['country'] === 'Greece') ? 'selected' : ''; ?>>Greece</option>
                                <option value="Greenland" <?php echo ($user['country'] === 'Greenland') ? 'selected' : ''; ?>>Greenland</option>
                                <option value="Grenada" <?php echo ($user['country'] === 'Grenada') ? 'selected' : ''; ?>>Grenada</option>
                                <option value="Guadeloupe" <?php echo ($user['country'] === 'Guadeloupe') ? 'selected' : ''; ?>>Guadeloupe</option>
                                <option value="Guam" <?php echo ($user['country'] === 'Guam') ? 'selected' : ''; ?>>Guam</option>
                                <option value="Guatemala" <?php echo ($user['country'] === 'Guatemala') ? 'selected' : ''; ?>>Guatemala</option>
                                <option value="Guernsey" <?php echo ($user['country'] === 'Guernsey') ? 'selected' : ''; ?>>Guernsey</option>
                                <option value="Guinea" <?php echo ($user['country'] === 'Guinea') ? 'selected' : ''; ?>>Guinea</option>
                                <option value="Guinea-bissau" <?php echo ($user['country'] === 'Guinea-bissau') ? 'selected' : ''; ?>>Guinea-bissau</option>
                                <option value="Guyana" <?php echo ($user['country'] === 'Guyana') ? 'selected' : ''; ?>>Guyana</option>
                                <option value="Haiti" <?php echo ($user['country'] === 'Haiti') ? 'selected' : ''; ?>>Haiti</option>
                                <option value="Heard Island and Mcdonald Islands" <?php echo ($user['country'] === 'Heard Island and Mcdonald Islands') ? 'selected' : ''; ?>>Heard Island and Mcdonald Islands</option>
                                <option value="Holy See (Vatican City State)" <?php echo ($user['country'] === 'Holy See (Vatican City State)') ? 'selected' : ''; ?>>Holy See (Vatican City State)</option>
                                <option value="Honduras" <?php echo ($user['country'] === 'Honduras') ? 'selected' : ''; ?>>Honduras</option>
                                <option value="Hong Kong" <?php echo ($user['country'] === 'Hong Kong') ? 'selected' : ''; ?>>Hong Kong</option>
                                <option value="Hungary" <?php echo ($user['country'] === 'Hungary') ? 'selected' : ''; ?>>Hungary</option>
                                <option value="Iceland" <?php echo ($user['country'] === 'Iceland') ? 'selected' : ''; ?>>Iceland</option>
                                <option value="India" <?php echo ($user['country'] === 'India') ? 'selected' : ''; ?>>India</option>
                                <option value="Indonesia" <?php echo ($user['country'] === 'Indonesia') ? 'selected' : ''; ?>>Indonesia</option>
                                <option value="Iran, Islamic Republic of" <?php echo ($user['country'] === 'Iran, Islamic Republic of') ? 'selected' : ''; ?>>Iran, Islamic Republic of</option>
                                <option value="Iraq" <?php echo ($user['country'] === 'Iraq') ? 'selected' : ''; ?>>Iraq</option>
                                <option value="Ireland" <?php echo ($user['country'] === 'Ireland') ? 'selected' : ''; ?>>Ireland</option>
                                <option value="Isle of Man" <?php echo ($user['country'] === 'Isle of Man') ? 'selected' : ''; ?>>Isle of Man</option>
                                <option value="Israel" <?php echo ($user['country'] === 'Israel') ? 'selected' : ''; ?>>Israel</option>
                                <option value="Italy" <?php echo ($user['country'] === 'Italy') ? 'selected' : ''; ?>>Italy</option>
                                <option value="Jamaica" <?php echo ($user['country'] === 'Jamaica') ? 'selected' : ''; ?>>Jamaica</option>
                                <option value="Japan" <?php echo ($user['country'] === 'Japan') ? 'selected' : ''; ?>>Japan</option>
                                <option value="Jersey" <?php echo ($user['country'] === 'Jersey') ? 'selected' : ''; ?>>Jersey</option>
                                <option value="Jordan" <?php echo ($user['country'] === 'Jordan') ? 'selected' : ''; ?>>Jordan</option>
                                <option value="Kazakhstan" <?php echo ($user['country'] === 'Kazakhstan') ? 'selected' : ''; ?>>Kazakhstan</option>
                                <option value="Kenya" <?php echo ($user['country'] === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                                <option value="Kiribati" <?php echo ($user['country'] === 'Kiribati') ? 'selected' : ''; ?>>Kiribati</option>
                                <option value="Korea, Democratic People's Republic of" <?php echo ($user['country'] === "Korea, Democratic People's Republic of") ? 'selected' : ''; ?>>Korea, Democratic People's Republic of</option>
                                <option value="Korea, Republic of" <?php echo ($user['country'] === 'Korea, Republic of') ? 'selected' : ''; ?>>Korea, Republic of</option>
                                <option value="Kuwait" <?php echo ($user['country'] === 'Kuwait') ? 'selected' : ''; ?>>Kuwait</option>
                                <option value="Kyrgyzstan" <?php echo ($user['country'] === 'Kyrgyzstan') ? 'selected' : ''; ?>>Kyrgyzstan</option>
                                <option value="Lao People's Democratic Republic" <?php echo ($user['country'] === "Lao People's Democratic Republic") ? 'selected' : ''; ?>>Lao People's Democratic Republic</option>
                                <option value="Latvia" <?php echo ($user['country'] === 'Latvia') ? 'selected' : ''; ?>>Latvia</option>
                                <option value="Lebanon" <?php echo ($user['country'] === 'Lebanon') ? 'selected' : ''; ?>>Lebanon</option>
                                <option value="Lesotho" <?php echo ($user['country'] === 'Lesotho') ? 'selected' : ''; ?>>Lesotho</option>
                                <option value="Liberia" <?php echo ($user['country'] === 'Liberia') ? 'selected' : ''; ?>>Liberia</option>
                                <option value="Libyan Arab Jamahiriya" <?php echo ($user['country'] === 'Libyan Arab Jamahiriya') ? 'selected' : ''; ?>>Libyan Arab Jamahiriya</option>
                                <option value="Liechtenstein" <?php echo ($user['country'] === 'Liechtenstein') ? 'selected' : ''; ?>>Liechtenstein</option>
                                <option value="Lithuania" <?php echo ($user['country'] === 'Lithuania') ? 'selected' : ''; ?>>Lithuania</option>
                                <option value="Luxembourg" <?php echo ($user['country'] === 'Luxembourg') ? 'selected' : ''; ?>>Luxembourg</option>
                                <option value="Macao" <?php echo ($user['country'] === 'Macao') ? 'selected' : ''; ?>>Macao</option>
                                <option value="Macedonia, The Former Yugoslav Republic of" <?php echo ($user['country'] === 'Macedonia, The Former Yugoslav Republic of') ? 'selected' : ''; ?>>Macedonia, The Former Yugoslav Republic of</option>
                                <option value="Madagascar" <?php echo ($user['country'] === 'Madagascar') ? 'selected' : ''; ?>>Madagascar</option>
                                <option value="Malawi" <?php echo ($user['country'] === 'Malawi') ? 'selected' : ''; ?>>Malawi</option>
                                <option value="Malaysia" <?php echo ($user['country'] === 'Malaysia') ? 'selected' : ''; ?>>Malaysia</option>
                                <option value="Maldives" <?php echo ($user['country'] === 'Maldives') ? 'selected' : ''; ?>>Maldives</option>
                                <option value="Mali" <?php echo ($user['country'] === 'Mali') ? 'selected' : ''; ?>>Mali</option>
                                <option value="Malta" <?php echo ($user['country'] === 'Malta') ? 'selected' : ''; ?>>Malta</option>
                                <option value="Marshall Islands" <?php echo ($user['country'] === 'Marshall Islands') ? 'selected' : ''; ?>>Marshall Islands</option>
                                <option value="Martinique" <?php echo ($user['country'] === 'Martinique') ? 'selected' : ''; ?>>Martinique</option>
                                <option value="Mauritania" <?php echo ($user['country'] === 'Mauritania') ? 'selected' : ''; ?>>Mauritania</option>
                                <option value="Mauritius" <?php echo ($user['country'] === 'Mauritius') ? 'selected' : ''; ?>>Mauritius</option>
                                <option value="Mayotte" <?php echo ($user['country'] === 'Mayotte') ? 'selected' : ''; ?>>Mayotte</option>
                                <option value="Mexico" <?php echo ($user['country'] === 'Mexico') ? 'selected' : ''; ?>>Mexico</option>
                                <option value="Micronesia, Federated States of" <?php echo ($user['country'] === 'Micronesia, Federated States of') ? 'selected' : ''; ?>>Micronesia, Federated States of</option>
                                <option value="Moldova, Republic of" <?php echo ($user['country'] === 'Moldova, Republic of') ? 'selected' : ''; ?>>Moldova, Republic of</option>
                                <option value="Monaco" <?php echo ($user['country'] === 'Monaco') ? 'selected' : ''; ?>>Monaco</option>
                                <option value="Mongolia" <?php echo ($user['country'] === 'Mongolia') ? 'selected' : ''; ?>>Mongolia</option>
                                <option value="Montenegro" <?php echo ($user['country'] === 'Montenegro') ? 'selected' : ''; ?>>Montenegro</option>
                                <option value="Montserrat" <?php echo ($user['country'] === 'Montserrat') ? 'selected' : ''; ?>>Montserrat</option>
                                <option value="Morocco" <?php echo ($user['country'] === 'Morocco') ? 'selected' : ''; ?>>Morocco</option>
                                <option value="Mozambique" <?php echo ($user['country'] === 'Mozambique') ? 'selected' : ''; ?>>Mozambique</option>
                                <option value="Myanmar" <?php echo ($user['country'] === 'Myanmar') ? 'selected' : ''; ?>>Myanmar</option>
                                <option value="Namibia" <?php echo ($user['country'] === 'Namibia') ? 'selected' : ''; ?>>Namibia</option>
                                <option value="Nauru" <?php echo ($user['country'] === 'Nauru') ? 'selected' : ''; ?>>Nauru</option>
                                <option value="Nepal" <?php echo ($user['country'] === 'Nepal') ? 'selected' : ''; ?>>Nepal</option>
                                <option value="Netherlands" <?php echo ($user['country'] === 'Netherlands') ? 'selected' : ''; ?>>Netherlands</option>
                                <option value="Netherlands Antilles" <?php echo ($user['country'] === 'Netherlands Antilles') ? 'selected' : ''; ?>>Netherlands Antilles</option>
                                <option value="New Caledonia" <?php echo ($user['country'] === 'New Caledonia') ? 'selected' : ''; ?>>New Caledonia</option>
                                <option value="New Zealand" <?php echo ($user['country'] === 'New Zealand') ? 'selected' : ''; ?>>New Zealand</option>
                                <option value="Nicaragua" <?php echo ($user['country'] === 'Nicaragua') ? 'selected' : ''; ?>>Nicaragua</option>
                                <option value="Niger" <?php echo ($user['country'] === 'Niger') ? 'selected' : ''; ?>>Niger</option>
                                <option value="Nigeria" <?php echo ($user['country'] === 'Nigeria') ? 'selected' : ''; ?>>Nigeria</option>
                                <option value="Niue" <?php echo ($user['country'] === 'Niue') ? 'selected' : ''; ?>>Niue</option>
                                <option value="Norfolk Island" <?php echo ($user['country'] === 'Norfolk Island') ? 'selected' : ''; ?>>Norfolk Island</option>
                                <option value="Northern Mariana Islands" <?php echo ($user['country'] === 'Northern Mariana Islands') ? 'selected' : ''; ?>>Northern Mariana Islands</option>
                                <option value="Norway" <?php echo ($user['country'] === 'Norway') ? 'selected' : ''; ?>>Norway</option>
                                <option value="Oman" <?php echo ($user['country'] === 'Oman') ? 'selected' : ''; ?>>Oman</option>
                                <option value="Pakistan" <?php echo ($user['country'] === 'Pakistan') ? 'selected' : ''; ?>>Pakistan</option>
                                <option value="Palau" <?php echo ($user['country'] === 'Palau') ? 'selected' : ''; ?>>Palau</option>
                                <option value="Palestinian Territory, Occupied" <?php echo ($user['country'] === 'Palestinian Territory, Occupied') ? 'selected' : ''; ?>>Palestinian Territory, Occupied</option>
                                <option value="Panama" <?php echo ($user['country'] === 'Panama') ? 'selected' : ''; ?>>Panama</option>
                                <option value="Papua New Guinea" <?php echo ($user['country'] === 'Papua New Guinea') ? 'selected' : ''; ?>>Papua New Guinea</option>
                                <option value="Paraguay" <?php echo ($user['country'] === 'Paraguay') ? 'selected' : ''; ?>>Paraguay</option>
                                <option value="Peru" <?php echo ($user['country'] === 'Peru') ? 'selected' : ''; ?>>Peru</option>
                                <option value="Philippines" <?php echo ($user['country'] === 'Philippines') ? 'selected' : ''; ?>>Philippines</option>
                                <option value="Pitcairn" <?php echo ($user['country'] === 'Pitcairn') ? 'selected' : ''; ?>>Pitcairn</option>
                                <option value="Poland" <?php echo ($user['country'] === 'Poland') ? 'selected' : ''; ?>>Poland</option>
                                <option value="Portugal" <?php echo ($user['country'] === 'Portugal') ? 'selected' : ''; ?>>Portugal</option>
                                <option value="Puerto Rico" <?php echo ($user['country'] === 'Puerto Rico') ? 'selected' : ''; ?>>Puerto Rico</option>
                                <option value="Qatar" <?php echo ($user['country'] === 'Qatar') ? 'selected' : ''; ?>>Qatar</option>
                                <option value="Reunion" <?php echo ($user['country'] === 'Reunion') ? 'selected' : ''; ?>>Reunion</option>
                                <option value="Romania" <?php echo ($user['country'] === 'Romania') ? 'selected' : ''; ?>>Romania</option>
                                <option value="Russian Federation" <?php echo ($user['country'] === 'Russian Federation') ? 'selected' : ''; ?>>Russian Federation</option>
                                <option value="Rwanda" <?php echo ($user['country'] === 'Rwanda') ? 'selected' : ''; ?>>Rwanda</option>
                                <option value="Saint Helena" <?php echo ($user['country'] === 'Saint Helena') ? 'selected' : ''; ?>>Saint Helena</option>
                                <option value="Saint Kitts and Nevis" <?php echo ($user['country'] === 'Saint Kitts and Nevis') ? 'selected' : ''; ?>>Saint Kitts and Nevis</option>
                                <option value="Saint Lucia" <?php echo ($user['country'] === 'Saint Lucia') ? 'selected' : ''; ?>>Saint Lucia</option>
                                <option value="Saint Pierre and Miquelon" <?php echo ($user['country'] === 'Saint Pierre and Miquelon') ? 'selected' : ''; ?>>Saint Pierre and Miquelon</option>
                                <option value="Saint Vincent and The Grenadines" <?php echo ($user['country'] === 'Saint Vincent and The Grenadines') ? 'selected' : ''; ?>>Saint Vincent and The Grenadines</option>
                                <option value="Samoa" <?php echo ($user['country'] === 'Samoa') ? 'selected' : ''; ?>>Samoa</option>
                                <option value="San Marino" <?php echo ($user['country'] === 'San Marino') ? 'selected' : ''; ?>>San Marino</option>
                                <option value="Sao Tome and Principe" <?php echo ($user['country'] === 'Sao Tome and Principe') ? 'selected' : ''; ?>>Sao Tome and Principe</option>
                                <option value="Saudi Arabia" <?php echo ($user['country'] === 'Saudi Arabia') ? 'selected' : ''; ?>>Saudi Arabia</option>
                                <option value="Senegal" <?php echo ($user['country'] === 'Senegal') ? 'selected' : ''; ?>>Senegal</option>
                                <option value="Serbia" <?php echo ($user['country'] === 'Serbia') ? 'selected' : ''; ?>>Serbia</option>
                                <option value="Seychelles" <?php echo ($user['country'] === 'Seychelles') ? 'selected' : ''; ?>>Seychelles</option>
                                <option value="Sierra Leone" <?php echo ($user['country'] === 'Sierra Leone') ? 'selected' : ''; ?>>Sierra Leone</option>
                                <option value="Singapore" <?php echo ($user['country'] === 'Singapore') ? 'selected' : ''; ?>>Singapore</option>
                                <option value="Slovakia" <?php echo ($user['country'] === 'Slovakia') ? 'selected' : ''; ?>>Slovakia</option>
                                <option value="Slovenia" <?php echo ($user['country'] === 'Slovenia') ? 'selected' : ''; ?>>Slovenia</option>
                                <option value="Solomon Islands" <?php echo ($user['country'] === 'Solomon Islands') ? 'selected' : ''; ?>>Solomon Islands</option>
                                <option value="Somalia" <?php echo ($user['country'] === 'Somalia') ? 'selected' : ''; ?>>Somalia</option>
                                <option value="South Africa" <?php echo ($user['country'] === 'South Africa') ? 'selected' : ''; ?>>South Africa</option>
                                <option value="South Georgia and The South Sandwich Islands" <?php echo ($user['country'] === 'South Georgia and The South Sandwich Islands') ? 'selected' : ''; ?>>South Georgia and The South Sandwich Islands</option>
                                <option value="Spain" <?php echo ($user['country'] === 'Spain') ? 'selected' : ''; ?>>Spain</option>
                                <option value="Sri Lanka" <?php echo ($user['country'] === 'Sri Lanka') ? 'selected' : ''; ?>>Sri Lanka</option>
                                <option value="Sudan" <?php echo ($user['country'] === 'Sudan') ? 'selected' : ''; ?>>Sudan</option>
                                <option value="Suriname" <?php echo ($user['country'] === 'Suriname') ? 'selected' : ''; ?>>Suriname</option>
                                <option value="Svalbard and Jan Mayen" <?php echo ($user['country'] === 'Svalbard and Jan Mayen') ? 'selected' : ''; ?>>Svalbard and Jan Mayen</option>
                                <option value="Swaziland" <?php echo ($user['country'] === 'Swaziland') ? 'selected' : ''; ?>>Swaziland</option>
                                <option value="Sweden" <?php echo ($user['country'] === 'Sweden') ? 'selected' : ''; ?>>Sweden</option>
                                <option value="Switzerland" <?php echo ($user['country'] === 'Switzerland') ? 'selected' : ''; ?>>Switzerland</option>
                                <option value="Syrian Arab Republic" <?php echo ($user['country'] === 'Syrian Arab Republic') ? 'selected' : ''; ?>>Syrian Arab Republic</option>
                                <option value="Taiwan" <?php echo ($user['country'] === 'Taiwan') ? 'selected' : ''; ?>>Taiwan</option>
                                <option value="Tajikistan" <?php echo ($user['country'] === 'Tajikistan') ? 'selected' : ''; ?>>Tajikistan</option>
                                <option value="Tanzania, United Republic of" <?php echo ($user['country'] === 'Tanzania, United Republic of') ? 'selected' : ''; ?>>Tanzania, United Republic of</option>
                                <option value="Thailand" <?php echo ($user['country'] === 'Thailand') ? 'selected' : ''; ?>>Thailand</option>
                                <option value="Timor-leste" <?php echo ($user['country'] === 'Timor-leste') ? 'selected' : ''; ?>>Timor-leste</option>
                                <option value="Togo" <?php echo ($user['country'] === 'Togo') ? 'selected' : ''; ?>>Togo</option>
                                <option value="Tokelau" <?php echo ($user['country'] === 'Tokelau') ? 'selected' : ''; ?>>Tokelau</option>
                                <option value="Tonga" <?php echo ($user['country'] === 'Tonga') ? 'selected' : ''; ?>>Tonga</option>
                                <option value="Trinidad and Tobago" <?php echo ($user['country'] === 'Trinidad and Tobago') ? 'selected' : ''; ?>>Trinidad and Tobago</option>
                                <option value="Tunisia" <?php echo ($user['country'] === 'Tunisia') ? 'selected' : ''; ?>>Tunisia</option>
                                <option value="Turkey" <?php echo ($user['country'] === 'Turkey') ? 'selected' : ''; ?>>Turkey</option>
                                <option value="Turkmenistan" <?php echo ($user['country'] === 'Turkmenistan') ? 'selected' : ''; ?>>Turkmenistan</option>
                                <option value="Turks and Caicos Islands" <?php echo ($user['country'] === 'Turks and Caicos Islands') ? 'selected' : ''; ?>>Turks and Caicos Islands</option>
                                <option value="Tuvalu" <?php echo ($user['country'] === 'Tuvalu') ? 'selected' : ''; ?>>Tuvalu</option>
                                <option value="Uganda" <?php echo ($user['country'] === 'Uganda') ? 'selected' : ''; ?>>Uganda</option>
                                <option value="Ukraine" <?php echo ($user['country'] === 'Ukraine') ? 'selected' : ''; ?>>Ukraine</option>
                                <option value="United Arab Emirates" <?php echo ($user['country'] === 'United Arab Emirates') ? 'selected' : ''; ?>>United Arab Emirates</option>
                                <option value="United Kingdom" <?php echo ($user['country'] === 'United Kingdom') ? 'selected' : ''; ?>>United Kingdom</option>
                                <option value="United States" <?php echo ($user['country'] === 'United States') ? 'selected' : ''; ?>>United States</option>
                                <option value="United States Minor Outlying Islands" <?php echo ($user['country'] === 'United States Minor Outlying Islands') ? 'selected' : ''; ?>>United States Minor Outlying Islands</option>
                                <option value="Uruguay" <?php echo ($user['country'] === 'Uruguay') ? 'selected' : ''; ?>>Uruguay</option>
                                <option value="Uzbekistan" <?php echo ($user['country'] === 'Uzbekistan') ? 'selected' : ''; ?>>Uzbekistan</option>
                                <option value="Vanuatu" <?php echo ($user['country'] === 'Vanuatu') ? 'selected' : ''; ?>>Vanuatu</option>
                                <option value="Venezuela" <?php echo ($user['country'] === 'Venezuela') ? 'selected' : ''; ?>>Venezuela</option>
                                <option value="Viet Nam" <?php echo ($user['country'] === 'Viet Nam') ? 'selected' : ''; ?>>Viet Nam</option>
                                <option value="Virgin Islands, British" <?php echo ($user['country'] === 'Virgin Islands, British') ? 'selected' : ''; ?>>Virgin Islands, British</option>
                                <option value="Virgin Islands, U.S." <?php echo ($user['country'] === 'Virgin Islands, U.S.') ? 'selected' : ''; ?>>Virgin Islands, U.S.</option>
                                <option value="Wallis and Futuna" <?php echo ($user['country'] === 'Wallis and Futuna') ? 'selected' : ''; ?>>Wallis and Futuna</option>
                                <option value="Western Sahara" <?php echo ($user['country'] === 'Western Sahara') ? 'selected' : ''; ?>>Western Sahara</option>
                                <option value="Yemen" <?php echo ($user['country'] === 'Yemen') ? 'selected' : ''; ?>>Yemen</option>
                                <option value="Zambia" <?php echo ($user['country'] === 'Zambia') ? 'selected' : ''; ?>>Zambia</option>
                                <option value="Zimbabwe" <?php echo ($user['country'] === 'Zimbabwe') ? 'selected' : ''; ?>>Zimbabwe</option>
                            </select>
                        </div>

                        <!-- Password fields with toggle -->
                        <div class="form-row">
                            <div class="label">Current Password:</div>
                            <div class="password-wrap">
                                <input id="old-password" name="oldPassword" class="input" type="password"
                                    placeholder="Enter your current password" />
                                <button type="button" class="toggle-password" data-target="old-password"
                                    aria-pressed="false" aria-label="Show current password"></button>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="label">New Password:</div>
                            <div class="password-wrap">
                                <input id="password" name="password" class="input" type="password"
                                    placeholder="Enter new password" />
                                <button type="button" class="toggle-password" data-target="password" aria-pressed="false"
                                    aria-label="Show new password"></button>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="label">Confirm Password:</div>
                            <div class="password-wrap">
                                <input id="confirmPassword" name="confirmPassword" class="input" type="password"
                                    placeholder="Confirm new password" />
                                <button type="button" class="toggle-password" data-target="confirmPassword"
                                    aria-pressed="false" aria-label="Show confirm password"></button>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="label">Bio:</div>
                            <textarea id="bio" name="bio" class="input" placeholder="Tell us about yourself..."
                                maxlength="500"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>

                        <div class="save-wrap">
                            <button type="submit" id="saveBtn" class="btn-save">Save Changes</button>
                        </div>
                    </form>
                </div>
            </main>
            <div id="toast" class="toast" role="status" aria-live="polite" aria-atomic="true"></div>
        </div>
    </main>
    <!-- Search & Results -->
    <section class="search-container" id="searchContainer" style="display: none;">
        <div class="tabs" id="tabs">
            <div class="tab active" data-type="all">All</div>
            <div class="tab" data-type="profiles">Profiles</div>
            <div class="tab" data-type="blogs">Blogs</div>
            <div class="tab" data-type="events">Events</div>
            <div class="tab" data-type="trades">Trades</div>
        </div>
        <div class="results" id="results"></div>
    </section>

    <!-- Scripts -->
    <script>
        const isAdmin = false;
    </script>
    <script src="../../javascript/mainScript.js"></script>
    <script>
        // Profile data from PHP
        const userData = {
            fullName: <?php echo json_encode($user['fullName'] ?? ''); ?>,
            username: <?php echo json_encode($user['username'] ?? ''); ?>,
            email: <?php echo json_encode($user['email'] ?? ''); ?>,
            gender: <?php echo json_encode($user['gender'] ?? ''); ?>,
            bio: <?php echo json_encode($user['bio'] ?? ''); ?>
        };

        const elements = {
            avatarImg: document.getElementById('avatarImg'),
            avatarWrap: document.getElementById('avatar'),
            toast: document.getElementById('toast')
        };

        const AVATAR_COLOR = ' #2fd26b'

        function generateAvatar(name) {
            const initials = name.split(/\s+/).map(s => s[0]).slice(0, 2).join('').toUpperCase();
            const canvas = document.createElement('canvas');
            canvas.width = canvas.height = 300;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = AVATAR_COLOR;
            ctx.fillRect(0, 0, 300, 300);
            ctx.fillStyle = '#FFF';
            ctx.font = 'bold 120px Inter, Arial, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(initials, 150, 160);
            const dataURL = canvas.toDataURL('image/png');
            elements.avatarImg.src = dataURL;
            elements.avatarImg.style.display = "block";
            elements.avatarWrap.style.background = "transparent";
        }

        function showToast(msg, isError = false) {
            elements.toast.textContent = msg;
            elements.toast.classList.remove('error', 'success');
            elements.toast.classList.add(isError ? 'error' : 'success');
            elements.toast.classList.remove('show');
            // Trigger reflow to restart animation
            void elements.toast.offsetWidth;
            elements.toast.classList.add('show');
            setTimeout(() => elements.toast.classList.remove('show'), 4000);
        }

        window.addEventListener('load', () => {
            generateAvatar(userData.fullName);

            <?php if (!empty($message)): ?>
                showToast(<?php echo json_encode($message); ?>, <?php echo $messageType === 'error' ? 'true' : 'false'; ?>);
            <?php endif; ?>
        });

        // Show/hide password logic
        (function() {
            const eyeSVG = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></circle></svg>';
            const eyeOffSVG = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 3l18 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M10.58 10.58A3 3 0 0 0 13.42 13.42" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M2 12s4-7 10-7c2.12 0 4.09.6 5.8 1.64M22 12s-4 7-10 7c-1.6 0-3.09-.36-4.4-.99" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>';

            document.querySelectorAll('.toggle-password').forEach(btn => {
                btn.innerHTML = eyeSVG;
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    if (!input) return;
                    const isHidden = input.type === 'password';
                    input.type = isHidden ? 'text' : 'password';
                    this.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                    this.innerHTML = isHidden ? eyeOffSVG : eyeSVG;
                });
            });
        })();
    </script>
</body>

</html>