<?php
session_start();
include("../../php/dbConn.php");
include("../../php/sessionCheck.php");

$autoCloseQuery = "UPDATE tblevents SET status = 'closed' WHERE endDate < CURDATE() AND status NOT IN ('cancelled', 'closed')";
$connection->query($autoCloseQuery);

// Remove Participant from Hosted Event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'removeParticipant') {
    $eventID = intval($_POST['eventID']);
    $participantUserID = intval($_POST['userID']);
    
    // Verify that the logged-in user is the host of this event
    $verifyHostSql = "SELECT userID FROM tblevents WHERE eventID = ?";
    $stmt = $connection->prepare($verifyHostSql);
    $stmt->bind_param("i", $eventID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Event not found.";
        header("Location: myEvents.php");
        exit();
    }
    
    $event = $result->fetch_assoc();
    
    if ($event['userID'] != $userID) {
        $_SESSION['error'] = "You are not authorized to remove participants from this event.";
        header("Location: myEvents.php");
        exit();
    }

    // Remove the participant by updating their registration status to 'cancelled'
    $removeSql = "UPDATE tblregistration SET status = 'cancelled' WHERE eventID = ? AND userID = ?";
    $stmt2 = $connection->prepare($removeSql);
    $stmt2->bind_param("ii", $eventID, $participantUserID);
    
    if ($stmt2->execute()) {
        if ($stmt2->affected_rows > 0) {
            $_SESSION['success'] = "Participant removed successfully.";
        } else {
            $_SESSION['error'] = "Participant not found or already removed.";
        }
    } else {
        $_SESSION['error'] = "Failed to remove participant. Please try again.";
    }
    
    $stmt2->close();
    $stmt->close();
    
    header("Location: myEvents.php");
    exit();
}

// Cancel Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancelRegistration') {
    $eventID = intval($_POST['eventID']);
    $userID = $_SESSION['userID'];

    // Verify the user is actually registered for this event
    $verifyRegistrationSql = "SELECT * FROM tblregistration WHERE eventID = ? AND userID = ? AND status = 'active'";
    $stmt = $connection->prepare($verifyRegistrationSql);
    $stmt->bind_param("ii", $eventID, $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Registration not found or already cancelled.";
        header("Location: myEvents.php");
        exit();
    }

    // Check if event has already started
    $eventSql = "SELECT startDate FROM tblevents WHERE eventID = ?";
    $stmt2 = $connection->prepare($eventSql);
    $stmt2->bind_param("i", $eventID);
    $stmt2->execute();
    $eventResult = $stmt2->get_result();
    $event = $eventResult->fetch_assoc();
    
    $eventStartTime = strtotime($event['startDate']);
    $currentTime = time();
    
    if ($eventStartTime < $currentTime) {
        $_SESSION['error'] = "Cannot cancel registration for an event that has already started.";
        header("Location: myEvents.php");
        exit();
    }

    // Cancel the registration by updating status to 'cancelled'
    $cancelSql = "UPDATE tblregistration SET status = 'cancelled' WHERE eventID = ? AND userID = ? AND status = 'active'";
    $stmt3 = $connection->prepare($cancelSql);
    $stmt3->bind_param("ii", $eventID, $userID);
    
    if ($stmt3->execute()) {
        if ($stmt3->affected_rows > 0) {
            $_SESSION['success'] = "Registration cancelled successfully.";
        } else {
            $_SESSION['error'] = "Failed to cancel registration. Please try again.";
        }
    } else {
        $_SESSION['error'] = "Database error. Please try again.";
    }

    $stmt3->close();
    $stmt2->close();
    $stmt->close();
    
    header("Location: myEvents.php");
    exit();
}

// Delete Hosted Event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'deleteEvent') {
    $eventID = intval($_POST['eventID']);
    
    // Verify that the logged-in user is the host of this event
    $verifyHostSql = "SELECT userID FROM tblevents WHERE eventID = ?";
    $stmt = $connection->prepare($verifyHostSql);
    $stmt->bind_param("i", $eventID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Event not found.";
        header("Location: myEvents.php");
        exit();
    }
    
    $event = $result->fetch_assoc();
    
    if ($event['userID'] != $userID) {
        $_SESSION['error'] = "You are not authorized to delete this event.";
        header("Location: myEvents.php");
        exit();
    }

    // Delete the event banner file if it exists
    $fileDeleted = false;
    if (!empty($event['bannerFilePath']) && file_exists($event['bannerFilePath'])) {
        if (unlink($event['bannerFilePath'])) {
            $fileDeleted = true;
        } else {
            error_log("Failed to delete event banner: " . $event['bannerFilePath']);
            // Continue with deletion
        }
    }

    // Delete the event
    $deleteSql = "DELETE FROM tblevents WHERE eventID = ?";
    $stmt2 = $connection->prepare($deleteSql);
    $stmt2->bind_param("i", $eventID);
    
    if ($stmt2->execute()) {
        $_SESSION['success'] = "Event deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete event. Please try again.";
    }
    
    $stmt2->close();
    $stmt->close();
    
    header("Location: myEvents.php");
    exit();
}
    
