<?php
session_start();
include("../../php/dbConn.php");
include("../../php/sessionCheck.php");

// Check if user is admin
if ($_SESSION['userType'] !== 'admin') {
    header("Location: ../../pages/memberPages/memberIndex.php");
    exit();
}

$autoCloseQuery = "UPDATE tblevents SET status = 'closed' WHERE endDate < CURDATE() AND status NOT IN ('cancelled', 'closed')";
$connection->query($autoCloseQuery);

// Handle event deletion
if (isset($_POST['delete_event'])) {
    $eventIDToDelete = $_POST['event_id'];
    
    $connection->begin_transaction();
    
    try {
        // Get event banner path before deletion
        $bannerQuery = "SELECT bannerFilePath FROM tblevents WHERE eventID = ?";
        $bannerStmt = $connection->prepare($bannerQuery);
        $bannerStmt->bind_param("i", $eventIDToDelete);
        $bannerStmt->execute();
        $bannerResult = $bannerStmt->get_result();
        $eventData = $bannerResult->fetch_assoc();
        $bannerStmt->close();
        
        // Delete banner file if exists
        if (!empty($eventData['bannerFilePath']) && file_exists($eventData['bannerFilePath'])) {
            if (!unlink($eventData['bannerFilePath'])) {
                error_log("Failed to delete event banner: " . $eventData['bannerFilePath']);
            }
        }
        
        $deletionOperations = [
            "DELETE FROM tblregistration WHERE eventID = ?",
            "DELETE FROM tblevents WHERE eventID = ?"
        ];
        
        foreach ($deletionOperations as $sql) {
            $stmt = $connection->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . $connection->error);
            }
            
            $stmt->bind_param("i", $eventIDToDelete);
            $result = $stmt->execute();
            
            if (!$result) {
                throw new Exception("Failed to execute deletion: " . $stmt->error);
            }
            $stmt->close();
        }
        
        $connection->commit();
        $_SESSION['success'] = "Event and all associated data deleted successfully!";
        
    } catch (Exception $e) {
        $connection->rollback();
        $_SESSION['error'] = "Error deleting event: " . $e->getMessage();
        error_log("Event deletion error for eventID $eventIDToDelete: " . $e->getMessage());
    }
    
    header("Location: aManageEvent.php");
    exit();
}

// Remove Participant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'removeParticipant') {
    $eventID = intval($_POST['eventID']);
    $participantUserID = intval($_POST['userID']);
    
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
    header("Location: aManageEvent.php");
    exit();
}

// Fetch All Events
$searchHost = $_GET['searchHosted'] ?? '';
$allEventsSql = "
    SELECT e.*, u.fullName as hostName, u.username as hostUsername
    FROM tblevents e
    JOIN tblusers u ON e.userID = u.userID
    WHERE e.title LIKE ?
    ORDER BY e.startDate DESC
";

$stmt_all = $connection->prepare($allEventsSql);
if ($stmt_all === false) {
    die("Error preparing events query: " . $connection->error);
}

$searchHostParam = "%$searchHost%";
$stmt_all->bind_param("s", $searchHostParam);

if (!$stmt_all->execute()) {
    die("Error executing events query: " . $stmt_all->error);
}

