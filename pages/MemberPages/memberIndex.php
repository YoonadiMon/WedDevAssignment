<?php
session_start();
include("../../php/dbConn.php");
include("../../php/sessionCheck.php");

// Initialize variables
$showWelcomePopup = false;
$userName = '';
$userData = [];
$initials = '';
$blogsPosted = 0;
$tradesCompleted = 0;
$eventsJoined = 0;
$leaderboard = [];
$userRank = 0;

// Check for welcome popup
if (isset($_SESSION['login_success']) && $_SESSION['login_success'] === true) {
    $showWelcomePopup = true;
    $userName = isset($_SESSION['fullName']) ? $_SESSION['fullName'] : $_SESSION['username'];
    unset($_SESSION['login_success']);
}

// Fetch user data
$userID = $_SESSION['userID'];
$query = "SELECT fullName, username, bio, point, tradesCompleted, country FROM tblusers WHERE userID = ?";

if ($stmt = $connection->prepare($query)) {
    $stmt->bind_param("i", $userID);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        
        if ($userData) {
            // Get user initials
            function getInitials($name) {
                $words = explode(' ', trim($name));
                if (count($words) >= 2) {
                    return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                }
                return strtoupper(substr($words[0], 0, 2));
            }
            
            $initials = getInitials($userData['fullName']);
            $tradesCompleted = $userData['tradesCompleted'] ?? 0;
            
            // Get events joined count
            $eventsJoinedQuery = "SELECT COUNT(*) as eventsJoined 
                                FROM tblregistration r 
                                INNER JOIN tblevents e ON r.eventID = e.eventID 
                                WHERE r.userID = ? 
                                AND r.status = 'active' 
                                AND e.endDate < CURDATE() 
                                AND e.status != 'cancelled'";
            
            if ($eventsJoinedStmt = $connection->prepare($eventsJoinedQuery)) {
                $eventsJoinedStmt->bind_param("i", $userID);
                if ($eventsJoinedStmt->execute()) {
                    $eventsJoinedResult = $eventsJoinedStmt->get_result();
                    $eventsJoinedData = $eventsJoinedResult->fetch_assoc();
                    $eventsJoined = $eventsJoinedData['eventsJoined'] ?? 0;
                }
                $eventsJoinedStmt->close();
            }
        }
    }
    $stmt->close();
}

// Get leaderboard data
$leaderboardQuery = "SELECT userID, fullName, username, point FROM tblusers WHERE userType = 'member' ORDER BY point DESC LIMIT 5";
if ($leaderboardResult = $connection->query($leaderboardQuery)) {
    $rank = 1;
    while ($row = $leaderboardResult->fetch_assoc()) {
        $leaderboard[] = $row;
        if ($row['userID'] == $userID) {
            $userRank = $rank;
        }
        $rank++;
    }
}

