<?php
session_start();
include("../../php/dbConn.php");
include("../../php/sessionCheck.php");

// Initialize variables
$showWelcomePopup = false;
$userName = '';
$userData = [];
$initials = '';

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
            function getInitials($name) {
                $words = explode(' ', trim($name));
                if (count($words) >= 2) {
                    return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                }
                return strtoupper(substr($words[0], 0, 2));
            }
            
            $initials = getInitials($userData['fullName']);
        }
    }
    $stmt->close();
}

// Fetch dashboard statistics
$stats = [
    'totalUsers' => 0,
    'activeUsers' => 0,
    'inactiveUsers' => 0,
    'todaySignups' => 0,
    'topCountry' => 'N/A',
    'topCountryPercentage' => 0,
    'totalEvents' => 0,
    'ongoingEvents' => 0,
    'totalBlogs' => 0,
    'tradesCompleted' => 0,
    'activeListings' => 0,
    'totalTickets' => 0,
    'openTickets' => 0,
    'solvedTickets' => 0,
    'commonIssue' => 'N/A',
    'commonIssuePercentage' => 0
];

// User statistics
$query = "SELECT COUNT(*) as total FROM tblusers WHERE userType = 'member'";
$result = $connection->query($query);
if ($row = $result->fetch_assoc()) {
    $stats['totalUsers'] = $row['total'];
}

// Active users (logged in within last 30 days)
$query = "SELECT COUNT(*) as active FROM tblusers WHERE userType = 'member' AND lastLogin >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$result = $connection->query($query);
if ($row = $result->fetch_assoc()) {
    $stats['activeUsers'] = $row['active'];
    $stats['inactiveUsers'] = $stats['totalUsers'] - $stats['activeUsers'];
}

// Today's signups (using createdAt field)
$query = "SELECT COUNT(*) as today FROM tblusers WHERE userType = 'member' AND DATE(createdAt) = CURDATE()";
$result = $connection->query($query);
if ($row = $result->fetch_assoc()) {
    $stats['todaySignups'] = $row['today'];
} else {
    $stats['todaySignups'] = 0;
}

// Top country among all users
$query = "SELECT country, COUNT(*) as count FROM tblusers WHERE userType = 'member' AND lastLogin >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY country ORDER BY count DESC LIMIT 1";
$result = $connection->query($query);
if ($row = $result->fetch_assoc()) {
    $stats['topCountry'] = $row['country'];
    $stats['topCountryPercentage'] = ($stats['totalUsers'] > 0) ? round(($row['count'] / $stats['totalUsers']) * 100) : 0;
} else {
    $stats['topCountry'] = 'N/A';
    $stats['topCountryPercentage'] = 0;
}

// Weekly active users data (last 7 days)
$weeklySignups = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $query = "SELECT COUNT(*) as count FROM tblusers WHERE userType = 'member' AND DATE(lastLogin) = '$date'";
    $result = $connection->query($query);
    $row = $result->fetch_assoc();
    $weeklySignups[$date] = $row['count'] ?? 0;
}

// Event statistics
$query = "SELECT COUNT(*) as total FROM tblevents";
$result = $connection->query($query);
if ($row = $result->fetch_assoc()) {
    $stats['totalEvents'] = $row['total'];
}

$query = "SELECT COUNT(*) as ongoing FROM tblevents WHERE status = 'open' AND endDate >= CURDATE()";
$result = $connection->query($query);
if ($row = $result->fetch_assoc()) {
    $stats['ongoingEvents'] = $row['ongoing'];
}

// Blog statistics
$query = "SELECT COUNT(*) as total FROM tblblog";
$result = $connection->query($query);
if ($row = $result->fetch_assoc()) {
    $stats['totalBlogs'] = $row['total'];
}

// Trade statistics 
$query = "SELECT SUM(tradesCompleted) AS totalTrades FROM tblusers WHERE userType = 'member'";
$result = $connection->query($query);
if ($row = $result->fetch_assoc()) {
    $stats['tradesCompleted'] = ($row['totalTrades'] ?? 0) / 2;
}

// Active trade listings
$query = "SELECT COUNT(*) as activeListings FROM tbltrade_listings WHERE status = 'active'";
$result = $connection->query($query);
if ($row = $result->fetch_assoc()) {
    $stats['activeListings'] = $row['activeListings'] ?? 0;
}

// Ticket statistics
$query = "SELECT COUNT(*) as total FROM tbltickets";
$result = $connection->query($query);
if ($row = $result->fetch_assoc()) {
    $stats['totalTickets'] = $row['total'];
}