$allEvents = $stmt_all->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - ReLeaf</title>
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">
    <link rel="stylesheet" href="../../style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <style>
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 1.5rem;
            transition: all 0.2s ease;
        }

        .back-button:hover {
            color: var(--MainGreen);
            text-decoration: underline;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            font-weight: 600;
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

        .event-list {
            display: grid;
            gap: 1.5rem;
        }

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

        .hosted-event-arrow img {
            content: url(../../assets/images/dropdown-icon-light.svg);
            width: 24px;
            height: 24px;
        }

        .dark-mode .hosted-event-arrow img {
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

        .event-host-info {
            display: block;
            color: var(--Gray);
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .participant-count {
            display: block;
            color: var(--text-color);
            font-size: 0.95rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

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

        @media (max-width: 768px) {
            .my-events-container {
                padding: 0 1rem;
            }

            .my-events-header h1 {
                font-size: 2rem;
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

    <main class="content" id="content">
        <section class="my-events-container">
            <a href="../../pages/CommonPages/mainEvent.php" class="back-button">← Back to Dashboard</a>
            
            <?php
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            ?>

            <div class="my-events-header">
                <h1>Manage Events</h1>
            </div>

            <form method="GET" class="event-search-form">
                <input type="text" name="searchHosted" class="c-input event-search-input"
                    placeholder="Search events by title..." 
                    value="<?php echo htmlspecialchars($searchHost); ?>">
                <button type="submit" class="event-search-btn">Search</button>
            </form>

            <div class="event-list">
                <?php if ($allEvents->num_rows === 0): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">🎯</div>
                        <h3>No events found</h3>
                        <p>No events match your search criteria.</p>
                    </div>
                <?php else: ?>
                    <?php while ($event = $allEvents->fetch_assoc()): ?>
                        <div class="hosted-event-item">
                            <button class="hosted-event-header">
                                <span>
                                    <?php echo htmlspecialchars($event['title']); ?> 
                                    (<?php echo date('M d, Y', strtotime($event['startDate'])); ?>)
                                    - <?php echo ucfirst($event['status']); ?>
                                </span>
                                <span class="hosted-event-arrow">
                                    <img src="../../assets/images/dropdown-icon-light.svg" alt="dropdown">
                                </span>
                            </button>
                            <div class="hosted-event-content">
                                <span class="event-host-info">
                                    Hosted by: <strong><?php echo htmlspecialchars($event['hostName']); ?></strong> (@<?php echo htmlspecialchars($event['hostUsername']); ?>)
                                </span>

                                <?php
                                    $participantsSql = "
                                        SELECT u.userID, u.fullName, u.email
                                        FROM tblregistration r
                                        JOIN tblusers u ON r.userID = u.userID
                                        WHERE r.eventID = ? AND r.status = 'active'
                                        ORDER BY u.fullName ASC
                                    ";
                                    $stmt3 = $connection->prepare($participantsSql);
                                    if ($stmt3) {
                                         $stmt3->bind_param("i", $event['eventID']);
                                        $stmt3->execute();
                                        $participants = $stmt3->get_result();
                                        $participantCount = $participants->num_rows;
                                    }
                                ?>

                                <div class="hosted-event-actions">
                                    <a href="../../pages/CommonPages/joinEvent.php?id=<?php echo $event['eventID']; ?>" 
                                        class="hosted-action-btn hosted-action-btn-view">
                                        View Event
                                    </a>
                                    <form method="POST" action="aManageEvent.php" style="display:inline;" 
                                        onsubmit="return confirm('Are you sure you want to delete this event? This action cannot be undone and will remove all participant registrations.');">
                                        <input type="hidden" name="event_id" value="<?php echo $event['eventID']; ?>">
                                        <button type="submit" name="delete_event" class="hosted-action-btn hosted-action-btn-delete">
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
                                                        <form method="POST" action="aManageEvent.php" style="display:inline;" 
                                                            onsubmit="return confirm('Are you sure you want to remove this participant?');">
                                                            <input type="hidden" name="action" value="removeParticipant">
                                                            <input type="hidden" name="eventID" value="<?php echo $event['eventID']; ?>">
                                                            <input type="hidden" name="userID" value="<?php echo $participant['userID']; ?>">
                                                            <button type="submit" class="participant-btn participant-btn-remove">
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
        </section>
    </main>

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
    <script>
        // Dropdown toggle for events
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

        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        if (alerts.length > 0) {
            setTimeout(function() {
                alerts.forEach(alert => {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                });
            }, 5000);
        }
    </script>
</body>
</html>

<?php mysqli_close($connection); ?>