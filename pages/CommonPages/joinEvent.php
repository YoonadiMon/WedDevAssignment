<?php
include("../../php/dbConn.php");
include("../../php/sessionCheck.php");

$eventID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($eventID <= 0) {
    header("Location: mainEvent.php");
    exit();
}

// Fetch event details
$eventQuery = "SELECT e.*, u.fullName AS hostName, u.email AS hostEmail 
               FROM tblevents e
               JOIN tblusers u ON e.userID = u.userID
               WHERE e.eventID = $eventID";
$eventResult = mysqli_query($connection, $eventQuery);

if (!$eventResult || mysqli_num_rows($eventResult) == 0) {
    header("Location: mainEvent.php");
    exit();
}

$event = mysqli_fetch_assoc($eventResult);

$eventBanner = $event['bannerFilePath'];

// Check if user is already registered
$isRegistered = false;
if (!$isAdmin) {
    $checkQuery = "SELECT * FROM tblregistration 
                   WHERE eventID = $eventID AND userID = $userID AND status = 'active'";
    $checkResult = mysqli_query($connection, $checkQuery);
    $isRegistered = mysqli_num_rows($checkResult) > 0;
}
$isClosed = ($event['status'] == 'closed');
$isHost = ($event['userID'] == $userID);

// Count total attendees
$countQuery = "SELECT COUNT(*) as total FROM tblregistration 
               WHERE eventID = $eventID AND status = 'active'";
$countResult = mysqli_query($connection, $countQuery);
$attendeeCount = mysqli_fetch_assoc($countResult)['total'];

$isFull = ($event['maxPax'] <= $attendeeCount);
if ($isFull) {
    $updateQuery = "UPDATE tblevents SET status = 'closed' WHERE eventID = $eventID AND userID = $userID";
    $updateResult = mysqli_query($connection, $updateQuery);
}

// Handle registration
$registrationMessage = '';
$registrationSuccess = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register']) && !$isAdmin && !$isHost && !$isClosed) {
    if (!$isRegistered) {
        $insertQuery = "INSERT INTO tblregistration (eventID, userID, datetimeRegistered, status) 
                        VALUES ($eventID, $userID, NOW(), 'active')";
        if (mysqli_query($connection, $insertQuery)) {
            $registrationSuccess = true;
            $isRegistered = true;
            $attendeeCount++;
            $registrationMessage = 'Registration successful!';
        } else {
            $registrationMessage = 'Registration failed. Please try again.';
        }
    } else {
        $registrationMessage = 'You are already registered for this event.';
    }
}

// Format date/time
$startDate = date('M d, Y', strtotime($event['startDate']));
$endDate = date('M d, Y', strtotime($event['endDate']));
$time = date('g:i A', strtotime($event['time']));

// Calculate event duration for display
$durationText = $event['duration'] . ' hour' . ($event['duration'] > 1 ? 's' : '');
$daysText = $event['day'] . ' day' . ($event['day'] > 1 ? 's' : '');

// Get host avatar initial
$hostInitial = strtoupper(substr($event['hostName'], 0, 1));

