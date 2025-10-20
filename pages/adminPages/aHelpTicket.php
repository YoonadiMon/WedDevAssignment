<?php
session_start();
include("../../php/dbConn.php");

// For demo purposes, set admin user ID (in real app, this would come from session)
$_SESSION['admin_id'] = 1;
$admin_id = $_SESSION['admin_id'];

// Handle mark solved action
if (isset($_POST['mark_solved']) && isset($_POST['ticket_id'])) {
    $ticket_id = mysqli_real_escape_string($connection, $_POST['ticket_id']);
    
    // Check if admin can modify this ticket
    $check_query = "SELECT adminAssignedID, status FROM tbltickets WHERE ticketID = '$ticket_id'";
    $check_result = mysqli_query($connection, $check_query);
    
    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $ticket_data = mysqli_fetch_assoc($check_result);
        $assigned_admin = $ticket_data['adminAssignedID'];
        $current_status = $ticket_data['status'];
        
        // Check if admin can modify (either not assigned or assigned to this admin)
        if ($assigned_admin == NULL || $assigned_admin == $admin_id) {
            // Update ticket status to solved and assign admin if not already assigned
            $update_query = "UPDATE tbltickets SET 
                            status = 'solved', 
                            adminAssignedID = '$admin_id',
                            updatedAt = NOW(),
                            lastReplyAt = NOW()
                            WHERE ticketID = '$ticket_id'";
            
            if (mysqli_query($connection, $update_query)) {
                $success_message = "Ticket #$ticket_id marked as solved successfully!";
                
                // If ticket was not assigned, update adminAssignedID
                if ($assigned_admin == NULL) {
                    $assign_query = "UPDATE tbltickets SET adminAssignedID = '$admin_id' WHERE ticketID = '$ticket_id'";
                    mysqli_query($connection, $assign_query);
                }
            } else {
                $error_message = "Error updating ticket: " . mysqli_error($connection);
            }
        } else {
            $error_message = "Cannot modify ticket #$ticket_id. It is assigned to another admin.";
        }
    } else {
        $error_message = "Ticket not found.";
    }
}

// Handle reopen action
if (isset($_POST['reopen_ticket']) && isset($_POST['ticket_id'])) {
    $ticket_id = mysqli_real_escape_string($connection, $_POST['ticket_id']);
    
    // Check if admin can modify this ticket
    $check_query = "SELECT adminAssignedID FROM tbltickets WHERE ticketID = '$ticket_id'";
    $check_result = mysqli_query($connection, $check_query);
    
    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $ticket_data = mysqli_fetch_assoc($check_result);
        $assigned_admin = $ticket_data['adminAssignedID'];
        
        // Check if admin can modify (either not assigned or assigned to this admin)
        if ($assigned_admin == NULL || $assigned_admin == $admin_id) {
            // Update ticket status to open
            $update_query = "UPDATE tbltickets SET 
                            status = 'open', 
                            updatedAt = NOW()
                            WHERE ticketID = '$ticket_id'";
            
            if (mysqli_query($connection, $update_query)) {
                $success_message = "Ticket #$ticket_id reopened successfully!";
            } else {
                $error_message = "Error reopening ticket: " . mysqli_error($connection);
            }
        } else {
            $error_message = "Cannot modify ticket #$ticket_id. It is assigned to another admin.";
        }
    } else {
        $error_message = "Ticket not found.";
    }
}

// Fetch all tickets from database
$query = "SELECT * FROM tbltickets ORDER BY createdAt DESC";
$result = mysqli_query($connection, $query);

$tickets = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $tickets[] = $row;
    }
}

// Count tickets by status for stats
$stats = [
    'open' => 0,
    'pending' => 0,
    'urgent' => 0,
    'resolved' => 0
];