$query = "SELECT COUNT(*) as openTickets FROM tbltickets WHERE status = 'open'";
$result = $connection->query($query);
if ($row = $result->fetch_assoc()) {
    $stats['openTickets'] = $row['openTickets'];
}

$query = "SELECT COUNT(*) as solvedTickets FROM tbltickets WHERE status = 'solved'";
$result = $connection->query($query);
if ($row = $result->fetch_assoc()) {
    $stats['solvedTickets'] = $row['solvedTickets'];
}

// Most common ticket category
$query = "SELECT category, COUNT(*) as count FROM tbltickets GROUP BY category ORDER BY count DESC LIMIT 1";
$result = $connection->query($query);
if ($row = $result->fetch_assoc()) {
    $categoryNames = [
        'technical' => 'Technical',
        'account' => 'Account',
        'billing' => 'Billing',
        'feature' => 'Feature Request',
        'bug' => 'Bug Report',
        'general' => 'General',
        'other' => 'Other'
    ];
    $stats['commonIssue'] = $categoryNames[$row['category']] ?? ucfirst($row['category']);
    $stats['commonIssuePercentage'] = ($stats['totalTickets'] > 0) ? round(($row['count'] / $stats['totalTickets']) * 100) : 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ReLeaf</title>
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">
    <link rel="stylesheet" href="../../style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Profile Section */
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
            background: var(--bg-color);
            padding: 2rem;
            border: 1px solid var(--Gray);
            border-radius: 1.5rem;
            box-shadow: 0 4px 12px var(--LowGreen);
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
        }

        .username-country-wrapper {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 0.75rem 0;
            flex-wrap: wrap;
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
            font-weight: 500;
        }

        .profile-country-wrapper {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .profile-country {
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
        }

        .dark-mode .profile-country-wrapper img {
            content: url('../../assets/images/location-icon-dark.svg');
        }

        .profile-bio {
            font-size: 0.95rem;
            color: var(--text-color);
            line-height: 1.6;
            margin-top: 0.75rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.75rem;
            flex-shrink: 0;
        }

        .action-btn {
            background: var(--bg-color);
            border: none;
            cursor: pointer;
            padding: 0.875rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
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

        /* Dashboard Section */
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .dashboard-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--MainGreen);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .dashboard-subtitle {
            color: var(--DarkerGray);
            font-size: 1rem;
        }

        .dark-mode .dashboard-subtitle {
            color: var(--Gray);
        }

        /* Summary Cards Grid */
        .summary-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background: var(--bg-color);
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 4px 12px var(--Gray);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .summary-card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px var(--LowGreen);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--MainGreen);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .card-link {
            color: var(--Gray);
            border: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            white-space: nowrap;
        }

        .card-link:hover {
            text-decoration: underline;
        }

        .stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--Gray);
        }

        .stat-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .stat-label {
            color: var(--text-color);
            font-weight: 500;
            font-size: 0.95rem;
        }

        .stat-value {
            color: var(--MainGreen);
            font-weight: 700;
            font-size: 1.5rem;
        }

        /* Progress Bar */
        .progress-wrapper {
            margin-top: 1.5rem;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-color);
        }

        .progress-label strong {
            margin-left: 0.25rem;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .progress-bar {
            height: 8px;
            background: var(--Gray);
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            background: var(--MainGreen);
            border-radius: 10px;
            transition: width 1s ease;
            animation: fillBar 1.5s ease-out;
        }

        @keyframes fillBar {
            from { width: 0; }
        }

        /* Chart Card */
        .chart-card {
            grid-column: 1 / -1;
            background: var(--bg-color);
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 4px 12px var(--Gray);
        }

        .chart-container {
            position: relative;
            height: 350px;
            margin-top: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .profile-top {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .profile-info {
                flex-direction: column;
                align-items: center;
            }

            .summary-section {
                grid-template-columns: 1fr;
            }

            .dashboard-header h1 {
                font-size: 2rem;
            }

            .avatar-circle {
                width: 80px;
                height: 80px;
                font-size: 2rem;
            }

            .profile-name {
                font-size: 1.5rem;
            }

            .username-country-wrapper {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .card-header-with-link {
                flex-direction: column;
                justify-content: flex-start;
                gap: 1rem;
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
    
    <!-- Header -->
    <header>
        <section class="c-logo-section">
            <a href="../../pages/adminPages/adminIndex.php" class="c-logo-link">
                <img src="../../assets/images/Logo.png" alt="Logo" class="c-logo">
                <div class="c-text">ReLeaf</div>
            </a>
        </section>

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
        <section >
            <!-- Profile Section -->
            <section class="profile-container">
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
                            <p class="profile-bio">
                                <?php 
                                echo $userData['bio'] 
                                    ? htmlspecialchars($userData['bio']) 
                                    : '<span style="color: var(--Gray);">This admin has yet to set their bio</span>';
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="action-buttons">
                        <a href="../../pages/adminPages/aProfile.php" class="action-btn" title="Edit Profile">
                            <img src="../../assets/images/edit-icon-light.svg" alt="Edit Profile" class="edit-icon">
                        </a>
                        <a href="../../php/logOut.php" class="action-btn" title="Log Out">
                            <img src="../../assets/images/log-out-icon-light.svg" alt="Log Out" class="log-out-icon">
                        </a>
                    </div>
                </div>
            </section>

            <!-- Dashboard Section -->
            <section class="dashboard-container">
                <div class="dashboard-header">
                    <h1>Admin Dashboard</h1>
                    <p class="dashboard-subtitle">Overview of ReLeaf platform statistics</p>
                </div>

                <!-- Summary Cards -->
                <section class="summary-section">
                    <!-- User Summary Card -->
                    <div class="summary-card">
                        <div class="card-header card-header-with-link">
                            <h4 class="card-title">User Summary</h4>
                            <a href="../../pages/adminPages/aManageUser.php" class="card-link">
                                View All >
                            </a>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Total Users</span>
                            <span class="stat-value"><?php echo number_format($stats['totalUsers']); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Active Users</span>
                            <span class="stat-value"><?php echo number_format($stats['activeUsers']); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Today's Signups</span>
                            <span class="stat-value"><?php echo number_format($stats['todaySignups']); ?></span>
                        </div>
                        <div class="progress-wrapper">
                            <div class="progress-label">
                                <span>Top Country: 
                                    <strong><?php echo htmlspecialchars($stats['topCountry']); ?></strong>
                                </span>
                                <span><?php echo $stats['topCountryPercentage']; ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $stats['topCountryPercentage']; ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Summary Card -->
                    <div class="summary-card">
                        <div class="card-header">
                            <h4 class="card-title">Activity Summary</h4>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Total Blogs</span>
                            <span class="stat-value"><?php echo number_format($stats['totalBlogs']); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Total Events</span>
                            <span class="stat-value"><?php echo number_format($stats['totalEvents']); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Ongoing Events</span>
                            <span class="stat-value"><?php echo number_format($stats['ongoingEvents']); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Trades Completed</span>
                            <span class="stat-value"><?php echo number_format($stats['tradesCompleted']); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Active Listings</span>
                            <span class="stat-value"><?php echo number_format($stats['activeListings']); ?></span>
                        </div>
                    </div>

                    <!-- Ticket Summary Card -->
                    <div class="summary-card">
                        <div class="card-header card-header-with-link">
                            <h4 class="card-title">Ticket Summary</h4>
                            <a href="../../pages/adminPages/aHelpTicket.php" class="card-link">
                                View All >
                            </a>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Total Tickets</span>
                            <span class="stat-value"><?php echo number_format($stats['totalTickets']); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Open</span>
                            <span class="stat-value"><?php echo number_format($stats['openTickets']); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Solved</span>
                            <span class="stat-value"><?php echo number_format($stats['solvedTickets']); ?></span>
                        </div>
                        <div class="progress-wrapper">
                            <div class="progress-label">
                                <span>Most Common: <?php echo htmlspecialchars($stats['commonIssue']); ?></span>
                                <span><?php echo $stats['commonIssuePercentage']; ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $stats['commonIssuePercentage']; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Chart Card -->
                <div class="chart-card">
                    <div class="card-header">
                        <h4 class="card-title">Weekly Active Users</h4>
                    </div>
                    <div class="chart-container">
                        <canvas id="signupsChart" 
                                data-weekly-labels='<?php echo json_encode(array_keys($weeklySignups)); ?>'
                                data-weekly-data='<?php echo json_encode(array_values($weeklySignups)); ?>'>
                        </canvas>
                    </div>
                </div>
            </section>
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

    <script>const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;</script>
    <script src="../../javascript/mainScript.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script src="../../javascript/aDashboard.js"></script>
</body>
</html>

<?php mysqli_close($connection); ?>