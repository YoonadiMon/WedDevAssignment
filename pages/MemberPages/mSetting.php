<?php
    // Start the session
    session_start();
    include("../../php/sessionCheck.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>mSetting Page</title>

    <link rel="stylesheet" href="../../style/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">

    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">

    <style>
        body {
            overflow-y: auto;
        }

        .settings h1 {
            margin-bottom: 4rem;
        }

        /* Scroll to Top Button */
        .scroll-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: var(--MainGreen);
            color: white;
            border: none;
            padding: 12px 16px;
            border-radius: 50%;
            font-size: 24px;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 999;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            transition: all 0.3s ease;
            width: 50px;
            height: 50px;
        }

        .scroll-to-top:hover {
            background-color: var(--btn-color-hover);
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
        }

        .scroll-to-top:active {
            transform: translateY(-1px);
        }

        .scroll-to-top.show {
            display: flex;
        }

        /* Dark mode support */
        .dark-mode .scroll-to-top {
            background-color: var(--MainGreen);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .dark-mode .scroll-to-top:hover {
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3);
        }

        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }

        /* Main content spacing */
        main.settings {
            margin-bottom: 80px;
        }

        /* Responsive scroll button */
        @media (max-width: 768px) {
            .scroll-to-top {
                bottom: 20px;
                right: 20px;
                width: 45px;
                height: 45px;
                padding: 10px 14px;
                font-size: 20px;
            }
        }

        @media (max-width: 480px) {
            .scroll-to-top {
                bottom: 15px;
                right: 15px;
                width: 40px;
                height: 40px;
                padding: 8px 12px;
                font-size: 18px;
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
            <img src="../../assets/images/icon-menu.svg" alt="icon-menu" onclick="showMenu()" class="c-icon-btn"
                id="menuBtn">
            <div id="sidebarNav" class="c-navbar-side-menu">

                <img src="../../assets/images/icon-menu-close.svg" alt="icon-menu-close" onclick="hideMenu()"
                    class="close-btn">
                <div class="c-navbar-side-items">
                    <section class="c-navbar-side-more">
                        <button id="themeToggle1">
                            <img src="../../assets/images/light-mode-icon.svg" alt="Light Mode Icon">
                        </button>

                        <div class="c-chatbox" id="chatboxMobile">
                            <a href="../../pages/MemberPages/mChat.php">
                                <img src="../../assets/images/chat-light.svg" alt="Chatbox">
                            </a>
                            <?php if ($unread_count > 0): ?>
                                <span class="c-notification-badge" id="chatBadgeMobile"></span>
                            <?php endif; ?>
                        </div>
                    </section>

                    <a href="../../pages/MemberPages/memberIndex.php">Home</a>
                    <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
                    <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                    <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                    <a href="../../pages/CommonPages/aboutUs.php">About</a>
                </div>
            </div>
        </nav>

        <!-- Menu Links Desktop + Tablet -->
        <nav class="c-navbar-desktop">
            <a href="../../pages/MemberPages/memberIndex.php">Home</a>
            <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
            <a href="../../pages/CommonPages/mainEvent.php">Event</a>
            <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
            <a href="../../pages/CommonPages/aboutUs.php">About</a>
        </nav>

        <section class="c-navbar-more">
            <button id="themeToggle2">
                <img src="../../assets/images/light-mode-icon.svg" alt="Light Mode Icon">
            </button>
            <a href="../../pages/MemberPages/mChat.php" class="c-chatbox" id="chatboxDesktop">
                <img src="../../assets/images/chat-light.svg" alt="Chatbox" id="chatImg">
                <?php if ($unread_count > 0): ?>
                    <span class="c-notification-badge" id="chatBadgeDesktop"></span>
                <?php endif; ?>
            </a>

            <a href="../../pages/MemberPages/mSetting.php">
                <img src="../../assets/images/setting-light.svg" alt="Settings" id="settingImg">
            </a>
        </section>
    </header>

    <main class="content" id="content">
        <section class="settings">
            <h1>Settings</h1>
            <ul class="settings-list">
                <li data-href="../../pages/MemberPages/mProfile.php">
                    <div class="icon-box">
                        <img src="../../assets/images/edit-profile-light.svg" alt="Edit Profile Picture"
                            class="edit-profile-pic">
                    </div>
                    <span>Edit Profile</span>
                </li>

                <li id="themeToggleLi">
                    <div class="icon-box">
                        <img src="../../assets/images/dark-mode-icon.svg" alt="dark-mode-icon">
                    </div>
                    <span>Dark Mode</span>
                </li>

                <li data-href="../../pages/CommonPages/mainFAQ.php">
                    <div class="icon-box">
                        <img src="../../assets/images/FAQ.png" alt="FAQ"
                            class="privacy-security-icon">
                    </div>
                    <span>FAQ</span>
                </li>

                <li data-href="../../pages/MemberPages/mContactSupport.php">
                    <div class="icon-box">
                        <img src="../../assets/images/help-support-icon-light.svg" alt="Help & Support"
                            class="help-support-icon">
                    </div>
                    <span>Help & Support</span>
                </li>

                <li data-href="../../php/logOut.php" id="logoutBtn">
                    <div class="icon-box">
                        <img src="../../assets/images/logout-icon-light.svg" alt="Log out" class="logout-icon">
                    </div>
                    <span>Log out</span>
                </li>
            </ul>
        </section>
    </main>

    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" id="scrollToTopBtn" title="Go to top" aria-label="Scroll to top">
        ↑
    </button>

    <script>
        const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
        const unreadCount = <?php echo $unread_count; ?>;
    </script>
    <script src="../../javascript/mainScript.js"></script>
    <script src="../../javascript/setting.js"></script>

    <script>
        // Scroll to Top Button Functionality
        const scrollToTopBtn = document.getElementById('scrollToTopBtn');

        // Show button when scrolled down
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.add('show');
            } else {
                scrollToTopBtn.classList.remove('show');
            }
        });

        // Scroll to top when button clicked
        scrollToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Alternative scroll wheel functionality (scroll down with smooth animation)
        let isScrolling = false;

        document.addEventListener('wheel', function(e) {
            // Optional: Add custom scroll handling if needed
            // This allows native scrolling while adding custom behavior
        }, { passive: true });

        // Keyboard navigation for accessibility
        document.addEventListener('keydown', function(e) {
            if (e.code === 'ArrowUp' && scrollToTopBtn.classList.contains('show')) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        });
    </script>
</body>

</html>