foreach ($tickets as $ticket) {
    switch ($ticket['status']) {
        case 'open':
            $stats['open']++;
            break;
        case 'in_progress':
            $stats['pending']++;
            break;
        case 'solved':
            $stats['resolved']++;
            break;
    }
    
    if ($ticket['priority'] === 'urgent') {
        $stats['urgent']++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Support Tickets</title>
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">

    <link rel="stylesheet" href="../../style/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <style>
        /* Add this to your CSS to ensure nothing is blocking clicks */
        .ticket-item {
            cursor: pointer;
            pointer-events: auto !important;
            z-index: 1 !important;
            position: relative !important;
        }

        .ticket-item * {
            pointer-events: auto !important;
        }

        .admin-tickets-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .admin-header {
            display: flex;
            justify-content: space-around;
            text-align: center;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--bg-color);
            border-radius: 8px;
            box-shadow: 0 2px 10px var(--shadow-color);
        }

        .admin-welcome h1 {
            font-size: 28px;
            margin-bottom: 5px;
            color: var(--text-heading);
        }

        .admin-welcome p {
            color: var(--Gray);
            font-size: 14px;
        }
        
        .success-message {
            display: flex;
            justify-content: space-between;
            background: var(--sec-bg-color); 
            color: white; 
            padding: 12px; 
            border-radius: 4px; 
            margin-bottom: 20px;
        }

        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--bg-color);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-open { color: var(--MainGreen); }
        .stat-pending { color: #f59e0b; }
        .stat-urgent { color: #ef4444; }
        .stat-resolved { color: var(--Gray); }

        .stat-label {
            font-size: 14px;
            color: var(--Gray);
        }

        .tickets-section {
            background: var(--bg-color);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .section-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-header h2 {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-heading);
        }

        .section-header .badge {
            background: var(--MainGreen);
            color: var(--White);
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .filters {
            display: flex;
            gap: 15px;
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            background: var(--sec-bg-color);
        }

        .filter-select {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background: var(--bg-color);
            color: var(--text-color);
            font-size: 14px;
        }

        .tickets-list {
            width: 100%;
            overflow-x: hidden;
        }

        .ticket-item {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.3s;
            display: flex;
            align-items: flex-start;
            gap: 15px;
            width: 100%;
            box-sizing: border-box;
        }

        .ticket-item:hover, .ticket-item.unread:hover {
            background: var(--btn-color-hover);
        }

        .ticket-item.unread {
            background: rgba(16, 185, 129, 0.05);
        }

        .ticket-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--MainGreen);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--White);
            font-weight: 600;
            font-size: 14px;
            flex-shrink: 0;
        }

        .ticket-content {
            flex: 1;
            min-width: 0;
        }

        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .ticket-title {
            font-weight: 600;
            color: var(--text-heading);
            margin-bottom: 5px;
            font-size: 15px;
        }

        .ticket-preview {
            max-width: 60%;
            color: var(--Gray);
            font-size: 14px;
            line-height: 1.4;
            margin-bottom: 8px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ticket-meta {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: var(--Gray);
        }

        .ticket-category {
            background-color: var(--sec-bg-color);
            color: var(--text-color);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
        }

        .ticket-priority {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .priority-low { background: #d1fae5; color: #065f46; }
        .priority-medium { background: #fef3c7; color: #92400e; }
        .priority-high { background: #fecaca; color: #991b1b; }
        .priority-urgent { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

        .ticket-id {
            color: var(--Gray);
            font-family: monospace;
        }

        .ticket-time {
            color: var(--Gray);
        }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: var(--Gray);
        }

        .empty-state p {
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .ticket-item {
                flex-direction: column;
                gap: 10px;
            }

            .ticket-header {
                flex-direction: column;
                gap: 10px;
            }

            .filters {
                flex-wrap: wrap;
            }
        }
        @media (max-width: 650px) {
            .ticket-item:hover, .ticket-item.unread:hover {
                background: var(--bg-color);
            }
            .tickets-section {
                display: block;
                margin: 15px 0;
            }

            .admin-header {
                padding: 15px;
                margin-bottom: 20px;
            }

            .admin-welcome h1 {
                font-size: 24px;
            }

            .admin-welcome p {
                font-size: 13px;
            }

            .stats-overview {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
                margin-bottom: 20px;
            }

            .stat-card {
                padding: 15px;
            }

            .stat-number {
                font-size: 24px;
            }

            .section-header {
                padding: 15px;
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }

            .section-header h2 {
                font-size: 18px;
            }

            .filters {
                flex-direction: column;
                gap: 10px;
                padding: 15px;
            }

            .filter-select {
                width: 100%;
                padding: 10px;
            }

            /* .tickets-list {
                max-height: 500px;
            } */

            .ticket-item {
                padding: 15px;
                flex-direction: column;
                gap: 12px;
                position: relative;
            }

            .ticket-avatar {
                width: 35px;
                height: 35px;
                font-size: 12px;
                position: absolute;
                top: 15px;
                left: 15px;
            }

            .ticket-content {
                margin-left: 45px;
                width: calc(100% - 45px);
            }

            .ticket-header {
                flex-direction: column;
                gap: 8px;
                margin-bottom: 8px;
            }

            .ticket-title {
                font-size: 14px;
                line-height: 1.3;
            }

            .ticket-preview {
                font-size: 13px;
                white-space: normal;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .ticket-meta {
                flex-wrap: wrap;
                gap: 8px;
                font-size: 11px;
            }

            .ticket-actions {
                width: 100%;
                margin-top: 10px;
            }

            .ticket-actions .c-btn {
                width: 100%;
                text-align: center;
            }

            .section-actions .c-text-helper,
            .ticket-time {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .admin-tickets-container {
                padding: 10px;
            }

            .stats-overview {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .ticket-meta {
                flex-direction: column;
                gap: 5px;
            }

            .ticket-priority,
            .ticket-category {
                align-self: flex-start;
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
                    <a href="../../pages/CommonPages/mainTrade.html">Trade</a>
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
            <a href="../../pages/CommonPages/mainTrade.html">Trade</a>
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
        
    </header>
    <hr>

    <!-- Main Content -->
    <main>
        <div class="admin-tickets-container">
            <!-- Admin Header -->
            <div class="admin-header">
                <div class="admin-welcome">
                    <h1>Hey Admin!</h1>
                    <p>Time to help the people who need us most</p>
                    <p><em>Click on a conversation to get started</em></p>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <?php if (isset($success_message)): ?>
                <div id="mssg" class="message success-message">
                    <?php echo $success_message; ?>
                    <button onclick="hideMssg()">
                        <b>X</b>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="message error-message" style="background: #f44336; color: white; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Stats Overview -->
            <div class="stats-overview">
                <div class="stat-card">
                    <div class="stat-number stat-open"><?php echo $stats['open']; ?></div>
                    <div class="stat-label">Open Tickets</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number stat-pending"><?php echo $stats['pending']; ?></div>
                    <div class="stat-label">In Progress</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number stat-urgent"><?php echo $stats['urgent']; ?></div>
                    <div class="stat-label">Urgent</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number stat-resolved"><?php echo $stats['resolved']; ?></div>
                    <div class="stat-label">Resolved</div>
                </div>
            </div>

            <!-- Help Tickets Section -->
            <div class="tickets-section">
                <div class="section-header">
                    <h2>Help Tickets: All Tickets <span class="badge"><?php echo count($tickets); ?></span></h2>
                    <div class="section-actions">
                        <span class="c-text-helper">Total: <?php echo count($tickets); ?> tickets</span>
                    </div>
                </div>

                <div class="filters">
                    <select class="filter-select" id="categoryFilter">
                        <option value="all">All Categories</option>
                        <option value="technical">Technical</option>
                        <option value="account">Account</option>
                        <option value="billing">Billing</option>
                        <option value="feature">Feature</option>
                        <option value="bug">Bug</option>
                        <option value="general">General</option>
                        <option value="other">Other</option>
                    </select>
                    <select class="filter-select" id="priorityFilter">
                        <option value="all">All Priorities</option>
                        <option value="urgent">Urgent</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                    <select class="filter-select" id="statusFilter">
                        <option value="all">All Status</option>
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="solved">Solved</option>
                    </select>
                </div>

                <div class="tickets-list" id="helpTicketsList">
                    <?php if (empty($tickets)): ?>
                        <div class="empty-state">
                            <p>No tickets found in the database</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($tickets as $ticket): 
                            $canModify = ($ticket['adminAssignedID'] == NULL || $ticket['adminAssignedID'] == $admin_id);
                            $isSolved = ($ticket['status'] === 'solved');
                        ?>
                            <div class="ticket-item <?php echo $ticket['isUnread'] ? 'unread' : ''; ?>" data-ticket-id="<?php echo $ticket['ticketID']; ?>">
                                <div class="ticket-avatar">
                                    <?php 
                                    // Get initials from username
                                    $initials = '';
                                    if (!empty($ticket['username'])) {
                                        $names = explode(' ', $ticket['username']);
                                        $initials = substr($names[0], 0, 1);
                                        if (count($names) > 1) {
                                            $initials .= substr($names[1], 0, 1);
                                        }
                                    } else {
                                        $initials = 'U';
                                    }
                                    echo strtoupper($initials);
                                    ?>
                                </div>
                                <div class="ticket-content">
                                    <div class="ticket-header">
                                        <div>
                                            <div class="ticket-title"><?php echo htmlspecialchars($ticket['subject']); ?></div>
                                            <div class="ticket-preview"><?php echo htmlspecialchars($ticket['description']); ?></div>
                                        </div>
                                    </div>
                                    <div class="ticket-meta">
                                        <span class="ticket-category"><?php echo formatCategory($ticket['category']); ?></span>
                                        <span class="ticket-priority priority-<?php echo $ticket['priority']; ?>"><?php echo formatPriority($ticket['priority']); ?></span>
                                        <span class="ticket-id">#<?php echo $ticket['ticketID']; ?></span>
                                        <span class="ticket-time"><?php echo formatTime($ticket['createdAt']); ?></span>
                                        <?php if ($ticket['adminAssignedID']): ?>
                                            <span class="ticket-assigned">Assigned to Admin #<?php echo $ticket['adminAssignedID']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="ticket-actions">
                                    <?php if ($canModify): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticketID']; ?>">
                                            <?php if ($isSolved): ?>
                                                <button type="submit" name="reopen_ticket" class="c-btn c-btn-primary c-btn-sm">
                                                    Reopen
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" name="mark_solved" class="c-btn c-btn-primary c-btn-sm">
                                                    Mark Solved
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    <?php else: ?>
                                        <button class="c-btn c-btn-disabled c-btn-sm" disabled title="Assigned to another admin">
                                            Assigned to Other
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
            <div class="c-text">ReLeaf Admin</div>
            <div class="c-text c-text-center">
                "Relief for the Planet, One Leaf at a Time."
                <br>
                "Together, We Can ReLeaf the Earth."
            </div>
            <div class="c-text c-text-label">
                Admin Support: +60 12 345 6789
            </div>
            <div class="c-text">
                admin@releaf.com
            </div>
        </section>
        
        <!-- Column 2 -->
        <section class="c-footer-links-section">
            <div>
                <b>Admin Panel</b><br>
                <a href="../../pages/adminPages/adminIndex.php">Dashboard</a><br>
                <a href="../../pages/adminPages/adminTickets.html">Support Tickets</a><br>
                <a href="../../pages/adminPages/adminUsers.html">User Management</a>
            </div>
            <div>
                <b>Content Management</b><br>
                <a href="../../pages/adminPages/adminEvents.html">Events</a><br>
                <a href="../../pages/adminPages/adminBlogs.html">Blogs</a><br>
                <a href="../../pages/adminPages/adminTrades.html">Trades</a>
            </div>
            <div>
                <b>System</b><br>
                <a href="../../pages/adminPages/adminSetting.html">Settings</a><br>
                <a href="../../pages/adminPages/adminReports.html">Reports</a><br>
                <a href="../../pages/CommonPages/mainFAQ.html">FAQs</a>
            </div>
        </section>
    </footer>

    <script>
        const isAdmin = true;
    </script>
    <script src="../../javascript/mainScript.js"></script>
    <script src="../../javascript/aHelpTickets.js"></script>
    <!-- <script>
        // Temporary debug function
        function debugTickets() {
            console.log('=== DEBUG TICKETS ===');
            const tickets = document.querySelectorAll('.ticket-item');
            console.log('Tickets found:', tickets.length);
            tickets.forEach((ticket, i) => {
                console.log(`Ticket ${i}:`, {
                    id: ticket.getAttribute('data-ticket-id'),
                    hasClickableClass: ticket.classList.contains('clickable'),
                    hasActions: ticket.querySelector('.ticket-actions') !== null,
                    canModify: ticket.querySelector('.ticket-actions .c-btn:not(.c-btn-disabled)') !== null
                });
            });
        }
        // Run debug after page loads
        setTimeout(debugTickets, 3000);
    </script> -->
</body>
</html>

<?php
// Helper functions
function formatCategory($category) {
    $categories = [
        'technical' => 'Technical',
        'account' => 'Account',
        'billing' => 'Billing',
        'feature' => 'Feature',
        'bug' => 'Bug',
        'general' => 'General',
        'other' => 'Others'
    ];
    return $categories[$category] ?? $category;
}

function formatPriority($priority) {
    $priorities = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent'
    ];
    return $priorities[$priority] ?? $priority;
}

function formatTime($timestamp) {
    $now = new DateTime();
    $ticketTime = new DateTime($timestamp);
    $interval = $now->diff($ticketTime);
    
    if ($interval->y > 0) return $interval->y . 'y ago';
    if ($interval->m > 0) return $interval->m . 'mo ago';
    if ($interval->d > 0) return $interval->d . 'd ago';
    if ($interval->h > 0) return $interval->h . 'h ago';
    if ($interval->i > 0) return $interval->i . 'm ago';
    
    return 'Just now';
}

// Close database connection
mysqli_close($connection);
?>