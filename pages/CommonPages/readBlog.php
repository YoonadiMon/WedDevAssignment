<?php
session_start();
include("../../php/dbConn.php");
include("../../php/sessionCheck.php");

// Get blog ID from URL
$blogID = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$blogID) {
    header("Location: mainBlog.php");
    exit;
}

// Fetch blog details
$query = "
    SELECT b.blogID, b.userID, b.title, b.excerpt, b.category, b.content, b.date, u.fullName
    FROM tblblog b
    JOIN tblusers u ON b.userID = u.userID
    WHERE b.blogID = $blogID
";

$result = mysqli_query($connection, $query);
$blog = mysqli_fetch_assoc($result);

if (!$blog) {
    header("Location: mainBlog.php");
    exit;
}

// Fetch tags for this blog
$tagsQuery = "
    SELECT t.tagName
    FROM tbltag t
    JOIN tblblogtag bt ON t.tagID = bt.tagID
    WHERE bt.blogID = $blogID
";

$tagsResult = mysqli_query($connection, $tagsQuery);
$tags = [];

while ($row = mysqli_fetch_assoc($tagsResult)) {
    $tags[] = $row['tagName'];
}

// Fetch next blog (for navigation) - Fixed query
$nextQuery = "
    SELECT blogID, title
    FROM tblblog
    WHERE (date > '{$blog['date']}' OR (date = '{$blog['date']}' AND blogID > $blogID))
    ORDER BY date ASC, blogID ASC
    LIMIT 1
";

