<?php
session_start();
include("../../php/dbConn.php");

$user_type = '';
$user_id = '';

// // Force reset for debugging
unset($_SESSION['admin_id']);
unset($_SESSION['user_id']);
unset($_SESSION['user_type']);

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    $user_type = 'admin';
    $user_id = 1;
    $_SESSION['admin_id'] = $user_id;

    // $user_type = 'member';
    // $user_id = 4;
    // $_SESSION['user_id'] = $user_id;
} elseif (isset($_SESSION['admin_id'])) {
    $user_type = 'admin';
    $user_id = $_SESSION['admin_id'];
} elseif (isset($_SESSION['user_id'])) {
    $user_type = 'member';
    $user_id = $_SESSION['user_id'];
}

// Store user type in session for consistency
$_SESSION['user_type'] = $user_type;
echo "<script>console.log('" . $_SESSION['user_type'] . "');</script>";

// Fetch listings from database
$listings = [];

try {
    if (!isset($connection) || $connection === null) {
        throw new Exception("Database connection not established.");
    }

    $query = "
        SELECT 
            tl.listingID,
            tl.userID,
            tl.title,
            tl.description,
            tl.tags,
            tl.imageUrl,
            tl.category,
            tl.dateListed,
            tl.status,
            tl.itemType,
            tl.itemCondition,
            tl.species,
            tl.growthStage,
            tl.careInstructions,
            tl.brand,
            tl.dimensions,
            tl.usageHistory,
            tl.lookingFor,
            tl.reported,
            u.fullName AS userName,
            u.country AS location,
            u.tradesCompleted
        FROM tbltrade_listings tl
        LEFT JOIN tblusers u ON tl.userID = u.userID
        WHERE tl.status = 'active'
        ORDER BY tl.dateListed DESC
    ";

    $result = mysqli_query($connection, $query);

    if (!$result) {
        throw new Exception("SQL Error: " . mysqli_error($connection));
    }

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $row['userRating'] = round(rand(45, 50) / 10, 1);
            $row['userTradeCount'] = (int)$row['tradesCompleted'];
            $listings[] = $row;
        }
    }

    $listings_json = json_encode($listings, JSON_INVALID_UTF8_SUBSTITUTE);

} catch (Exception $e) {
    error_log("Error fetching trade listings: " . $e->getMessage());
    $listings = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trade Marketplace - ReLeaf</title>
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">

    <link rel="stylesheet" href="../../style/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <style>
        /* Additional styling for enhanced visual appeal */
        .trade-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 0 20px 10px;
        }

        .trade-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px 20px;
            background: linear-gradient(135deg, var(--MainGreen) 0%, var(--LightGreen) 100%);
            border-radius: 12px;
            color: var(--White);
        }

        .trade-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .trade-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        .trade-header a {
            margin-top: 20px;
            display: inline-block;
        }

        .owner-badge {
            color: var(--White);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
            display: inline-block;
            width: 100%;
            text-align: center;
            font-size: 1.2rem;
        }

        .modal-actions .edit-btn {
            background-color: var(--bg-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .modal-actions .edit-btn:hover {
            background-color: var(--btn-color-hover);
        }

        .modal-actions .delete-btn {
            background-color: var(--Red);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .modal-actions .delete-btn:hover {
            background-color: var(--LowRed);
        }

        .filters-section {
            background: var(--bg-color);
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .filters-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-heading);
            font-size: 14px;
        }

        .filter-select {
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background: var(--bg-color);
            color: var(--text-color);
            font-size: 14px;
        }

        .search-box {
            position: relative;
            margin-bottom: 20px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 14px;
            background: var(--bg-color);
            color: var(--text-color);
        }

        .search-box button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--Gray);
            cursor: pointer;
        }

        .listings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .listing-card {
            background: var(--bg-color);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid var(--border-color);
            position: relative;
        }

        .listing-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .listing-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: var(--sec-bg-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--Gray);
            font-size: 14px;
        }

        .listing-content {
            padding: 20px;
        }

        .listing-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .listing-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-heading);
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .listing-category {
            background: var(--White);
            color: var(--Black);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            width: fit-content;
        }

        .listing-description {
            color: var(--text-color);
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .listing-details {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }

        .detail-badge {
            background: var(--LightGreen);
            color: var(--Black);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
        }

        .listing-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
        }

        .listing-user {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--MainGreen);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--White);
            font-size: 12px;
            font-weight: 600;
        }

        .listing-date {
            font-size: 0.8rem;
        }

        .plant-special {
            background: linear-gradient(135deg, #10b981, #a7f3d0);
            color: var(--White);
        }

        .item-special {
            background: linear-gradient(135deg, #6366f1, #a5b4fc);
            color: var(--White);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--Gray);
        }

        .empty-state p {
            margin-bottom: 20px;
            font-size: 16px;
        }

        .category-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .category-tab {
            padding: 10px 20px;
            border: 1px solid var(--border-color);
            border-radius: 25px;
            background: var(--bg-color);
            color: var(--text-color);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
        }

        .category-tab.active {
            background: var(--MainGreen);
            color: var(--White);
            border-color: var(--MainGreen);
        }

        .category-tab:hover {
            background: var(--sec-bg-color);
        }

        .category-tab.active:hover {
            background: var(--btn-color-hover);
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .listing-modal {
            background: var(--bg-color);
            border-radius: 12px;
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--bg-color);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 20px;
            color: var(--Gray);
            z-index: 10;
            transition: all 0.3s ease;
        }

        .modal-close:hover {
            background: var(--sec-bg-color);
            color: var(--text-color);
        }

        .modal-content {
            padding: 30px;
        }

        .modal-header {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .modal-image {
            width: 100%;
            height: 250px;
            border-radius: 8px;
            background: var(--sec-bg-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--Gray);
            overflow: hidden;
        }

        .modal-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .modal-info h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text-heading);
        }

        .modal-category {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 15px;
        }

        .modal-description {
            color: var(--text-color);
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .modal-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
            padding: 20px;
            background: var(--sec-bg-color);
            border-radius: 8px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 12px;
            color: var(--Gray);
            margin-bottom: 5px;
            font-weight: 500;
        }

        .detail-value {
            font-weight: 600;
            color: var(--text-heading);
        }

        .modal-user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: var(--sec-bg-color);
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .user-avatar-large {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--MainGreen);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--White);
            font-size: 18px;
            font-weight: 600;
            flex-shrink: 0;
        }

        .user-details h4 {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text-heading);
        }

        .user-details p {
            color: var(--Gray);
            font-size: 14px;
            margin-bottom: 8px;
        }

        .user-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }

        .stars {
            color: #fbbf24;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }

        .save-btn {
            background: transparent;
            color: var(--Gray);
            border: 1px solid var(--border-color);
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }

        .save-btn:hover {
            background: var(--sec-bg-color);
        }

        .trade-btn {
            background: var(--MainGreen);
            color: var(--White);
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .trade-btn:hover {
            background: var(--btn-color-hover);
        }

        /* Admin-specific styles */
        .admin-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px 20px;
            background: var(--sec-bg-color);
            border-radius: 8px;
            border-left: 4px solid var(--MainGreen);
        }

        .view-toggle {
            display: flex;
            gap: 10px;
        }

        .view-toggle-btn {
            padding: 8px 16px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background: var(--bg-color);
            color: var(--text-color);
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }

        .view-toggle-btn.active {
            background: var(--MainGreen);
            color: var(--White);
            border-color: var(--MainGreen);
        }

        .delete-btn {
            background: #dc2626;
            color: var(--White);
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .delete-btn:hover {
            background: #b91c1c;
        }

        .admin-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #dc2626;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            z-index: 2;
        }

        .admin-badge.reported-badge {
            position: static;
            margin-top: 10px;
            display: inline-block;
            background-color: #dc3545;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .modal-actions .report-btn {
            background-color: #ffc107;
            color: #212529;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .modal-actions .report-btn:hover {
            background-color: #e0a800;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .modal-actions button {
            flex: 1;
            min-width: 120px;
        }

        @media (max-width: 768px) {
            .modal-header {
                grid-template-columns: 1fr;
            }
            
            .modal-image {
                height: 200px;
            }
            
            .modal-actions {
                flex-direction: column;
            }
            
            .save-btn, .trade-btn {
                width: 100%;
            }
            
            .admin-controls {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>
    <div id="cover" class="" onclick="hideMenu()"></div>

    <!-- Dynamic Header based on user type -->
    <header id="main-header">
        <?php if ($user_type === 'admin'): ?>
            <!-- Admin Header -->
            <section class="c-logo-section">
                <a href="../../pages/adminPages/adminIndex.php" class="c-logo-link">
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
                                <img src="../../assets/images/light-mode-icon.svg" alt="Light Mode Icon" >
                            </button>
                            <a href="../../pages/adminPages/aProfile.html">
                                <img src="../../assets/images/profile-light.svg" alt="Profile">
                            </a>
                        </section>

                        <a href="../../pages/adminPages/adminIndex.php">Dashboard</a>
                        <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
                        <a href="../../pages/CommonPages/mainEvent.html">Event</a>
                        <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                        <a href="../../pages/CommonPages/mainFAQ.html">FAQs</a>
                        <a href="../../pages/adminPages/aHelpTicket.php">Help</a>
                    </div>
                </div>
            </nav>

            <!-- Menu Links Desktop + Tablet -->
            <nav class="c-navbar-desktop">
                <a href="../../pages/adminPages/adminIndex.php">Dashboard</a>
                <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
                <a href="../../pages/CommonPages/mainEvent.html">Event</a>
                <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                <a href="../../pages/CommonPages/mainFAQ.html">FAQs</a>
                <a href="../../pages/adminPages/aHelpTicket.php">Help</a>
            </nav>          
            <section class="c-navbar-more">
                <input type="text" placeholder="Search..." id="searchBar" class="search-bar">
                <button id="themeToggle2">
                    <img src="../../assets/images/light-mode-icon.svg" alt="Light Mode Icon" >
                </button>
                <a href="../../pages/adminPages/aProfile.html">
                    <img src="../../assets/images/profile-light.svg" alt="Profile" id="profileImg">
                </a>
            </section>
        <?php else: ?>
            <!-- Member Header -->
            <section class="c-logo-section">
                <a href="../../pages/MemberPages/memberIndex.html" class="c-logo-link">
                    <img src="../../assets/images/Logo.png" alt="Logo" class="c-logo">
                    <div class="c-text">ReLeaf</div>
                </a>
            </section>

            <!-- Menu Links Mobile -->
            <nav class="c-navbar-side">
                <input type="text" placeholder="Search..." id="searchBar" class="search-bar">
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
                                <a href="../../pages/MemberPages/mChat.html">
                                    <img src="../../assets/images/chat-light.svg" alt="Chatbox">
                                </a>
                                <span class="c-notification-badge" id="chatBadgeMobile"></span>
                            </div>

                            <a href="../../pages/MemberPages/mSetting.html">
                                <img src="../../assets/images/setting-light.svg" alt="Settings">
                            </a>
                        </section>

                        <a href="../../pages/MemberPages/memberIndex.html">Home</a>
                        <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
                        <a href="../../pages/CommonPages/mainEvent.html">Event</a>
                        <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                        <a href="../../pages/CommonPages/aboutUs.html">About</a>
                    </div>
                </div>
            </nav>

            <!-- Menu Links Desktop + Tablet -->
            <nav class="c-navbar-desktop">
                <a href="../../pages/MemberPages/memberIndex.html">Home</a>
                <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
                <a href="../../pages/CommonPages/mainEvent.html">Event</a>
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
        <?php endif; ?>
    </header>

    <hr>

    <!-- Main Content -->
    <main>
        <div class="trade-container">
            <!-- Trade Header -->
            <div class="trade-header">
                <h1>Trade Marketplace</h1>
                <p>Exchange items and plants with fellow ReLeaf community members. Sustainable trading for a greener future.</p>
                <?php if ($user_type === 'member'): ?>
                    <a link href="../../pages/MemberPages/addTrade.php" id="createListingLink">
                        <button class="c-btn c-btn-primary" id="createListingBtn">Create New Listing</button>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Admin Controls -->
            <?php if ($user_type === 'admin'): ?>
            <div class="admin-controls" id="adminControls">
                <div class="view-toggle">
                    <button class="view-toggle-btn active" data-view="all">All Listings</button>
                    <button class="view-toggle-btn" data-view="reported">Reported Listings</button>
                </div>
                <div>
                    <span id="adminStatus">Viewing all listings</span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Category Tabs -->
            <div class="category-tabs">
                <div class="category-tab active" data-category="all">All Listings</div>
                <div class="category-tab" data-category="Plants">Plants</div>
                <div class="category-tab" data-category="Gardening Tools">Gardening Tools</div>
                <div class="category-tab" data-category="Seeds & Saplings">Seeds & Saplings</div>
                <div class="category-tab" data-category="Garden Decor">Garden Decor</div>
                <div class="category-tab" data-category="Gardening Books">Gardening Books</div>
            </div>

            <!-- Filters Section -->
            <div class="filters-section">
                <div class="search-box">
                    <input type="text" placeholder="Search for items, plants, or keywords..." id="searchInput">
                    <button>🔍</button>
                </div>
                
                <div class="filters-row">
                    <div class="filter-group">
                        <label>Category</label>
                        <select class="filter-select" id="categoryFilter">
                            <option value="all">All Categories</option>
                            <option value="Plants">Plants</option>
                            <option value="Gardening Tools">Gardening Tools</option>
                            <option value="Seeds & Saplings">Seeds & Saplings</option>
                            <option value="Garden Decor">Garden Decor</option>
                            <option value="Gardening Books">Gardening Books</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Condition</label>
                        <select class="filter-select" id="conditionFilter">
                            <option value="all">Any Condition</option>
                            <option value="New">New</option>
                            <option value="Excellent">Excellent</option>
                            <option value="Good">Good</option>
                            <option value="Fair">Fair</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Item Type</label>
                        <select class="filter-select" id="typeFilter">
                            <option value="all">All Types</option>
                            <option value="Plant">Plants Only</option>
                            <option value="Item">Items Only</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Sort By</label>
                        <select class="filter-select" id="sortFilter">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="title">Title A-Z</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Listings Grid -->
            <div class="listings-grid" id="listingsGrid">
                <!-- Listings will be populated by JavaScript -->
            </div>
        </div>

        <!-- Listing Detail Modal -->
        <div class="modal-overlay" id="listingModal">
            <div class="listing-modal">
                <button class="modal-close" id="modalClose">×</button>
                <div class="modal-content" id="modalContent">
                    <!-- Modal content will be populated by JavaScript -->
                </div>
            </div>
        </div>

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
    </main>

    <hr>

    <!-- Footer -->
    <footer>
        <!-- Column 1 -->
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
        
        <!-- Column 2 -->
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
                <a href="../../pages/CommonPages/mainEvent.html">Events</a><br>
                <a href="../../pages/CommonPages/mainBlog.html">Blogs</a><br>
                <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
            </div>
        </section>
    </footer>

    <script>
        // Pass PHP variables to JavaScript
        const isAdmin = <?php echo $user_type === 'admin' ? 'true' : 'false'; ?>;
        const listingsData = <?php echo $listings_json ?: '[]'; ?>;
        const currentUserId = <?php echo $user_id; ?>;
    </script>
    <script src="../../javascript/mainTrade.js"></script>
    <script src="../../javascript/mainScript.js"></script>
</body>
</html>