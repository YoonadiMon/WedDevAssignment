<?php
    session_start();
    include("../../php/dbConn.php");
    include("../../php/sessionCheck.php");
    include("../../php/errorPopUp.php");

    $indexUrl = $isAdmin ? '../../pages/adminPages/adminIndex.php' : '../../pages/MemberPages/memberIndex.php';

    // Initialize variables
    $currentUserID = $_SESSION['userID'];
    $profileUserID = isset($_GET['userID']) ? intval($_GET['userID']) : 0;
    $hasError = false;
    $userData = [];
    $initials = '';
    $blogsPosted = 0;
    $tradesCompleted = 0;
    $eventsJoined = 0;
    $profileUserIsAdmin = false;

    // Get previous page for redirect
    $previousPage = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $indexUrl;

    // Validate user ID first
    if ($profileUserID <= 0) {
        showErrorPopup("Invalid user profile requested.", $previousPage);
    } else {
        $isViewingOwnProfile = ($profileUserID == $currentUserID);
        if ($isViewingOwnProfile) {
            header("Location: " . $indexUrl);
            exit();
        }
        // Fetch profile user data
        $query = "SELECT fullName, username, bio, point, tradesCompleted, country, userType 
                FROM tblusers 
                WHERE userID = $profileUserID";
        $result = mysqli_query($connection, $query);

        if (!$result) {
            error_log("Database error: " . mysqli_error($connection));
            showErrorPopup("Connection issue. Try again later.", $previousPage);
            exit();
        }
        if (mysqli_num_rows($result) === 0) {
            showErrorPopup("User profile not found.", $previousPage);
            exit();
        }

        $userData = mysqli_fetch_assoc($result);
        $profileUserIsAdmin = ($userData['userType'] === 'admin');

        // Get initials for avatar
        function getInitials($name) {
            $words = explode(' ', trim($name));
            if (count($words) >= 2) {
                return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
            }
            return strtoupper(substr($words[0], 0, 2));
        }
        $initials = getInitials($userData['fullName']);
        $tradesCompleted = $userData['tradesCompleted'] ?? 0; // admin always 0
        $point = $userData['point'] ?? 0; // admin always 0

        if (!$profileUserIsAdmin) {
            // Count events joined
            $eventsJoinedQuery = "
                SELECT COUNT(*) as eventsJoined 
                FROM tblregistration r 
                INNER JOIN tblevents e ON r.eventID = e.eventID 
                WHERE r.userID = $profileUserID 
                AND r.status = 'active' 
                AND e.status != 'cancelled'";

            $eventsJoinedResult = mysqli_query($connection, $eventsJoinedQuery);
            if ($eventsJoinedResult) {
                $eventsJoinedData = mysqli_fetch_assoc($eventsJoinedResult);
                $eventsJoined = $eventsJoinedData['eventsJoined'] ?? 0;
            }

            // Count blogs posted
            $blogsPostedQuery = "SELECT COUNT(*) as blogsPosted FROM tblblog WHERE userID = $profileUserID";
            $blogResult = mysqli_query($connection, $blogsPostedQuery);
            if ($blogResult) {
                $blogData = mysqli_fetch_assoc($blogResult);
                $blogsPosted = $blogData['blogsPosted'] ?? 0;
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($userData['fullName']); ?>'s Profile - ReLeaf</title>
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
    </style>
</head>

<body>
    <div id="cover" class="" onclick="hideMenu()"></div>

    <header>
        <!-- Logo + Navbar -->
        <section class="c-logo-section">
            <a href="../../pages/<?php echo $isAdmin ? 'adminPages/adminIndex.php' : 'MemberPages/memberIndex.php'; ?>" class="c-logo-link">
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

                        <?php if ($isAdmin): ?>
                            <!-- Admin Navigation Icons -->
                            <a href="../../pages/adminPages/aProfile.php">
                                <img src="../../assets/images/profile-light.svg" alt="Profile">
                            </a>
                        <?php else: ?>
                            <!-- Member Navigation Icons -->
                            <div class="c-chatbox" id="chatboxMobile">
                                <a href="../../pages/MemberPages/mChat.php">
                                    <img src="../../assets/images/chat-light.svg" alt="Chatbox">
                                </a>
                                <?php if ($unread_count > 0): ?>
                                    <span class="c-notification-badge" id="chatBadgeMobile"></span>
                                <?php endif; ?>
                            </div>
                            <a href="../../pages/MemberPages/mSetting.php">
                                <img src="../../assets/images/setting-light.svg" alt="Settings">
                            </a>
                        <?php endif; ?>
                    </section>

                    <?php if ($isAdmin): ?>
                        <!-- Admin Menu Items -->
                        <a href="../../pages/adminPages/adminIndex.php">Dashboard</a>
                        <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
                        <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                        <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                        <a href="../../pages/CommonPages/mainFAQ.php">FAQs</a>
                        <a href="../../pages/adminPages/aHelpTicket.php">Help</a>
                    <?php else: ?>
                        <!-- Member Menu Items -->
                        <a href="../../pages/MemberPages/memberIndex.php">Home</a>
                        <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
                        <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                        <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                        <a href="../../pages/CommonPages/aboutUs.php">About</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <!-- Menu Links Desktop + Tablet -->
        <nav class="c-navbar-desktop">
            <?php if ($isAdmin): ?>
                <!-- Admin Desktop Menu -->
                <a href="../../pages/adminPages/adminIndex.php">Dashboard</a>
                <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
                <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                <a href="../../pages/CommonPages/mainFAQ.php">FAQs</a>
                <a href="../../pages/adminPages/aHelpTicket.php">Help</a>
            <?php else: ?>
                <!-- Member Desktop Menu -->
                <a href="../../pages/MemberPages/memberIndex.php">Home</a>
                <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
                <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                <a href="../../pages/CommonPages/aboutUs.php">About</a>
            <?php endif; ?>
        </nav>

        <section class="c-navbar-more">
            <input type="text" placeholder="Search..." id="searchBar" class="search-bar">
            <button id="themeToggle2">
                <img src="../../assets/images/light-mode-icon.svg" alt="Light Mode Icon">
            </button>

            <?php if ($isAdmin): ?>
                <!-- Admin Navbar More -->
                <a href="../../pages/adminPages/aProfile.php">
                    <img src="../../assets/images/profile-light.svg" alt="Profile" id="profileImg">
                </a>
            <?php else: ?>
                <!-- Member Navbar More -->
                <a href="../../pages/MemberPages/mChat.php" class="c-chatbox" id="chatboxDesktop">
                    <img src="../../assets/images/chat-light.svg" alt="Chatbox" id="chatImg">
                    <?php if ($unread_count > 0): ?>
                        <span class="c-notification-badge" id="chatBadgeDesktop"></span>
                    <?php endif; ?>
                </a>
                <a href="../../pages/MemberPages/mSetting.php">
                    <img src="../../assets/images/setting-light.svg" alt="Settings" id="settingImg">
                </a>
            <?php endif; ?>
        </section>
    </header>
    <hr>

    <!-- Main Content -->
    <main class="content" id="content">
        <section class="profile-container content">
            <a href="javascript:history.back()" class="back-button">← Back</a>

            <div class="profile-top">
                <div class="profile-info">
                    <div class="avatar-circle"><?php echo htmlspecialchars($initials); ?></div>
                    <div class="profile-details">
                        <h1 class="profile-name"><?php echo htmlspecialchars($userData['fullName']); ?></h1>
                        <div class="username-country-wrapper">
                            <div class="profile-username-wrapper">
                                <p class="profile-username">@<?php echo htmlspecialchars($userData['username']); ?></p>
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
                <?php if ($profileUserID !== $currentUserID && !$profileUserIsAdmin): ?>
                    <div class="action-buttons">
                        <a href="../../pages/MemberPages/mCreateTicket.php" class="action-btn" title="Report User">
                            <img src="../../assets/images/report-icon-light.svg" alt="Report" class="report-icon">
                        </a>
                        <a href="../../pages/MemberPages/mChat.php?start_chat_with=<?php echo urlencode($profileUserID); ?>"
                            class="action-btn"
                            title="Chat with <?php echo htmlspecialchars($userData['fullName']); ?>">
                            <img src="../../assets/images/chat-light.svg" alt="Chat" class="chat-icon">
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!$profileUserIsAdmin): ?>
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
                        <span class="stat-value"><?php echo number_format($point); ?></span>
                        <span class="stat-label">Points</span>
                    </div>
                </div>
            <?php endif; ?>
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

    <?php if (!$isAdmin): ?>
        <!-- Footer (Member Only) -->
        <hr>
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
                    <a href="../../pages/MemberPages/mChat.php">My Chat</a><br>
                    <a href="../../pages/MemberPages/mSetting.php">Settings</a>
                </div>
                <div>
                    <b>Helps</b><br>
                    <a href="../../pages/CommonPages/aboutUs.php">Contact</a><br>
                    <a href="../../pages/CommonPages/mainFAQ.php">FAQs</a><br>
                    <a href="../../pages/MemberPages/mContactSupport.php">Helps and Support</a>
                </div>
                <div>
                    <b>Community</b><br>
                    <a href="../../pages/CommonPages/mainEvent.php">Events</a><br>
                    <a href="../../pages/CommonPages/mainBlog.php">Blogs</a><br>
                    <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                </div>
            </section>
        </footer>
    <?php endif; ?>

    <script>
        const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
        const unreadCount = <?php echo $unread_count; ?>;
    </script>
    <script src="../../javascript/mainScript.js"></script>
</body>

</html>

<?php
if (isset($connection)) {
    mysqli_close($connection);
}
?>