// Get user info for modal
$userQuery = "SELECT fullName, email FROM tblusers WHERE userID = $userID";
$userResult = mysqli_query($connection, $userQuery);
$userInfo = mysqli_fetch_assoc($userResult);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReLeaf - Join Event</title>
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">
    <link rel="stylesheet" href="../../style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <style>
        /* Event Detail Page Styles */
        .event-detail-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        /* Back Button */
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 1.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .back-button:hover {
            color: var(--MainGreen);
            text-decoration: underline;
        }

        /* Event Header */
        .event-header {
            background: var(--bg-color);
            border: 1px solid var(--Gray);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px var(--Gray);
        }

        .event-banner {
            width: 100%;
            height: 400px;
            object-fit: cover;
            background: var(--LowGreen);
            flex-shrink: 0;
        }

        .event-banner-placeholder {
            object-fit: contain;
        }

        .event-header-content {
            padding: 2rem;
        }

        .event-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--MainGreen);
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .event-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            color: var(--text-color);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }

        .event-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .event-meta-icon {
            font-size: 1.2rem;
        }

        .event-host {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: linear-gradient(135deg, var(--LowGreen), var(--bg-color));
            border-radius: 10px;
            margin-top: 1rem;
        }

        .event-host-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--MainGreen);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--White);
            font-weight: 700;
            font-size: 1.5rem;
        }

        .event-host-info h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.25rem;
        }

        .event-host-info p {
            font-size: 0.85rem;
            color: var(--DarkerGray);
        }

        .dark-mode .event-host-info p {
            color: var(--Gray);
        }

        /* Event Content */
        .event-content {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
        }

        .event-main {
            background: var(--bg-color);
            border: 1px solid var(--Gray);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px var(--Gray);
        }

        .event-section {
            margin-bottom: 2rem;
        }

        .event-section:last-child {
            margin-bottom: 0;
        }

        .event-section h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--MainGreen);
        }

        .event-description {
            font-size: 1rem;
            line-height: 1.8;
            color: var(--text-color);
        }

        .event-details-grid {
            display: grid;
            grid-template-columns: minmax(250px, 1fr);
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .event-detail-card {
            padding: 1.5rem;
            border-radius: 10px;
            border: 3px dotted var(--MainGreen);
        }

        .event-detail-card h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .event-detail-card p {
            font-size: 0.9rem;
            color: var(--Gray);
            margin: 0;
        }

        .event-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .event-tag {
            background: var(--MainGreen);
            color: var(--White);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Event Sidebar */
        .event-sidebar {
            height: fit-content;
            position: sticky;
            top: 1rem;
        }

        .event-card-sticky {
            background: var(--bg-color);
            border: 1px solid var(--Gray);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px var(--Gray);
        }

        .event-attendees {
            text-align: center;
            padding: 1.5rem;
            background: var(--LightGreen);
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .event-attendees-count {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .dark-mode .event-attendees {
            background: var(--LowGreen);
        }

        .event-attendees-label {
            color: var(--text-color);
            font-size: 0.9rem;
        }

        .event-info-list {
            list-style: none;
            padding: 0;
            margin-bottom: 1.5rem;
        }

        .event-info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--Gray);
            font-size: 0.9rem;
        }

        .event-info-item:last-child {
            border-bottom: none;
        }

        .event-info-label {
            color: var(--Gray);
            font-weight: 500;
        }

        .event-info-value {
            color: var(--text-color);
            font-weight: 600;
            text-align: right;
        }

        /* Register Button */
        .register-button {
            width: 100%;
            background: var(--MainGreen);
            color: var(--White);
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 900;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px var(--LowGreen);
        }

        .register-button:hover:not(:disabled) {
            transform: translateY(-2px);
        }

        .register-button:active:not(:disabled) {
            transform: translateY(0);
        }

        .register-button:disabled {
            background: var(--Gray);
            cursor: not-allowed;
            transform: none;
            opacity: 0.6;
        }

        .register-button.registered {
            background: var(--Gray);
            box-shadow: none;
        }

        .warning-notice {
            background: var(--Yellow);
            color: var(--White);
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            justify-content: center;
            font-weight: 500;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .warning-notice img {
            width: 20px;  
            height: 20px;
            flex-shrink: 0; 
        }

        .dark-mode .warning-notice {
            background: var(--LowYellow);
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 500;
        }

        .alert-success {
            background: var(--bg-color);
            color: var(--MainGreen);
            border: 1px solid var(--MainGreen);
        }

        .alert-error {
            background: var(--bg-color);
            color: var(--Red);
            border: 1px solid var(--Red);
        }

        /* Confirmation Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: var(--bg-color);
            border: 2px solid var(--text-color);
            border-radius: 16px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .modal-message {
            font-size: 1rem;
            color: var(--DarkerGray);
            line-height: 1.6;
            text-align: center;
        }

        .dark-mode .modal-message {
            color: var(--Gray);
        }

        .modal-details {
            background: var(--LightGreen);
            padding: 1rem;
            border-radius: 10px;
            margin: 1.5rem 0;
        }

        .dark-mode .modal-details {
            background: var(--LowGreen);
        }

        .modal-detail-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 0.9rem;
        }

        .modal-detail-label {
            color: var(--text-color);
        }

        .modal-detail-value {
            color: var(--text-color);
            font-weight: 600;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .modal-btn {
            flex: 1;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .modal-btn-cancel {
            background: var(--Gray);
            color: var(--White);
        }

        .modal-btn-cancel:hover {
            background: var(--DarkerGray);
            color: var(--White);
        }

        .modal-btn-confirm {
            background: var(--MainGreen);
            color: var(--White);
        }

        .modal-btn-confirm:hover {
            transform: scale(1.05);
        }

        /* Success Modal */
        .success-modal {
            color: var(--MainGreen);
        }

        .success-modal .modal-btn-confirm {
            width: 100%;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .event-content {
                grid-template-columns: 1fr;
            }

            .event-sidebar {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .event-title {
                font-size: 2rem;
            }

            .event-banner {
                height: 250px;
            }

            .event-header-content {
                padding: 1.5rem;
            }

            .event-main {
                padding: 1.5rem;
            }

            .event-meta {
                gap: 1rem;
            }

            .modal-content {
                padding: 1.5rem;
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
                            <a href="../../pages/adminPages/aProfile.html">
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
                            <a href="../../pages/MemberPages/mSetting.html">
                                <img src="../../assets/images/setting-light.svg" alt="Settings">
                            </a>
                        <?php endif; ?>
                    </section>

                    <?php if ($isAdmin): ?>
                        <!-- Admin Menu Items -->
                        <a href="../../pages/adminPages/adminIndex.php">Dashboard</a>
                        <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
                        <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                        <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                        <a href="../../pages/CommonPages/mainFAQ.html">FAQs</a>
                        <a href="../../pages/adminPages/aHelpTicket.php">Help</a>
                    <?php else: ?>
                        <!-- Member Menu Items -->
                        <a href="../../pages/MemberPages/memberIndex.php">Home</a>
                        <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
                        <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                        <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                        <a href="../../pages/CommonPages/aboutUs.html">About</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <!-- Menu Links Desktop + Tablet -->
        <nav class="c-navbar-desktop">
            <?php if ($isAdmin): ?>
                <!-- Admin Desktop Menu -->
                <a href="../../pages/adminPages/adminIndex.php">Dashboard</a>
                <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
                <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                <a href="../../pages/CommonPages/mainFAQ.html">FAQs</a>
                <a href="../../pages/adminPages/aHelpTicket.php">Help</a>
            <?php else: ?>
                <!-- Member Desktop Menu -->
                <a href="../../pages/MemberPages/memberIndex.php">Home</a>
                <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
                <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                <a href="../../pages/CommonPages/aboutUs.html">About</a>
            <?php endif; ?>
        </nav>

        <section class="c-navbar-more">
            <input type="text" placeholder="Search..." id="searchBar" class="search-bar">
            <button id="themeToggle2">
                <img src="../../assets/images/light-mode-icon.svg" alt="Light Mode Icon">
            </button>

            <?php if ($isAdmin): ?>
                <!-- Admin Navbar More -->
                <a href="../../pages/adminPages/aProfile.html">
                    <img src="../../assets/images/profile-light.svg" alt="Profile" id="profileImg">
                </a>
            <?php else: ?>
                <!-- Member Navbar More -->
                <a href="../../pages/MemberPages/mChat.html" class="c-chatbox" id="chatboxDesktop">
                    <img src="../../assets/images/chat-light.svg" alt="Chatbox" id="chatImg">
                    <span class="c-notification-badge" id="chatBadgeDesktop"></span>
                </a>
                <a href="../../pages/MemberPages/mSetting.html">
                    <img src="../../assets/images/setting-light.svg" alt="Settings" id="settingImg">
                </a>
            <?php endif; ?>
        </section>
    </header>
    <hr>

    <!-- Main Content -->
    <main>
        <section class="content" id="content">
            <div class="event-detail-container">
                <a href="mainEvent.php" class="back-button">← Back to Events</a>

                <?php if ($registrationSuccess): ?>
                    <div class="alert alert-success">
                        Registration successful! You are now registered for this event.
                    </div>
                <?php elseif ($registrationMessage && !$registrationSuccess): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($registrationMessage); ?>
                    </div>
                <?php endif; ?>

                <!-- Event Header -->
                <div class="event-header">
                    <?php
                    if (!empty($event['bannerFilePath'])): ?>
                        <img src="<?php echo htmlspecialchars($event['bannerFilePath']); ?>" 
                            alt="<?php echo htmlspecialchars($event['title']); ?>" 
                            class="event-banner" 
                            onerror="this.src='../../assets/images/Logo.png'; this.classList.add('event-banner-placeholder')">
                    <?php else: ?>
                        <img src="../../assets/images/Logo.png" 
                            alt="<?php echo htmlspecialchars($event['title']); ?>" 
                            class="event-banner event-banner-placeholder">
                    <?php endif; ?>
                    <div class="event-header-content">
                        <h1 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h1>
                        
                        <div class="event-meta">
                            <div class="event-meta-item">
                                <span class="event-meta-icon">📅</span>
                                <span>
                                    <?php 
                                    if ($startDate != $endDate){
                                        echo $startDate . " - " . $endDate;
                                    } else {
                                        echo $startDate;
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="event-meta-item">
                                <span class="event-meta-icon">🕐</span>
                                <span><?php echo $time; ?></span>
                            </div>
                            <div class="event-meta-item">
                                <span class="event-meta-icon">📍</span>
                                <span><?php echo htmlspecialchars($event['location'] . ", " . $event['country']); ?></span>
                            </div>
                        </div>

                        <div class="event-host">
                            <div class="event-host-avatar"><?php echo $hostInitial; ?></div>
                            <div class="event-host-info">
                                <h4>Hosted by <span><?php echo htmlspecialchars($event['hostName']); ?></span></h4>
                                <p><?php echo htmlspecialchars($event['hostEmail']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Event Content -->
                <div class="event-content">
                    <!-- Main Content -->
                    <div class="event-main">
                        <div class="event-section">
                            <h3>About This Event</h3>
                            <p class="event-description">
                                <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                            </p>
                            <br><br>
                            <h3>Capacity</h3>
                            <p class="event-description">
                                --> <?php echo $event['maxPax']; ?> maximum participants
                            </p>
                            <br><br>
                            <h3>Event Mode</h3>
                            <p class="event-description">
                                --> <?php echo ucfirst($event['mode']); ?> Event
                            </p>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <aside class="event-sidebar">
                        <div class="event-card-sticky">
                            <div class="event-attendees">
                                <div class="event-attendees-count"><?php echo $attendeeCount; ?></div>
                                <div class="event-attendees-label">Registered Attendees</div>
                            </div>

                            <ul class="event-info-list">
                                <li class="event-info-item">
                                    <span class="event-info-label">Status</span>
                                    <span class="event-info-value">
                                        <?php echo ucfirst($event['status']); ?>
                                    </span>
                                </li>
                                <li class="event-info-item">
                                    <span class="event-info-label">Time Zone</span>
                                    <span class="event-info-value"><?php echo $event['timeZone']; ?></span>
                                </li>
                                <li class="event-info-item">
                                    <span class="event-info-label">Event Type</span>
                                    <span class="event-info-value"><?php echo ucwords(str_replace('-', ' ', $event['type'])); ?></span>
                                </li>
                                <li class="event-info-item">
                                    <span class="event-info-label">Duration</span>
                                    <span class="event-info-value"><?php echo $daysText; ?></span>
                                </li>
                            </ul>

                            <?php if ($isAdmin): ?>
                                <div class="warning-notice">
                                    <img src="../../assets/images/warning-icon.svg" alt=""> Admins cannot register for events
                                </div>
                                <button class="register-button" disabled>Admin Account</button>
                            <?php elseif ($isHost): ?>
                                <div class="warning-notice">
                                    <img src="../../assets/images/warning-icon.svg" alt=""> Host cannot register for own events
                                </div>
                                <button class="register-button" disabled>Host Account</button>
                            <?php elseif ($isClosed): ?>
                                <button class="register-button" disabled>Registration Ended</button>
                            <?php elseif ($isFull): ?>
                                <button class="register-button" disabled>Registration Full</button>
                            <?php elseif ($isRegistered): ?>
                                <button class="register-button registered" disabled>Already Registered</button>
                            <?php else: ?>
                                <button class="register-button" onclick="showConfirmation()">Register for Event</button>
                            <?php endif; ?>
                        </div>
                    </aside>
                </div>
            </div>
            <br>
        </section>

        <!-- Confirmation Modal -->
        <div class="modal-overlay" id="confirmModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Confirm Registration</h2>
                    <p class="modal-message">
                        You are about to register for this event. Please review your details below:
                    </p>
                </div>

                <div class="modal-details">
                    <div class="modal-detail-item">
                        <span class="modal-detail-label">Name:</span>
                        <span class="modal-detail-value" id="userName"><?php echo htmlspecialchars($userInfo['fullName']); ?></span>
                    </div>
                    <div class="modal-detail-item">
                        <span class="modal-detail-label">Email:</span>
                        <span class="modal-detail-value" id="userEmail"><?php echo htmlspecialchars($userInfo['email']); ?></span>
                    </div>
                    <div class="modal-detail-item">
                        <span class="modal-detail-label">Event:</span>
                        <span class="modal-detail-value" id="modalEventName"><?php echo htmlspecialchars($event['title']); ?></span>
                    </div>
                    <div class="modal-detail-item">
                        <span class="modal-detail-label">Date:</span>
                        <span class="modal-detail-value"><?php echo $startDate; ?></span>
                    </div>
                    <div class="modal-detail-item">
                        <span class="modal-detail-label">Time:</span>
                        <span class="modal-detail-value"><?php echo $time; ?></span>
                    </div>
                </div>

                <div class="modal-actions">
                    <button class="modal-btn modal-btn-cancel" onclick="hideConfirmation()">Cancel</button>
                    <form method="POST" id="registrationForm" style="display: none;">
                        <input type="hidden" name="register" value="1">
                    </form>
                    <button class="modal-btn modal-btn-confirm" onclick="confirmRegistration()">Confirm Registration</button>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <div class="modal-overlay" id="successModal">
            <div class="modal-content success-modal">
                <div class="modal-header">
                    <h2 class="modal-title">Registration Successful!</h2>
                    <p class="modal-message">
                        You have successfully registered for <strong id="successEventName"><?php echo htmlspecialchars($event['title']); ?></strong>. 
                        A confirmation email has been sent to <strong id="successUserEmail"><?php echo htmlspecialchars($userInfo['email']); ?></strong>.
                    </p>
                </div>

                <div class="modal-actions">
                    <button class="modal-btn modal-btn-confirm" onclick="closeSuccess()">Got it!</button>
                </div>
            </div>
        </div>

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
    <?php endif; ?>

    <script>const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;</script>
    <script src="../../javascript/mainScript.js"></script>
    <script>
        // Modal functions
        function showConfirmation() {
            document.getElementById('confirmModal').classList.add('active');
        }

        function hideConfirmation() {
            document.getElementById('confirmModal').classList.remove('active');
        }

        function confirmRegistration() {
            // Submit the form programmatically
            document.getElementById('registrationForm').submit();
        }

        function closeSuccess() {
            document.getElementById('successModal').classList.remove('active');
        }

        // Show success modal if registration was successful
        <?php if ($registrationSuccess): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('successModal').classList.add('active');
        });
        <?php endif; ?>

        // Close modals when clicking outside
        document.addEventListener('click', function(event) {
            const modals = document.querySelectorAll('.modal-overlay.active');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>

<?php
mysqli_close($connection);
?>