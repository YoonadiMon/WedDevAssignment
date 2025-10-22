<?php
session_start();
include("../../php/dbConn.php");
include("../../php/sessionCheck.php");

// Get the profile user ID from URL parameter
$currentUserID = $_SESSION['userID'];
$indexUrl = $isAdmin ? '../../pages/adminPages/adminIndex.php' : '../../pages/MemberPages/memberIndex.php';
$previousPage = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $indexUrl;

$profileUserID = isset($_GET['userID']) ? intval($_GET['userID']) : 0;
$hasError = false;

// Fetch profile user data
$query = "SELECT fullName, username, bio, point, tradesCompleted, country, userType FROM tblusers WHERE userID = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $profileUserID);
$stmt->execute();
$result = $stmt->get_result();

if ($profileUserID === 0) {
    $_SESSION['profile_error'] = "Invalid user profile requested.";
    $hasError = true;
} else if ($result->num_rows === 0) {
    $_SESSION['profile_error'] = "User profile not found.";
    $hasError = true;
} else {
    $userData = $result->fetch_assoc();

    if ($userData['userType'] === 'admin') {
        $_SESSION['profile_error'] = "This is an admin account.";
        $hasError = true;
    } else {
        function getInitials($name) {
            $words = explode(' ', trim($name));
            if (count($words) >= 2) {
                return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
            }
            return strtoupper(substr($words[0], 0, 2));
        }

        $initials = getInitials($userData['fullName']);

        $blogsPosted = 0; //placeholder
        $tradesCompleted = $userData['tradesCompleted'] ?? 0;

        // Get events joined count
        $eventsJoinedQuery = "SELECT COUNT(*) as eventsJoined 
                            FROM tblregistration r 
                            INNER JOIN tblevents e ON r.eventID = e.eventID 
                            WHERE r.userID = ? 
                            AND r.status = 'active' 
                            AND e.endDate < CURDATE() 
                            AND e.status != 'cancelled'";
        $eventsJoinedStmt = $connection->prepare($eventsJoinedQuery);
        $eventsJoinedStmt->bind_param("i", $profileUserID);
        $eventsJoinedStmt->execute();
        $eventsJoinedResult = $eventsJoinedStmt->get_result();
        $eventsJoinedData = $eventsJoinedResult->fetch_assoc();
        $eventsJoined = $eventsJoinedData['eventsJoined'] ?? 0;
    }  
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReLeaf - <?php echo htmlspecialchars($userData['fullName']); ?>'s Profile</title>
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">
    <link rel="stylesheet" href="../../style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .profile-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .profile-info {
            display: flex;
            align-items: flex-start;
            gap: 1.5rem;
            flex: 1;
        }

        .avatar-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: var(--MainGreen);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--White);
            flex-shrink: 0;
            margin-top: 0.5rem;
        }

        .profile-details {
            flex: 1;
            min-width: 0;
        }

        .profile-name {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 0.5rem;
            word-wrap: break-word;
        }

        .username-country-wrapper {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .profile-username-wrapper {
            display: inline-flex;
            align-items: center;
            background: var(--sec-bg-color);
            border-radius: 8px;
            padding: 0.5rem 1rem;
        }

        .profile-username {
            font-size: 0.9rem;
            color: var(--DarkerGray);
        }

        .profile-country-wrapper {
            display: inline-flex;
            align-items: center;
            margin-left: 12px;
            gap: 0.25rem;
        }

        .profile-country {
            padding-top: 0.125rem;
            font-size: 0.95rem;
            color: var(--DarkerGray);
        }

        .dark-mode .profile-country {
            color: var(--Gray);
        }

        .profile-country-wrapper img {
            content: url('../../assets/images/location-icon-light.svg');
            width: 16px;
            height: 16px;
            vertical-align: middle;
        }

        .dark-mode .profile-country-wrapper img {
            content: url('../../assets/images/location-icon-dark.svg');
        }

        .profile-bio-wrapper {
            display: flex;
            width: 100%;
            max-width: 700px;
            overflow-wrap: break-word;
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
        }

        .profile-bio {
            font-size: 0.95rem;
            color: var(--text-color);
            line-height: 1.5;
            margin-top: 0.5rem;
            overflow-wrap: break-word;
            word-break: break-word;
            white-space: normal;
        }

        .action-buttons {
            display: flex;
            gap: 0.75rem;
            flex-shrink: 0;
        }

        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.75rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            text-decoration: none;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            background: var(--sec-bg-color);
        }

        .action-btn img {
            width: 24px;
            height: 24px;
        }

        .report-icon {
            content: url('../../assets/images/report-icon-light.svg');
        }

        .dark-mode .report-icon {
            content: url('../../assets/images/report-icon-dark.svg');
        }

        .chat-icon {
            content: url('../../assets/images/chat-light.svg');
        }

        .dark-mode .chat-icon {
            content: url('../../assets/images/chat-dark.svg');
        }

        .stats-bar {
            border: 1px solid var(--MainGreen);
            border-radius: 16px;
            display: flex;
            justify-content: space-around;
            gap: 1rem;
            padding: 1.5rem;
            background: var(--LowGreen);
        }

        .stat-item {
            text-align: center;
            flex: 1;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--White);
            display: block;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--DarkerGray);
            margin-top: 0.25rem;
            display: block;
        }

        .dark-mode .stat-label {
            color: var(--Gray);
        }

        .leaderboard-section {
            background: var(--bg-color);
            border: 1px solid var(--Gray);
            border-radius: 16px;
            padding: 2rem;
            margin-top: 2rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .leaderboard-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .leaderboard-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--Gray);
            transition: all 0.2s ease;
        }

        .leaderboard-item:last-child {
            border-bottom: none;
        }

        .leaderboard-item.current-user {
            background: var(--LightGreen);
            font-weight: 600;
        }

        .dark-mode .leaderboard-item.current-user {
            background: var(--LowGreen);
        }

        .rank-number {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-color);
            width: 40px;
            text-align: center;
        }

        .user-avatar-small {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--Gray);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--White);
            margin: 0 1rem;
            flex-shrink: 0;
        }

        .leaderboard-item.current-user .user-avatar-small {
            background: var(--MainGreen);
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-fullname {
            font-size: 1rem;
            color: var(--text-color);
            font-weight: 600;
            word-wrap: break-word;
        }

        .user-username {
            font-size: 0.875rem;
            color: var(--Gray);
        }

        .user-points {
            font-size: 1rem;
            font-weight: 600;
            color: var(--MainGreen);
            flex-shrink: 0;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .back-button:hover {
            color: var(--MainGreen);
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .profile-top {
                flex-direction: column;
                gap: 1.5rem;
                align-items: center;
            }

            .profile-info {
                flex-direction: column;
                text-align: center;
                align-items: center;
                gap: 1rem;
            }

            .profile-bio-wrapper {
                width: 100%;
                max-width: 100%;
                text-align: center;
                padding: 0 1rem;
            }

            .stats-bar {
                flex-direction: column;
                gap: 1.5rem;
            }

            .avatar-circle {
                width: 80px;
                height: 80px;
                font-size: 2rem;
                margin-top: 0;
            }

            .profile-name {
                font-size: 1.5rem;
            }

            .profile-details {
                text-align: center;
            }

            .action-buttons {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .profile-header {
                padding: 1.5rem 1rem;
            }
            
            .profile-top {
                gap: 1rem;
            }
            
            .avatar-circle {
                width: 70px;
                height: 70px;
                font-size: 1.75rem;
            }
            
            .profile-name {
                font-size: 1.25rem;
            }
        }

        /* Error Popup Modal */
        .error-popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.85);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        .error-popup-overlay.show {
            display: flex;
        }

        .error-popup {
            background: var(--bg-color);
            border-radius: 16px;
            padding: 2.5rem;
            max-width: 450px;
            width: 90%;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.4s ease;
            border: 2px solid var(--Red);
        }

        .dark-mode .error-popup {
            border-color: var(--Red);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-popup-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 1.25rem;
        }

        .error-popup-icon img {
            width: 100%;
            height: 100%;
        }

        .error-popup-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--Red);
            margin-bottom: 1rem;
        }

        .error-popup-message {
            font-size: 1.05rem;
            color: var(--text-color);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .error-popup-btn {
            padding: 0.875rem 2.5rem;
            background: var(--Red);
            color: var(--White);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .error-popup-btn:hover {
            opacity: 0.8;
            transform: translateY(-2px);
        }

        @media (max-width: 480px) {
            .error-popup {
                padding: 2rem 1.5rem;
            }
            .error-popup-title {
                font-size: 1.5rem;
            }
            .error-popup-icon {
                width: 60px;
                height: 60px;
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
            <a href="../../pages/MemberPages/memberIndex.php" class="c-logo-link">
                <img src="../../assets/images/Logo.png" alt="Logo" class="c-logo">
                <div class="c-text">ReLeaf</div>
            </a>
        </section>

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
                        <div class="c-chatbox" id="chatboxMobile">
                            <a href="../../pages/MemberPages/mChat.html">
                                <img src="../../assets/images/chat-light.svg" alt="Chatbox">
                            </a>
                            <span class="c-notification-badge" id="chatBadgeMobile"></span>
                        </div>
                        <a href="../../pages/MemberPages/mSetting.html">
                            <img src="../../assets/images/setting-light.svg" alt="Settings">
                        </a>
                    </section>
                    <a href="../../pages/MemberPages/memberIndex.php">Home</a>
                    <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
                    <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                    <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                    <a href="../../pages/CommonPages/aboutUs.html">About</a>
                </div>
            </div>
        </nav>

        <!-- Menu Links Desktop + Tablet -->
        <nav class="c-navbar-desktop">
            <a href="../../pages/MemberPages/memberIndex.php">Home</a>
            <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
            <a href="../../pages/CommonPages/mainEvent.php">Event</a>
            <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
            <a href="../../pages/CommonPages/aboutUs.html">About</a>
        </nav>

        <section class="c-navbar-more">
            <input type="text" placeholder="Search..." id="searchBar" class="search-bar">
            <button id="themeToggle2">
                <img src="../../assets/images/light-mode-icon.svg" alt="Light Mode Icon">
            </button>
            <a href="../../pages/MemberPages/mChat.html" class="c-chatbox" id="chatboxDesktop">
                <img src="../../assets/images/chat-light.svg" alt="Chatbox" id="chatImg">
                <span class="c-notification-badge" id="chatBadgeDesktop"></span>
            </a>
            <a href="../../pages/MemberPages/mSetting.html">
                <img src="../../assets/images/setting-light.svg" alt="Settings" id="settingImg">
            </a>
        </section>
    </header>
    <hr>
    
    <!-- Main Content -->
    <main class="content">
        <!-- Error Popup -->
        <?php if (isset($_SESSION['profile_error'])): ?>
        <div class="error-popup-overlay show" id="errorPopup">
            <div class="error-popup">
                <div class="error-popup-icon">
                    <img src="../../assets/images/banned-icon-red.svg" alt="Error">
                </div>
                <h2 class="error-popup-title">Error</h2>
                <p class="error-popup-message">
                    <?php 
                        echo htmlspecialchars($_SESSION['profile_error']); 
                        unset($_SESSION['profile_error']);
                    ?>
                </p>
                <button class="error-popup-btn" onclick="closeErrorPopup()">OK</button>
            </div>
        </div>
        <?php endif; ?>
        <section class="profile-container" style="<?php echo $hasError ? 'display: none;' : ''; ?>">
            <!-- Back Button -->
            <a href="javascript:history.back()" class="back-button">
                ← Back
            </a>

            <!-- Profile Header -->
            <div class="profile-top">
                <div class="profile-info">
                    <div class="avatar-circle"><?php echo htmlspecialchars($initials); ?></div>
                    <div class="profile-details">
                        <h1 class="profile-name"><?php echo htmlspecialchars($userData['fullName']); ?></h1>
                        <div class="username-country-wrapper">
                            <div class="profile-username-wrapper">
                                <p class="profile-username">
                                    @<?php echo htmlspecialchars($userData['username']); ?>
                                </p>
                            </div>
                            <div class="profile-country-wrapper">
                                <img src="../../assets/images/location-icon-light.svg" alt="Location">
                                <p class="profile-country"><?php echo htmlspecialchars($userData['country']); ?></p>
                            </div>
                        </div>
                        <div class="profile-bio-wrapper">
                            <p class="profile-bio">
                            <?php 
                            echo $userData['bio'] 
                                ? htmlspecialchars($userData['bio']) 
                                : '<span style="color: var(--Gray);">This user has yet to set their bio</span>';
                            ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="../../pages/MemberPages/mCreateTicket.php" 
                       class="action-btn" 
                       title="Report User">
                        <img src="../../assets/images/report-icon-light.svg" alt="Report" class="report-icon">
                    </a>
                    <a href="../../pages/MemberPages/mChat.html" 
                       class="action-btn" 
                       title="Chat">
                        <img src="../../assets/images/chat-light.svg" alt="Chat" class="chat-icon">
                    </a>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="stats-bar">
                <div class="stat-item">
                    <span class="stat-value"><?php echo $blogsPosted; ?></span>
                    <span class="stat-label">Blogs Posted</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?php echo $tradesCompleted; ?></span>
                    <span class="stat-label">Trades Completed</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?php echo $eventsJoined; ?></span>
                    <span class="stat-label">Events Joined</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?php echo number_format($userData['point']); ?></span>
                    <span class="stat-label">Points</span>
                </div>
            </div>
        </section>

        <!-- Search & Results -->
        <section class="search-container" id="searchContainer" style="display: none;">
            <!-- Tabs -->
            <div class="tabs" id="tabs">
                <div class="tab active" data-type="all">All</div>
                <?php if ($isAdmin): ?>
                    <div class="tab" data-type="tickets">Tickets</div>
                <?php endif; ?>
                <div class="tab" data-type="profiles">Profiles</div>
                <div class="tab" data-type="blogs">Blogs</div>
                <div class="tab" data-type="events">Events</div>
                <div class="tab" data-type="trades">Trades</div>
                <?php if ($isAdmin): ?>
                    <div class="tab" data-type="faqs">FAQ</div>
                <?php endif; ?>
            </div>

            <!-- Results -->
            <div class="results" id="results"></div>
        </section>
    </main>

    <hr>
    
    <!-- Footer -->
    <footer>
        <section class="c-footer-info-section">
            <img src="../../assets/images/Logo.png" alt="Logo" class="c-logo">
            <div class="c-text">ReLeaf</div>
            <div class="c-text c-text-center">
                "Relief for the Planet, One Leaf at a Time."
                <br>
                "Together, We Can ReLeaf the Earth."
            </div>
            <div class="c-text c-text-label">
                +60 12 345 6789
            </div>
            <div class="c-text">
                abc@gmail.com
            </div>
        </section>
        
        <section class="c-footer-links-section">
            <div>
                <b>My Account</b><br>
                <a href="../../pages/MemberPages/mProfile.html">My Account</a><br>
                <a href="../../pages/MemberPages/mChat.html">My Chat</a><br>
                <a href="../../pages/MemberPages/mSetting.html">Settings</a>
            </div>
            <div>
                <b>Helps</b><br>
                <a href="../../pages/CommonPages/aboutUs.html">Contact</a><br>
                <a href="../../pages/CommonPages/mainFAQ.html">FAQs</a><br>
                <a href="../../pages/MemberPages/mSetting.html">Settings</a>
            </div>
            <div>
                <b>Community</b><br>
                <a href="../../pages/CommonPages/mainEvent.php">Events</a><br>
                <a href="../../pages/CommonPages/mainBlog.html">Blogs</a><br>
                <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
            </div>
        </section>
    </footer>

    <script>const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;</script>
    <script src="../../javascript/mainScript.js"></script>
    <script>
        const redirectUrl = <?php echo json_encode($previousPage); ?>;

        function redirectBack() {
            if (redirectUrl.includes('http')) {
                window.location.href = redirectUrl;
            } else {
                window.location.href = '../../' + redirectUrl;
            }
        }

        function closeErrorPopup() {
            const popup = document.getElementById('errorPopup');
            if (popup) {
                popup.classList.remove('show');
                setTimeout(() => {
                    redirectBack();
                }, 200);
            }
        }

        // Auto-redirect after 3 seconds
        setTimeout(() => {
            if (document.getElementById('errorPopup')) {
                redirectBack();
            }
        }, 3000);
    </script>
</body>
</html>
<?php
// Close database connection
if (isset($connection)) {
    mysqli_close($connection);
}
?>