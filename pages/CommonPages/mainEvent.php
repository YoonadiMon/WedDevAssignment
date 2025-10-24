<?php
session_start();
include("../../php/dbConn.php");
include("../../php/sessionCheck.php");

// Initialize variables
$filterMode = isset($_GET['mode']) ? (array)$_GET['mode'] : [];
$filterType = isset($_GET['type']) ? (array)$_GET['type'] : [];
$filterStatus = isset($_GET['status']) ? (array)$_GET['status'] : [];
$sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'newest';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$events = [];
$eventCount = 0;

// Auto-close expired events
$autoCloseQuery = "UPDATE tblevents SET status = 'closed' WHERE endDate < CURDATE() AND status NOT IN ('cancelled', 'closed')";
if (!$connection->query($autoCloseQuery)) {
    error_log("Auto-close events failed: " . $connection->error);
}

$query = "SELECT * FROM tblevents WHERE 1=1";
$params = [];
$types = "";

// Apply search filter
if (!empty($searchQuery)) {
    $query .= " AND (title LIKE ? OR description LIKE ? OR location LIKE ?)";
    $searchParam = "%{$searchQuery}%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
    $types .= "sss";
}

// Apply filters with validation
$validModes = ['online', 'physical', 'hybrid'];
$validTypes = ['clean-up', 'workshop', 'tree-planting', 'campaign', 'talk', 'seminar', 'competition', 'other'];
$validStatuses = ['open', 'closed'];

if (!empty($filterMode)) {
    $filterMode = array_intersect($filterMode, $validModes);
    if (!empty($filterMode)) {
        $placeholders = implode(',', array_fill(0, count($filterMode), '?'));
        $query .= " AND mode IN ($placeholders)";
        $params = array_merge($params, $filterMode);
        $types .= str_repeat('s', count($filterMode));
    }
}

if (!empty($filterType)) {
    $filterType = array_intersect($filterType, $validTypes);
    if (!empty($filterType)) {
        $placeholders = implode(',', array_fill(0, count($filterType), '?'));
        $query .= " AND type IN ($placeholders)";
        $params = array_merge($params, $filterType);
        $types .= str_repeat('s', count($filterType));
    }
}

if (!empty($filterStatus)) {
    $filterStatus = array_intersect($filterStatus, $validStatuses);
    if (!empty($filterStatus)) {
        $placeholders = implode(',', array_fill(0, count($filterStatus), '?'));
        $query .= " AND status IN ($placeholders)";
        $params = array_merge($params, $filterStatus);
        $types .= str_repeat('s', count($filterStatus));
    }
}

// Apply sorting with validation
$validSortOptions = ['newest', 'oldest', 'popular', 'date'];
$sortBy = in_array($sortBy, $validSortOptions) ? $sortBy : 'newest';

switch($sortBy) {
    case 'oldest':
        $query .= " ORDER BY eventID ASC";
        break;
    case 'popular':
        $query .= " ORDER BY maxPax DESC";
        break;
    case 'date':
        $query .= " ORDER BY startDate ASC";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY eventID DESC";
        break;
}