// Fetch Registered Events 
$search = $_GET['searchRegistered'] ?? '';
$registeredSql = "
    SELECT r.*, e.*
    FROM tblregistration r
    JOIN tblevents e ON r.eventID = e.eventID
    WHERE r.userID = ? AND r.status = 'active' AND e.title LIKE ?
    ORDER BY e.startDate DESC
";

// Check if prepare was successful
$stmt_registered = $connection->prepare($registeredSql);
if ($stmt_registered === false) {
    die("Error preparing registered events query: " . $connection->error);
}

$searchParam = "%$search%";
$stmt_registered->bind_param("is", $userID, $searchParam);

if (!$stmt_registered->execute()) {
    die("Error executing registered events query: " . $stmt_registered->error);
}

$registeredEvents = $stmt_registered->get_result();

// Fetch Hosted Events 
$searchHost = $_GET['searchHosted'] ?? '';
$hostedSql = "
    SELECT * FROM tblevents
    WHERE userID = ? AND title LIKE ?
    ORDER BY startDate DESC
";

// Check if prepare was successful
$stmt_hosted = $connection->prepare($hostedSql);
if ($stmt_hosted === false) {
    die("Error preparing hosted events query: " . $connection->error);
}

$searchHostParam = "%$searchHost%";
$stmt_hosted->bind_param("is", $userID, $searchHostParam);

if (!$stmt_hosted->execute()) {
    die("Error executing hosted events query: " . $stmt_hosted->error);
}

