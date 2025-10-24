<!--  for Common Pages -->
<?php
include("../../php/dbConn.php");
include("../../php/sessionCheck.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReLeaf - Common Pages</title>
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">
    <link rel="stylesheet" href="../../style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
</head>
<body>
    <div id="cover" class="" onclick="hideMenu()"></div>
    
    <!-- Logo + Name & Navbar -->
    <header>
        <!-- Logo + Name -->
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
                                <a href="../../pages/MemberPages/mChat.html">
                                    <img src="../../assets/images/chat-light.svg" alt="Chatbox">
                                </a>
                                <span class="c-notification-badge" id="chatBadgeMobile"></span>
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
                <a href="../../pages/MemberPages/mChat.html" class="c-chatbox" id="chatboxDesktop">
                    <img src="../../assets/images/chat-light.svg" alt="Chatbox" id="chatImg">
                    <span class="c-notification-badge" id="chatBadgeDesktop"></span>
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
        <!-- Content Placeholder -->
        <section>
            <p>Welcome to ReLeaf, <?php echo htmlspecialchars($fullName); ?>!</p>
        </section>
    </main>
    <!-- Search & Results -->
    <section class="search-container" id="searchContainer" style="display: none;">
        <!-- Tabs -->
        <div class="tabs" id="tabs">
            <div class="tab active" data-type="all">All</div>
            <div class="tab" data-type="profiles">Profiles</div>
            <div class="tab" data-type="blogs">Blogs</div>
            <div class="tab" data-type="events">Events</div>
            <div class="tab" data-type="trades">Trades</div>
        </div>

        <!-- Results -->
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
                <a href="../../pages/MemberPages/mChat.html">My Chat</a><br>
                <a href="../../pages/MemberPages/mSetting.php">Settings</a>
            </div>
            <div>
                <b>Helps</b><br>
                <a href="../../pages/CommonPages/aboutUs.php">Contact</a><br>
                <a href="../../pages/CommonPages/mainFAQ.php">FAQs</a><br>
                <a href="../../pages/MemberPages/mSetting.php">Settings</a>
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

    <script>const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;</script>
    <script src="../../javascript/mainScript.js"></script>
</body>
</html>