if ($stmt = $connection->prepare($query)) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $events = $result->fetch_all(MYSQLI_ASSOC);
        $eventCount = count($events);
    } else {
        error_log("Event query execution failed: " . $stmt->error);
        $events = [];
        $eventCount = 0;
    }
    $stmt->close();
} else {
    error_log("Event query preparation failed: " . $connection->error);
    $events = [];
    $eventCount = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - ReLeaf</title>
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">
    <link rel="stylesheet" href="../../style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <style>
        /* Additional styling unique to page */
        .event-browse-container {
            display: flex;
            padding-left: 1rem;
            padding-right: 1rem;
            gap: 2rem;
            margin-top: 1rem;
        }

        /* Event Header */
        .event-header {
            background: linear-gradient(135deg, var(--LowGreen), var(--MainGreen), var(--LowGreen));
            padding: 3rem 2rem;
            margin: 2rem 0;
            text-align: center;
            border-radius: 20px;
        }

        .event-header-content {
            max-width: 600px;
            margin: 0 auto;
        }

        .event-header-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--White);
            margin-bottom: 0.75rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .event-header-subtitle {
            font-size: 1.25rem;
            color: var(--White);
            font-weight: 400;
            margin: 0;
            opacity: 0.95;
        }

        .btn-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: transparent;
            padding: 1rem;  
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .top-btn {
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .btn-left-group {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .filter-toggle {
            display: none;
        }

        /* Filter Sidebar */
        .filter-sidebar {
            width: 260px;
            flex-shrink: 0;
            background: var(--bg-color);
            border: 1px solid var(--Gray);
            border-radius: 12px;
            padding: 1.5rem;
            height: fit-content;
            position: sticky;
            top: 1rem;
        }

        .filter-sidebar h3 {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            color: var(--text-color);
            font-weight: 600;
        }

        .filter-group {
            margin-bottom: 1.5rem;
        }

        .filter-group h4 {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.75rem;
        }

        .filter-option {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            cursor: pointer;
        }

        .filter-option input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 0.5rem;
            cursor: pointer;
            accent-color: var(--DarkerGray);
        }

        .dark-mode .filter-option input[type="checkbox"] {
            accent-color: var(--LowGreen);
        }

        .filter-option label {
            font-size: 0.9rem;
            color: var(--text-color);
            cursor: pointer;
            margin: 0;
            font-weight: 400;
        }

        .filter-clear {
            color: var(--MainGreen);
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: underline;
            background: none;
            border: none;
            padding: 0;
            margin-top: 1rem;
        }

        .no-results-text {
            grid-column: 1/-1; 
            text-align: center; 
            padding: 3rem; 
            color: var(--DarkerGray);
        }
        
        .dark-mode .no-results-text {
            color: var(--Gray);
        }

        /* Event Content */
        .event-content {
            flex: 1;
            min-width: 0;
        }

        .event-search-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .event-top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .event-sort-bar {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--Gray);
        }

        .event-count {
            font-size: 1rem;
            color: var(--text-color);
            font-weight: 500;
        }

        .search-input-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 1;
            max-width: 400px;
        }

        .event-search-input {
            flex: 1;
            padding: 0.6rem 1rem;
            font-size: 0.95rem;
            transition: border-color 0.3s ease;
        }

        .event-search-input:focus {
            outline: none;
            border-color: var(--MainGreen);
        }

        .search-btn {
            padding: 0.6rem 1rem;
            background: var(--MainGreen);
            color: var(--Black);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }

        .sort-control {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .sort-control label {
            font-size: 1rem;
            color: var(--text-color);
            font-weight: 300;
            margin: 0;
            white-space: nowrap;
        }

        .sort-control select {
            width: 180px;
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--text-color);
        }

        /* Event Grid */
        .event-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        /* Event Card */
        .event-card {
            background: var(--bg-color);
            border: 2px solid var(--Gray);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
        }

        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px var(--MainGreen);
            border: 2px solid var(--MainGreen);
        }

        .event-card-banner {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background: var(--bg-color);
            flex-shrink: 0;
            border-bottom: 1px solid var(--Black);
        }

        .event-card-content {
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .event-card-date {
            font-size: 0.8rem;
            color: var(--MainGreen);
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }

        .event-card-title {
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .event-card-location {
            font-size: 0.85rem;
            color: var(--DarkerGray);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .dark-mode .event-card-location {
            color: var(--Gray);
        }
        
        .event-card-description {
            font-size: 0.9rem;
            color: var(--text-color);
            line-height: 1.5;
            margin-bottom: 1rem;
            overflow: hidden;
            flex-grow: 1;
        }

        .event-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 0.75rem;
            border-top: 1px solid var(--Gray);
            margin-top: auto;
        }

        .event-card-attendees {
            font-size: 0.85rem;
            color: var(--Gray);
        }
        
        .event-card-mode {
            background: var(--sec-bg-color);
            color: var(--MainGreen);
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .dark-mode .event-card-mode {
            color: var(--DarkerGray);
        }

        @media (max-width: 1024px) {
            .event-browse-container {
                flex-direction: column;
            }

            .btn-wrapper {
                justify-content: space-between;
            }

            .filter-sidebar {
                display: none;
                width: 100%;
                position: static;
            }
            
            .filter-sidebar.active {
                display: block;
            }

            .filter-toggle {
                display: block;
            }

            .event-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }

        @media (max-width: 760px) {
            .event-header {
                padding: 2rem 1.5rem;
            }

            .event-header-title {
                font-size: 2rem;
            }

            .event-header-subtitle {
                font-size: 1.1rem;
            }
            
            .event-grid {
                grid-template-columns: 1fr;
            }

            .event-top-bar {
                flex-direction: column;
                align-items: flex-start;
            }

            .event-search-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-input-wrapper {
                max-width: 100%;
            }

            .sort-control select {
                width: 150px;
            }
        }

        @media (max-width: 480px) {
            .event-header {
                padding: 1.5rem 1rem;
            }

            .event-header-title {
                font-size: 1.75rem;
            }

            .event-header-subtitle {
                font-size: 1rem;
            }

            .btn-left-group {
                display: flex;
                justify-content: space-between;
                gap: 1rem;
            }

            .top-btn {
                font-size: 0.85rem;
                padding: 0.6rem 1.2rem;
                margin-bottom: 0.5rem;
            }

            .filter-toggle {
                width: 100%;
            }

            .sort-control {
                display: block;
            }

            .sort-control select{
                margin: 1rem 0 1rem 0;
            }
        }

        @media (max-width: 320px) {
            .top-btn {
                font-size: 0.75rem;
                padding: 0.5rem 1rem;
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
        <!-- Event Header -->
        <section class="event-header">
            <div class="event-header-content">
                <h1 class="event-header-title">🗓️<br>Events</h1>
                <p class="event-header-subtitle">Join us and be part of something bigger — learn, connect, volunteer, and make an impact together.</p>
            </div>
        </section>
        
        <section class="btn-wrapper">
            <?php if ($isAdmin): ?>
                <a href="../../pages/adminPages/aManageEvent.php" class="c-btn c-btn-primary top-btn">Manage Events</a>
            <?php else: ?>
                <div class="btn-left-group">
                    <a href="../../pages/CommonPages/createEvent.php" class="c-btn c-btn-primary top-btn">Host an Event</a>
                    <a href="../../pages/CommonPages/myEvents.php" class="c-btn c-btn-primary top-btn">My Events</a>
                </div>                
            <?php endif; ?>
            <button class="c-btn c-btn-primary top-btn filter-toggle" onclick="toggleFilters()">Show Filters</button>
        </section>
        <section class="event-browse-container">
            <!-- Filter Sidebar -->
            <aside class="filter-sidebar" id="filterSidebar">
                <h3>Filters</h3>

                <form method="GET" action="" id="filterForm">
                    <div class="filter-group">
                        <h4>Mode</h4>
                        <div class="filter-option">
                            <input type="checkbox" id="online" name="mode[]" value="online" 
                                <?php echo in_array('online', $filterMode) ? 'checked' : ''; ?>>
                            <label for="online">Online</label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="physical" name="mode[]" value="physical" 
                                <?php echo in_array('physical', $filterMode) ? 'checked' : ''; ?>>
                            <label for="physical">Physical</label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="hybrid" name="mode[]" value="hybrid" 
                                <?php echo in_array('hybrid', $filterMode) ? 'checked' : ''; ?>>
                            <label for="hybrid">Hybrid</label>
                        </div>
                    </div>

                    <div class="filter-group">
                        <h4>Type</h4>
                        <div class="filter-option">
                            <input type="checkbox" id="cleanup" name="type[]" value="clean-up" 
                                <?php echo in_array('clean-up', $filterType) ? 'checked' : ''; ?>>
                            <label for="cleanup">Clean-up</label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="workshop" name="type[]" value="workshop" 
                                <?php echo in_array('workshop', $filterType) ? 'checked' : ''; ?>>
                            <label for="workshop">Workshop</label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="planting" name="type[]" value="tree-planting" 
                                <?php echo in_array('tree-planting', $filterType) ? 'checked' : ''; ?>>
                            <label for="planting">Tree Planting</label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="campaign" name="type[]" value="campaign" 
                                <?php echo in_array('campaign', $filterType) ? 'checked' : ''; ?>>
                            <label for="campaign">Campaign</label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="talk" name="type[]" value="talk" 
                                <?php echo in_array('talk', $filterType) ? 'checked' : ''; ?>>
                            <label for="talk">Talk</label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="seminar" name="type[]" value="seminar" 
                                <?php echo in_array('seminar', $filterType) ? 'checked' : ''; ?>>
                            <label for="seminar">Seminar</label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="competition" name="type[]" value="competition" 
                                <?php echo in_array('competition', $filterType) ? 'checked' : ''; ?>>
                            <label for="competition">Competition</label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="other" name="type[]" value="other" 
                                <?php echo in_array('other', $filterType) ? 'checked' : ''; ?>>
                            <label for="other">Other</label>
                        </div>
                    </div>

                    <div class="filter-group">
                        <h4>Status</h4>
                        <div class="filter-option">
                            <input type="checkbox" id="open" name="status[]" value="open" 
                                <?php echo in_array('open', $filterStatus) ? 'checked' : ''; ?>>
                            <label for="open">Open</label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="closed" name="status[]" value="closed" 
                                <?php echo in_array('closed', $filterStatus) ? 'checked' : ''; ?>>
                            <label for="Closed">Closed</label>
                        </div>
                    </div>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <input type="hidden" name="sortBy" value="<?php echo htmlspecialchars($sortBy); ?>">
                </form>

                <button class="filter-clear" onclick="clearFilters()">Clear All Filters</button>
            </aside>

            <!-- Event Content -->
            <div class="event-content">                    
                <!-- Search Bar -->
                <div class="event-search-bar">
                    <div class="event-count">Showing <span id="eventCount"><?php echo $eventCount; ?></span> events</div>
                    <div class="search-input-wrapper">
                        <input type="text" id="eventSearch" placeholder="Search events..." class="c-input event-search-input" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button onclick="performSearch()" class="search-btn">Search</button>
                    </div>
                </div>
                
                <!-- Sort Bar -->
                <div class="event-sort-bar">
                    <div class="sort-control">
                        <label for="sortBy">Sort by:</label>
                        <select class="c-input c-input-select" id="sortBy" name="sortBy" onchange="updateSort()">
                            <option value="newest" <?php echo $sortBy == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="oldest" <?php echo $sortBy == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                            <option value="popular" <?php echo $sortBy == 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                            <option value="date" <?php echo $sortBy == 'date' ? 'selected' : ''; ?>>Event Date</option>
                        </select>
                    </div>
                </div>

                <!-- Event Grid -->
                <div class="event-grid" id="eventGrid">
                    <?php if ($eventCount == 0): ?>
                        <p class="no-results-text">
                            No events found matching your filters.
                        </p>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                            <a href="joinEvent.php?id=<?php echo $event['eventID']; ?>" class="event-card">
                                <?php if (!empty($event['bannerFilePath'])): ?>
                                    <img src="<?php echo htmlspecialchars($event['bannerFilePath']); ?>" 
                                        alt="<?php echo htmlspecialchars($event['title']); ?>" 
                                        class="event-card-banner" 
                                        onerror="this.src='../../assets/images/banner-placeholder.png';">
                                <?php else: ?>
                                    <img src="../../assets/images/banner-placeholder.png" 
                                        alt="<?php echo htmlspecialchars($event['title']); ?>" 
                                        class="event-card-banner">
                                <?php endif; ?>
                                <?php
                                    $maxDescriptionLength = 250; // characters
                                    $maxLocationLength = 70; // characters

                                    $description = $event['description'];
                                    if (strlen($description) > $maxDescriptionLength) {
                                        $description = substr($description, 0, $maxDescriptionLength) . '...';
                                    }

                                    $location = $event['location'] . ", " . $event['country'];
                                    if (strlen($location) > $maxLocationLength) {
                                        $location = substr($location, 0, $maxLocationLength) . '...';
                                    }
                                ?>

                                <div class="event-card-content">
                                    <div class="event-card-date">
                                        <?php echo date('M d, Y', strtotime($event['startDate'])); ?>
                                    </div>
                                    <h3 class="event-card-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                                    <div class="event-card-location">
                                        📍 <?php echo htmlspecialchars($location); ?>
                                    </div>
                                    <p class="event-card-description">
                                        <?php echo htmlspecialchars($description); ?>
                                    </p>
                                    <div class="event-card-footer">
                                        <div class="event-card-attendees">
                                            👥 <?php echo $event['maxPax']; ?> max attendees
                                        </div>
                                        <div class="event-card-mode">
                                            <?php echo htmlspecialchars($event['mode']); ?>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <br>
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
    <script>
        // Toggle filters on mobile
        function toggleFilters() {
            const sidebar = document.getElementById('filterSidebar');
            sidebar.classList.toggle('active');
            
            const button = document.querySelector('.filter-toggle');
            if (sidebar.classList.contains('active')) {
                button.textContent = 'Hide Filters';
                localStorage.setItem('filterSidebarOpen', 'true');
            } else {
                button.textContent = 'Show Filters';
                localStorage.setItem('filterSidebarOpen', 'false');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const filterState = localStorage.getItem('filterSidebarOpen');
            const sidebar = document.getElementById('filterSidebar');
            const button = document.querySelector('.filter-toggle');
            
            if (filterState === 'true' && window.innerWidth <= 1024) {
                sidebar.classList.add('active');
                button.textContent = 'Hide Filters';
            }
        });

        // Clear all filters
        function clearFilters() {
            window.location.href = window.location.pathname;
        }

        // Update sort
        function updateSort() {
            const sortValue = document.getElementById('sortBy').value;
            const url = new URL(window.location);
            url.searchParams.set('sortBy', sortValue);
            window.location.href = url.toString();
        }

        // Auto-submit form when checkbox changes
        document.querySelectorAll('.filter-option input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        });

        // Perform search
        function performSearch() {
            const searchValue = document.getElementById('eventSearch').value;
            const url = new URL(window.location);
            
            if (searchValue.trim() !== '') {
                url.searchParams.set('search', searchValue);
            } else {
                url.searchParams.delete('search');
            }
            
            // Preserve existing filter parameters
            const existingParams = new URLSearchParams(window.location.search);
            ['mode[]', 'type[]', 'status[]', 'sortBy'].forEach(param => {
                if (existingParams.has(param)) {
                    url.searchParams.set(param, existingParams.get(param));
                }
            });
            
            window.location.href = url.toString();
        }

        // Allow Enter key to trigger search
        document.getElementById('eventSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    </script>
</body>
</html>

<?php
mysqli_close($connection);
?>