$hostedEvents = $stmt_hosted->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events - ReLeaf</title>
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">
    <link rel="stylesheet" href="../../style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <style>
        /* Back Button */
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

        /* alert */
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            font-weight: 600;
            background: ;
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

        .my-events-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .my-events-header {
            margin-top: 1rem;
            margin-bottom: 2rem;
        }

        .my-events-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-color);
        }

        /* Tabs Styling */
        .event-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--Gray);
        }

        .event-tab-btn {
            padding: 1rem 2rem;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            color: var(--Gray);
            transition: all 0.3s ease;
            position: relative;
            margin-bottom: -2px;
        }

        .event-tab-btn:hover {
            transform: scale(1.05);
        }

        .event-tab-btn.active {
            color: var(--MainGreen);
            border-bottom-color: var(--MainGreen);
        }

        /* Tab Content */
        .event-tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .event-tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Search Bar */
        .event-search-form {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }

        .event-search-input {
            flex: 1;
            transition: all 0.3s ease;
        }

        .event-search-input:focus {
            outline: none;
            border-color: var(--MainGreen);
            box-shadow: 0 0 0 3px var(--shadow-color);
        }

        .event-search-btn {
            padding: 0.875rem 2rem;
            background: var(--MainGreen);
            color: var(--White);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .event-search-btn:hover {
            opacity: 0.6;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--shadow-color);
        }

        /* Event List */
        .event-list {
            display: grid;
            gap: 1.5rem;
        }

        /* Event Card */
        .event-card {
            background: var(--bg-color);
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
            transition: all 0.3s ease;
        }

        .event-card:hover {
            border-color: var(--MainGreen);
            box-shadow: 0 8px 24px var(--shadow-color);
            transform: translateY(-3px);
        }

        .event-card-info h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .event-card-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--DarkerGray);
            margin-bottom: 0.25rem;
            font-size: 0.95rem;
        }

        .dark-mode .event-card-detail {
            color: var(--Gray);
        }

        .event-status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: var(--Red);
            color: var(--White);
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .event-card-actions {
            display: flex;
            gap: 0.75rem;
            flex-shrink: 0;
        }

        .event-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .event-btn-view {
            background: var(--MainGreen);
            color: var(--White);
        }

        .event-btn-view:hover {
            opacity: 0.6;
        }

        .event-btn-cancel {
            background: var(--Red);
            color: var(--White);
        }

        .event-btn-cancel:hover:not(:disabled) {
            opacity: 0.6;
        }

        .event-btn-cancel:disabled {
            background: var(--Gray);
            cursor: not-allowed;
            opacity: 0.5;
        }

        /* Hosted Events Dropdown */
        .hosted-event-item {
            background: var(--bg-color);
            border: 2px solid var(--Gray);
            border-radius: 16px;
            margin-bottom: 1.25rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .hosted-event-item:hover {
            border-color: var(--MainGreen);
            box-shadow: 0 4px 16px var(--shadow-color);
        }

        .hosted-event-header {
            width: 100%;
            padding: 1.5rem;
            background: var(--bg-color);
            border: none;
            cursor: pointer;
            text-align: left;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .hosted-event-header:hover {
            opacity: 0.6;
        }

        .hosted-event-header.active {
            color: var(--MainGreen);
        }

        .hosted-event-arrow {
            transition: transform 0.3s ease;
            font-size: 1.25rem;
        }

        .hosted-event-arrow img{
            content: url(../../assets/images/dropdown-icon-light.svg);
            width: 24px;
            height: 24px;
        }

        .dark-mode .hosted-event-arrow img{
            content: url(../../assets/images/dropdown-icon-dark.svg);
        }

        .hosted-event-header.active .hosted-event-arrow {
            transform: rotate(180deg);
        }

        .hosted-event-actions {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .hosted-action-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .hosted-action-btn-view {
            background: var(--MainGreen);
            color: var(--White);
        }

        .hosted-action-btn-view:hover {
            opacity: 0.6;
        }

        .hosted-action-btn-delete {
            background: var(--Red);
            color: var(--White);
        }

        .hosted-action-btn-delete:hover {
            opacity: 0.6;
        }

        .hosted-event-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            padding: 0 1.5rem;
        }

        .hosted-event-content.show {
            max-height: 2000px;
            padding: 1.5rem;
        }

        .participant-count {
            display: block;
            color: var(--text-color);
            font-size: 0.95rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        /* Participant Search */
        .participant-search-input {
            width: 100%;
            margin-bottom: 1rem;
            border: 2px solid var(--Gray);
            border-radius: 10px;
            font-size: 0.95rem;
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        .participant-search-input:focus {
            outline: none;
            border-color: var(--MainGreen);
            box-shadow: 0 0 0 3px var(--shadow-color);
        }

        /* Participant Table */
        .participant-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--bg-color);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px var(--LowGreen);
        }

        .participant-table thead {
            background: var(--LowGreen);
            color: var(--White);
        }

        .participant-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .participant-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--Gray);
            color: var(--text-color);
        }

        .participant-table tbody tr {
            transition: background-color 0.2s ease;
        }

        .participant-table tbody tr:hover {
            background: var(--sec-bg-color);
        }

        .dark-mode .participant-table tbody tr:hover {
            background: var(--DarkerGray);
        }

        .participant-table tbody tr:last-child td {
            border-bottom: none;
        }

        .participant-actions {
            display: flex;
            gap: 0.5rem;
        }

        .participant-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .participant-btn-view {
            background: var(--MainGreen);
            color: var(--White);
        }

        .participant-btn-view:hover {
            opacity: 0.6;
        }

        .participant-btn-remove {
            background: var(--Red);
            color: var(--White);
        }

        .participant-btn-remove:hover {
            opacity: 0.6;
        }

        .participant-btn-remove:disabled {
            background: var(--Gray);
            cursor: not-allowed;
            opacity: 0.5;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-color);
        }

        .hosted-event-item .empty-state {
            padding: 2rem;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }

        .empty-state p {
            font-size: 1rem;
            color: var(--Gray);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .my-events-container {
                padding: 0 1rem;
            }

            .my-events-header h1 {
                font-size: 2rem;
            }

            .event-tab-btn {
                padding: 0.75rem 1.25rem;
                font-size: 0.95rem;
            }

            .event-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .event-card-actions {
                width: 100%;
            }

            .event-btn {
                flex: 1;
            }

            .participant-table {
                font-size: 0.85rem;
            }

            .participant-table th,
            .participant-table td {
                padding: 0.75rem 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .event-search-form {
                flex-direction: column;
            }

            .event-search-btn {
                width: 100%;
            }

            .participant-table {
                display: block;
                overflow-x: auto;
            }

            .hosted-event-header {
                font-size: 1rem;
                padding: 1rem;
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
                            <a href="../../pages/MemberPages/mSetting.html">
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
                <a href="../../pages/MemberPages/mSetting.html">
                    <img src="../../assets/images/setting-light.svg" alt="Settings" id="settingImg">
                </a>
            <?php endif; ?>
        </section>
    </header>
    <hr>
    <main class="content" id="content">
        <section class="my-events-container">
            <a href="mainEvent.php" class="back-button">← Back to Events</a>
            <?php
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                unset($_SESSION['success']);
            }
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-error">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            ?>
            <div class="my-events-header">
                <h1>My Events</h1>
            </div>

            <!-- Tabs -->
            <div class="event-tabs">
                <button class="event-tab-btn active" data-tab="registered">
                    📅 Registered Events
                </button>
                <button class="event-tab-btn" data-tab="hosted">
                    🎯 Hosted Events
                </button>
            </div>

            <!-- Registered Events Tab -->
            <div id="registered" class="event-tab-content active">
                <form method="GET" class="event-search-form">
                    <input 
                        type="text" 
                        name="searchRegistered" 
                        class="c-input event-search-input"
                        placeholder="Search registered events..." 
                        value="<?php echo htmlspecialchars($search); ?>"
                    >
                    <button type="submit" class="event-search-btn">Search</button>
                </form>

                <div class="event-list">
                    <?php if ($registeredEvents->num_rows === 0): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📅</div>
                            <h3>No registered events found</h3>
                            <p>Start exploring and register for exciting events!</p>
                        </div>
                    <?php else: ?>
                        <?php while ($event = $registeredEvents->fetch_assoc()): 
                            $isPast = strtotime($event['endDate']) < time();
                        ?>
                        <div class="event-card">
                            <div class="event-card-info">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <div class="event-card-detail">
                                    📍 <?php echo htmlspecialchars($event['location']); ?>
                                </div>
                                <div class="event-card-detail">
                                    📅 <?php echo date('M d, Y', strtotime($event['startDate'])); ?> - 
                                    <?php echo date('M d, Y', strtotime($event['endDate'])); ?>
                                </div>
                                <?php if ($isPast): ?>
                                    <span class="event-status-badge">• Event Ended</span>
                                <?php endif; ?>
                            </div>
                            <div class="event-card-actions">
                                <a href="joinEvent.php?id=<?php echo $event['eventID']; ?>" class="event-btn event-btn-view">
                                    View Event
                                </a>
                                <form method="POST" action="myEvents.php" style="display:inline;">
                                    <input type="hidden" name="action" value="cancelRegistration">
                                    <input type="hidden" name="eventID" value="<?php echo $event['eventID']; ?>">
                                    <button type="submit" class="event-btn event-btn-cancel" 
                                        <?php echo $isPast ? 'disabled' : ''; ?>
                                        onclick="return confirm('Are you sure you want to cancel this registration?');">
                                        Cancel Registration
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Hosted Events Tab -->
            <div id="hosted" class="event-tab-content">
                <form method="GET" class="event-search-form">
                    <input type="text" name="searchHosted" class="c-input event-search-input"placeholder="Search hosted events..." 
                        value="<?php echo htmlspecialchars($searchHost); ?>">
                    <button type="submit" class="event-search-btn">Search</button>
                </form>

                <div class="event-list">
                    <?php if ($hostedEvents->num_rows === 0): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">🎯</div>
                            <h3>No hosted events found</h3>
                            <p>Create your first event and start hosting!</p>
                        </div>
                    <?php else: ?>
                        <?php while ($event = $hostedEvents->fetch_assoc()): ?>
                            <div class="hosted-event-item">
                                <button class="hosted-event-header">
                                    <span>
                                        <?php echo htmlspecialchars($event['title']); ?> 
                                        (<?php echo date('M d, Y', strtotime($event['startDate'])); ?>)
                                        - <?php echo ($event['status']); ?>
                                    </span>
                                    <span class="hosted-event-arrow">
                                        <img src="../../assets/images/dropdown-icon-light.svg" alt="dropdown">
                                    </span>
                                </button>
                                <div class="hosted-event-content">
                                    <?php
                                        $participantsSql = "
                                            SELECT u.userID, u.fullName, u.email
                                            FROM tblregistration r
                                            JOIN tblusers u ON r.userID = u.userID
                                            WHERE r.eventID = ? AND r.status = 'active'
                                            ORDER BY u.fullName ASC
                                        ";
                                        $stmt3 = $connection->prepare($participantsSql);
                                        $stmt3->bind_param("i", $event['eventID']);
                                        $stmt3->execute();
                                        $participants = $stmt3->get_result();
                                        $participantCount = $participants->num_rows;
                                    ?>

                                    <div class="hosted-event-actions">
                                            <a href="joinEvent.php?id=<?php echo $event['eventID']; ?>" 
                                                class="hosted-action-btn hosted-action-btn-view">
                                                View Event
                                            </a>
                                            <form method="POST" action="myEvents.php" style="display:inline;" 
                                                onsubmit="return confirm(
                                                'Are you sure you want to delete this event? This action cannot be undone and will remove all participant registrations.');">
                                                <input type="hidden" name="action" value="deleteEvent">
                                                <input type="hidden" name="eventID" value="<?php echo $event['eventID']; ?>">
                                                <button type="submit" class="hosted-action-btn hosted-action-btn-delete">
                                                    Delete Event
                                                </button>
                                            </form>
                                        </div>

                                        <?php if ($participantCount > 0): ?>
                                            <span class="participant-count">
                                                Total Participants: <strong><?php echo $participantCount; ?></strong>
                                            </span>
                                            <input type="text" class="c-input participant-search-input" 
                                                placeholder="Search participants by name or email...">
                                        
                                        <table class="participant-table">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php while ($participant = $participants->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($participant['fullName']); ?></td>
                                                    <td><?php echo htmlspecialchars($participant['email']); ?></td>
                                                    <td>
                                                        <div class="participant-actions">
                                                            <a href="../../pages/CommonPages/viewProfile.php?userID=<?php echo $participant['userID']; ?>" 
                                                                class="participant-btn participant-btn-view">
                                                                View
                                                            </a>
                                                            <form method="POST" action="myEvents.php" style="display:inline;" 
                                                                onsubmit="return confirm('Are you sure you want to remove this participant?');">
                                                                <input type="hidden" name="action" value="removeParticipant">
                                                                <input type="hidden" name="eventID" value="<?php echo $event['eventID']; ?>">
                                                                <input type="hidden" name="userID" value="<?php echo $participant['userID']; ?>">
                                                                <button type="submit" class="participant-btn participant-btn-remove" 
                                                                    <?php echo ($event['status'] == 'closed') ? 'disabled' : ''; ?>
                                                                    <?php if ($event['status'] == 'closed'): ?>
                                                                        title="Cannot remove participants from closed events"
                                                                    <?php endif; ?>>
                                                                    Remove
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    <?php else: ?>
                                        <div class="empty-state">
                                            <p>No participants registered yet</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
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
                <a href="../../pages/MemberPages/mChat.html">My Chat</a><br>
                <a href="../../pages/MemberPages/mSetting.html">Settings</a>
            </div>
            <div>
                <b>Helps</b><br>
                <a href="../../pages/CommonPages/aboutUs.php">Contact</a><br>
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
    <?php endif; ?>

    <script>const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;</script>
    <script src="../../javascript/mainScript.js"></script>
    <script>
        // Tab switching
        const tabBtns = document.querySelectorAll('.event-tab-btn');
        const tabContents = document.querySelectorAll('.event-tab-content');
        
        // Function to switch tabs
        function switchTab(tabId) {
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            const selectedBtn = document.querySelector(`[data-tab="${tabId}"]`);
            const selectedContent = document.getElementById(tabId);
            
            if (selectedBtn && selectedContent) {
                selectedBtn.classList.add('active');
                selectedContent.classList.add('active');
                
                localStorage.setItem('activeEventTab', tabId);
            }
        }
        
        // Initialize tabs on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTab = localStorage.getItem('activeEventTab') || 'registered';
            
            switchTab(savedTab);
            tabBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const tabId = btn.dataset.tab;
                    switchTab(tabId);
                });
            });
        });

        // Dropdown toggle for hosted events
        document.querySelectorAll('.hosted-event-header').forEach(btn => {
            btn.addEventListener('click', function() {
                this.classList.toggle('active');
                const content = this.nextElementSibling;
                content.classList.toggle('show');
            });
        });

        // Participant search functionality
        document.querySelectorAll('.participant-search-input').forEach(searchInput => {
            searchInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                const table = this.nextElementSibling;
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.innerText.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                });
            });
        });
    </script>
</body>
</html>