// If user is not in top 5, get their rank
if ($userRank == 0 && isset($userData['point'])) {
    $rankQuery = "SELECT COUNT(*) + 1 as rank FROM tblusers WHERE point > ? AND userType = 'member'";
    if ($rankStmt = $connection->prepare($rankQuery)) {
        $rankStmt->bind_param("i", $userData['point']);
        if ($rankStmt->execute()) {
            $rankResult = $rankStmt->get_result();
            $rankData = $rankResult->fetch_assoc();
            $userRank = $rankData['rank'] ?? 0;
        }
        $rankStmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - ReLeaf</title>
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

        .edit-icon {
            content: url('../../assets/images/edit-icon-light.svg');
        }

        .dark-mode .edit-icon {
            content: url('../../assets/images/edit-icon-dark.svg');
        }

        .log-out-icon {
            content: url('../../assets/images/log-out-icon-light.svg');
        }

        .dark-mode .log-out-icon {
            content: url('../../assets/images/log-out-icon-dark.svg');
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

        .leaderboard {
            background: var(--bg-color);
            border: 1px solid var(--Gray);
            border-radius: 16px;
            padding: 2rem;
            margin-top: 2rem;
        }

        .leaderboard-title {
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

        .leaderboard-link {
            text-decoration: none;
            display: block;
            color: inherit;
            border-bottom: 1px solid var(--text-color);
        }

        .leaderboard-link:hover .leaderboard-item {
            background: var(--LightGreen);
            border-color: var(--MainGreen);
            transform: scale(1.02);
            transition: all 0.3s ease;
        }

        .dark-mode .leaderboard-link:hover .leaderboard-item {
            background: var(--LowGreen);
        }

        .leaderboard-link:hover .user-username {
            color: var(--MainGreen);
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
            background: var(--DarkerGray);
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

        .floating-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            background: var(--MainGreen);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .floating-btn img {
            width: 28px;
            height: 28px;
        }

        .floating-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 12px var(--MainGreen);
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

            .username-country-wrapper {
                justify-content: center;
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

            .username-country-wrapper {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php if ($showWelcomePopup): ?>
    <!-- Welcome Popup -->
    <div class="welcome-overlay" id="welcomeOverlay">
        <div class="welcome-popup">
            <h2 class="welcome-title">Welcome Back!</h2>
            <p class="welcome-message">
                Hello, <span class="welcome-username"><?php echo htmlspecialchars($userName); ?></span><br>
                Great to see you again! 🌱
            </p>
            <button class="welcome-close-btn" onclick="closeWelcomePopup()">
                Get Started
            </button>
        </div>
    </div>

    <script>
        // Show welcome popup on page load
        window.addEventListener('DOMContentLoaded', function() {
            const welcomeOverlay = document.getElementById('welcomeOverlay');
            if (welcomeOverlay) {
                setTimeout(() => {
                    welcomeOverlay.classList.add('show');
                }, 100);
            }
        });

        function closeWelcomePopup() {
            const welcomeOverlay = document.getElementById('welcomeOverlay');
            if (welcomeOverlay) {
                welcomeOverlay.classList.remove('show');
                setTimeout(() => {
                    welcomeOverlay.style.display = 'none';
                }, 300);
            }
        }

        document.addEventListener('click', function(e) {
            const welcomeOverlay = document.getElementById('welcomeOverlay');
            if (e.target === welcomeOverlay) {
                closeWelcomePopup();
            }
        });
    </script>
    <?php endif; ?>
    
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
                    <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
                    <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                    <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                    <a href="../../pages/CommonPages/aboutUs.html">About</a>
                </div>
            </div>
        </nav>

        <!-- Menu Links Desktop + Tablet -->
        <nav class="c-navbar-desktop">
            <a href="../../pages/MemberPages/memberIndex.php">Home</a>
            <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
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
    <main class="content" id="content">
        <section class="profile-container">
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
                    <a href="../../pages/MemberPages/mProfile.php" class="action-btn">
                        <img src="../../assets/images/edit-icon-light.svg" alt="Edit Profile" class="edit-icon">
                    </a>
                    <a href="../../php/logOut.php" class="action-btn">
                        <img src="../../assets/images/log-out-icon-light.svg" alt="Log Out" class="log-out-icon">
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
            
            <!-- Leaderboard -->
            <section class="leaderboard">
                <h2 class="leaderboard-title">Leaderboard</h2>
                <ul class="leaderboard-list">
                    <?php foreach ($leaderboard as $index => $user): ?>
                        <a href="../../pages/CommonPages/viewProfile.php?userID=<?php echo $user['userID']; ?>" class="leaderboard-link">
                            <li class="leaderboard-item <?php echo ($user['userID'] == $userID) ? 'current-user' : ''; ?>">
                                <span class="rank-number"><?php echo $index + 1; ?></span>
                                <div class="user-avatar-small">
                                    <?php echo htmlspecialchars(getInitials($user['fullName'])); ?>
                                </div>
                                <div class="user-info">
                                    <div class="user-fullname"><?php echo htmlspecialchars($user['fullName']); ?></div>
                                    <div class="user-username">@<?php echo htmlspecialchars($user['username']); ?></div>
                                </div>
                                <span class="user-points"><?php echo number_format($user['point']); ?> Points</span>
                            </li>
                        </a>
                    <?php endforeach; ?>
                    
                    <?php if ($userRank > 5): ?>
                        <a href="../../pages/CommonPages/viewProfile.php?userID=<?php echo $userID; ?>" class="leaderboard-link">
                            <li class="leaderboard-item current-user" style="margin-top: 1rem; border-top: 2px solid var(--MainGreen);">
                                <span class="rank-number"><?php echo $userRank; ?></span>
                                <div class="user-avatar-small">
                                    <?php echo htmlspecialchars($initials); ?>
                                </div>
                                <div class="user-info">
                                    <div class="user-fullname"><?php echo htmlspecialchars($userData['fullName']); ?></div>
                                    <div class="user-username">@<?php echo htmlspecialchars($userData['username']); ?></div>
                                </div>
                                <span class="user-points"><?php echo number_format($userData['point']); ?> Points</span>
                            </li>
                        </a>
                    <?php endif; ?>
                </ul>
            </section>

            <a href="../../pages/MemberPages/mQuiz.php" class="floating-btn" title="Take a Quiz">
                <img src="../../assets/images/quiz-icon-dark.svg" alt="Quiz">
            </a>
        </section>
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
                <a href="../../pages/MemberPages/mProfile.php">My Account</a><br>
                <a href="../../pages/MemberPages/mChat.html">My Chat</a><br>
                <a href="../../pages/MemberPages/mSetting.html">Settings</a>
            </div>
            <div>
                <b>Helps</b><br>
                <a href="../../pages/CommonPages/aboutUs.html">Contact</a><br>
                <a href="../../pages/CommonPages/mainFAQ.php">FAQs</a><br>
                <a href="../../pages/MemberPages/mSetting.html">Settings</a>
            </div>
            <div>
                <b>Community</b><br>
                <a href="../../pages/CommonPages/mainEvent.php">Events</a><br>
                <a href="../../pages/CommonPages/mainBlog.php">Blogs</a><br>
                <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
            </div>
        </section>
    </footer>

    <script>const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;</script>
    <script src="../../javascript/mainScript.js"></script>
</body>
</html>