$nextResult = mysqli_query($connection, $nextQuery);
$nextBlog = mysqli_fetch_assoc($nextResult);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($blog['title']); ?> - ReLeaf</title>
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png" />
    <link rel="stylesheet" href="../../style/style.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet" />
    <style>
        /* Read Blog Page Styling */
        main {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .read-blog-header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .blog-meta {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .blog-category {
            background-color: var(--MainGreen);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .blog-date {
            color: var(--text-color-2);
            font-size: 1rem;
            font-weight: 500;
        }

        .blog-author {
            color: var(--text-color-2);
            font-size: 1rem;
            font-weight: 500;
        }

        .blog-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-heading);
            line-height: 1.2;
        }

        .blog-excerpt {
            font-size: 1.125rem;
            color: var(--text-color-2);
            line-height: 1.6;
        }

        .blog-image-container {
            margin: 2rem 0;
            text-align: center;
        }

        .blog-image {
            width: 100%;
            max-width: 700px;
            height: 400px;
            background: linear-gradient(135deg, var(--LightGreen), var(--MainGreen));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0 auto;
            box-shadow: 0 8px 32px var(--shadow-color);
        }

        .blog-content {
            font-size: 1.125rem;
            line-height: 1.8;
            color: var(--text-color);
            margin: 2rem 0;
        }

        .blog-content h2 {
            font-size: 1.875rem;
            font-weight: 700;
            margin: 2rem 0 1rem;
            color: var(--text-heading);
        }

        .blog-content h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 1.5rem 0 0.75rem;
            color: var(--text-heading);
        }

        .blog-content p {
            margin-bottom: 1.5rem;
        }

        .blog-content ul,
        .blog-content ol {
            margin: 1rem 0 1.5rem 2rem;
        }

        .blog-content li {
            margin-bottom: 0.5rem;
        }

        .blog-content blockquote {
            background-color: var(--bg-color-light-3);
            border-left: 4px solid var(--MainGreen);
            padding: 1.5rem;
            margin: 2rem 0;
            border-radius: 0 8px 8px 0;
            font-style: italic;
            color: var(--text-color-2);
        }

        .blog-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin: 2rem 0;
        }

        .tag {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: var(--bg-color-light-3);
            color: var(--MainGreen);
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .blog-navigation {
            margin: 3rem 0;
            display: flex;
            justify-content: flex-end;
        }

        .nav-btn {
            padding: 1rem;
            background-color: var(--bg-color-light);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            color: var(--text-color);
            transition: all 0.3s ease;
            width: fit-content;
        }

        .nav-btn:hover {
            background-color: var(--MainGreen);
            color: white;
            transform: translateY(-2px);
        }

        .nav-btn-title {
            font-size: 0.875rem;
            color: var(--text-color-2);
            margin-bottom: 0.5rem;
        }

        .nav-btn:hover .nav-btn-title {
            color: rgba(255, 255, 255, 0.8);
        }

        .nav-btn-text {
            font-weight: 600;
        }

        .back-to-blogs {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            background-color: transparent;
            text-decoration: none;
            margin-right: 1rem;
            font-size: 1.1rem;
        }

        .back-to-blogs img {
            width: 35px;
            height: 35px;
            transition: all 0.3s ease;
        }

        .back-to-blogs:hover {
            background-color: var(--bg-color-light-3);
        }

        .back-to-blogs:hover img {
            opacity: 1;
            transform: translateX(-2px);
        }

        .c-logo-section {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background-color: var(--bg-color);
            gap: 1rem;
        }

        .dark-mode .back-to-blogs:hover {
            background-color: var(--bg-color-dark-3);
        }

        .dark-mode .nav-btn {
            background-color: var(--bg-color-dark);
        }

        .dark-mode .blog-image {
            background: linear-gradient(135deg, var(--MainGreen), var(--btn-color-hover));
        }

        .dark-mode .blog-content blockquote {
            background-color: var(--bg-color-dark-4);
        }

        @media (max-width: 768px) {
            main {
                padding: 1rem;
            }

            .blog-title {
                font-size: 2rem;
            }

            .blog-meta {
                flex-direction: column;
                gap: 0.5rem;
            }

            .blog-image {
                height: 250px;
                font-size: 1.25rem;
            }

            .blog-content {
                font-size: 1rem;
            }

            .blog-content h2 {
                font-size: 1.5rem;
            }

            .blog-content h3 {
                font-size: 1.25rem;
            }

            .blog-navigation {
                justify-content: center;
            }

            .nav-btn {
                flex: 1;
            }
        }

        @media (max-width: 480px) {
            .blog-title {
                font-size: 1.75rem;
            }

            .blog-tags {
                gap: 0.5rem;
            }

            .tag {
                font-size: 0.75rem;
                padding: 0.375rem 0.75rem;
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
            <a href="mainBlog.php" class="back-to-blogs" title="Back to Blogs">
                <img src="../../assets/images/icon-back-light.svg" alt="Back" />
            </a>
            <img src="../../assets/images/Logo.png" alt="Logo" class="c-logo" />
            <div class="c-text">ReLeaf</div>
        </section>

        <!-- Menu Links Mobile -->
        <nav class="c-navbar-side">
            <img src="../../assets/images/icon-menu.svg" alt="icon-menu" onclick="showMenu()" class="c-icon-btn" id="menuBtn">
            <div id="sidebarNav" class="c-navbar-side-menu">
                <img src="../../assets/images/icon-menu-close.svg" alt="icon-menu-close" onclick="hideMenu()" class="close-btn">
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
                        <a href="../../pages/MemberPages/mSetting.php">
                            <img src="../../assets/images/setting-light.svg" alt="Settings">
                        </a>
                    </section>
                    <a href="../../pages/MemberPages/memberIndex.php">Home</a>
                    <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
                    <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                    <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                    <a href="../../pages/CommonPages/aboutUs.php">About</a>
                </div>
            </div>
        </nav>

        <!-- Menu Links Desktop -->
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

    <hr />

    <!-- Main Content -->
    <main class="content" id="content">
        <!-- Blog Header -->
        <article class="read-blog-header">
            <div class="blog-meta">
                <span class="blog-category"><?php echo htmlspecialchars($blog['category']); ?></span>
                <span class="blog-date"><?php echo date('M d, Y', strtotime($blog['date'])); ?></span>
                <span class="blog-author">By <?php echo htmlspecialchars($blog['fullName']); ?></span>
            </div>
            <h1 class="blog-title"><?php echo htmlspecialchars($blog['title']); ?></h1>
            <p class="blog-excerpt"><?php echo htmlspecialchars($blog['excerpt']); ?></p>
        </article>

        <!-- Blog Image -->
        <div class="blog-image-container">
            <div class="blog-image">📰 Blog Content</div>
        </div>

        <!-- Blog Content -->
        <div class="blog-content">
            <?php echo nl2br(htmlspecialchars($blog['content'])); ?>
        </div>

        <!-- Blog Tags -->
        <?php if (!empty($tags)): ?>
            <div class="blog-tags">
                <?php foreach ($tags as $tag): ?>
                    <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Navigation - Only Next Blog -->
        <div class="blog-navigation">
            <?php if ($nextBlog): ?>
                <a href="readBlog.php?id=<?php echo $nextBlog['blogID']; ?>" class="nav-btn">
                    <div class="nav-btn-title">Next</div>
                    <div class="nav-btn-text"><?php echo htmlspecialchars($nextBlog['title']); ?></div>
                </a>
            <?php endif; ?>
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

    <!-- Footer -->
    <footer>
        <section class="c-footer-info-section">
            <img src="../../assets/images/Logo.png" alt="Logo" class="c-logo" />
            <div class="c-text">ReLeaf</div>
            <div class="c-text c-text-center">
                "Relief for the Planet, One Leaf at a Time."<br />
                "Together, We Can ReLeaf the Earth."
            </div>
            <div class="c-text c-text-label">+60 12 345 6789</div>
            <div class="c-text">abc@gmail.com</div>
        </section>

        <section class="c-footer-links-section">
            <div>
                <b>My Account</b><br />
                <a href="../../pages/MemberPages/mProfile.php">My Account</a><br />
                <a href="../../pages/MemberPages/mChat.php">My Chat</a><br />
                <a href="../../pages/MemberPages/mSetting.php">Settings</a>
            </div>
            <div>
                <b>Helps</b><br />
                <a href="../../pages/CommonPages/aboutUs.php">Contact</a><br />
                <a href="../../pages/CommonPages/mainFAQ.php">FAQs</a><br />
                <a href="../../pages/MemberPages/mContactSupport.php">Helps and Support</a>
            </div>
            <div>
                <b>Community</b><br />
                <a href="../../pages/CommonPages/mainEvent.php">Events</a><br />
                <a href="../../pages/CommonPages/mainBlog.php">Blogs</a><br />
                <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
            </div>
        </section>
    </footer>

    <script>
        const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
        const unreadCount = <?php echo $unread_count; ?>;
    </script>
    <script src="../../javascript/mainScript.js"></script>
</body>
</html>