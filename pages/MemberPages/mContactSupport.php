<?php
// Database connection and query for current user's tickets
include("../../php/dbConn.php");

// Start session and get actual logged-in user ID
session_start();
$currentUserID = $_SESSION['userID']; 
$username = $_SESSION['username'] ?? '';

// Query to fetch tickets for the current user
$query = "SELECT * FROM tbltickets WHERE userID = $currentUserID ORDER BY ticketID DESC";
$result = mysqli_query($connection, $query);

// Error handling
if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}

// Count the number of tickets
$ticketCount = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Contact Support - ReLeaf</title>
        <link rel="icon" type="image/png" href="../../assets/images/Logo.png">

        <link rel="stylesheet" href="../../style/style.css">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
            rel="stylesheet">

        <style>
            .support-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 15px;
            }

            .help-section {
                background-color: var(--bg-color);
                border-radius: 8px;
                padding: 20px 15px;
                text-align: center;
                margin-bottom: 20px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .help-icon {
                width: 80px;
                height: 80px;
                background-color: var(--Gray);
                border-radius: 50%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 48px;
                color: var(--White);
                margin-bottom: 20px;
            }

            .help-section h2 {
              font-size: 24px;
              margin-bottom: 15px;
              font-weight: 400;
            }

            .help-section p {
                color: var(--Gray);
                margin-bottom: 25px;
                line-height: 1.6;
                max-width: 800px;
                margin-left: auto;
                margin-right: auto;
            }

            .open-ticket-btn {
                background-color: var(--MainGreen);
                color: white;
                border: none;
                padding: 12px 30px;
                font-size: 16px;
                border-radius: 4px;
                cursor: pointer;
                transition: background-color 0.3s;
                width: 100%;
                max-width: 250px;
            }

            .open-ticket-btn:hover {
                background-color: var(--MainGreen);
            }

            .tickets-section {
                background-color: var(--DarkerGray);
                border-radius: 8px;
                padding: 20px 15px;
                margin-bottom: 30px;
                overflow-x: auto;
            }

            .tickets-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                flex-wrap: wrap;
                gap: 15px;
            }

            .tickets-header h3 {
                font-size: 22px;
                font-weight: 400;
                color: var(--White);
                margin: 0;
            }

            a {
                text-decoration: none;
            }
            .add-new-btn {
                background-color: var(--MainGreen);
                color: var(--White);
                border: none;
                padding: 10px 20px;
                font-size: 14px;
                border-radius: 20px;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 8px;
                transition: background-color 0.3s;
                white-space: nowrap;
            }

            .add-new-btn:hover {
                background-color: var(--LightGreen);
                color: var(--Black);
            }

            /* Desktop table styles */
            .tickets-table {
                width: 100%;
                border-collapse: collapse;
                min-width: 800px;
            }

            .tickets-table thead {
                border-bottom: 1px solid var(--DarkGray);
            }

            .tickets-table th {
                padding: 15px 10px;
                text-align: left;
                font-weight: 400;
                color: var(--Gray);
                font-size: 14px;
            }

            .tickets-table tbody tr {
                border-bottom: 1px solid var(--DarkGray);
                cursor: pointer;
                transition: background-color 0.2s;
            }

            .tickets-table tbody tr:hover {
                background-color: rgba(255, 255, 255, 0.05);
            }

            .tickets-table td {
                padding: 15px 10px;
                color: var(--White);
            }

            .no-tickets {
                text-align: center;
                padding: 40px;
                color: var(--Gray);
            }
            
            .status-badge {
                display: inline-block;
                padding: 5px 12px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 500;
            }
            
            .status-open {
                background-color: #4CAF50;
                color: white;
            }
            
            .status-pending {
                background-color: #FF9800;
                color: white;
            }
            
            .status-resolved {
                background-color: #2196F3;
                color: white;
            }
            
            .status-closed {
                background-color: #9E9E9E;
                color: white;
            }
            
            .priority-high {
                color: #F44336;
                font-weight: 500;
            }
            
            .priority-medium {
                color: #FF9800;
                font-weight: 500;
            }
            
            .priority-low {
                color: #4CAF50;
                font-weight: 500;
            }

            /* Mobile card styles */
            .tickets-cards {
                display: none;
                flex-direction: column;
                gap: 15px;
            }

            .ticket-card {
                background-color: var(--DarkGray);
                border-radius: 8px;
                padding: 20px;
                cursor: pointer;
                transition: background-color 0.2s;
                border: 1px solid var(--Gray);
            }

            .ticket-card:hover {
                background-color: rgba(255, 255, 255, 0.05);
            }

            .ticket-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 15px;
                flex-wrap: wrap;
                gap: 10px;
            }

            .ticket-id {
                font-weight: 600;
                color: var(--White);
                font-size: 16px;
            }

            .ticket-status {
                margin-left: auto;
            }

            .ticket-subject {
                font-size: 18px;
                font-weight: 500;
                color: var(--White);
                margin-bottom: 10px;
                width: 100%;
            }

            .ticket-details {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 15px;
                margin-top: 15px;
            }

            .ticket-detail {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }

            .detail-label {
                font-size: 12px;
                color: var(--Gray);
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .detail-value {
                font-size: 14px;
                color: var(--White);
            }

            /* Responsive styles */
            @media (max-width: 920px) {
                .tickets-table {
                    display: none;
                }
                
                .tickets-cards {
                    display: flex;
                }
                
                .tickets-section {
                    padding: 15px 10px;
                }
                
                .tickets-header {
                    flex-direction: column;
                    align-items: flex-start;
                }
                
                .tickets-header h3 {
                    font-size: 20px;
                }
                
                .help-section {
                    padding: 15px 10px;
                }
                
                .help-icon {
                    width: 60px;
                    height: 60px;
                    font-size: 36px;
                }
                
                .help-section h2 {
                    font-size: 20px;
                }
                
                .ticket-details {
                    grid-template-columns: 1fr;
                    gap: 10px;
                }
            }

            @media (max-width: 480px) {
                .ticket-header {
                    flex-direction: column;
                    align-items: flex-start;
                }
                
                .ticket-status {
                    margin-left: 0;
                }
                
                .open-ticket-btn, .add-new-btn {
                    width: 100%;
                    justify-content: center;
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

            <!-- Menu Links -->

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

            <!-- Menu Links Desktop + Tablet -->
            <nav class="c-navbar-desktop">
                <a href="../../pages/MemberPages/memberIndex.php">Home</a>
                <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
                <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                <a href="../../pages/CommonPages/aboutUs.php">About</a>
            </nav>
            <section class="c-navbar-more">
                <input type="text" placeholder="Search..." id="searchBar" class="search-bar">
                
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

        <hr>

        <!-- Main Content -->
        <main class="content support-container" id="content">
            <!-- Content Section -->
            <!-- Help Section -->
            <div class="help-section">
                <div class="help-icon">?</div>
                <h2>Need some help, <?php echo $username?>?</h2>
                <p>Our team is happy to help or answer any question you may have. All you need to do is to create a support ticket, ask your question and our team will respond as soon as possible.</p>
                <a link href="../../pages/MemberPages/mCreateTicket.php">
                    <button class="open-ticket-btn">Open a support ticket</button>
                </a>
            </div>

            <!-- Tickets Section -->
            <div class="tickets-section">
                <div class="tickets-header">
                    <h3>Your Support Tickets</h3>
                    <a link href="../../pages/MemberPages/mCreateTicket.php">
                        <button class="add-new-btn">Add New +</button>
                    </a>
                    
                </div>

                <!-- Desktop Table View -->
                <table class="tickets-table">
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Subject</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Last Reply</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($ticketCount > 0): ?>
                            <?php 
                            // Reset pointer to beginning for table
                            mysqli_data_seek($result, 0);
                            while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr onclick="window.location.href='../../pages/CommonPages/ticketThread.php?ticket_id=<?php echo $row['ticketID']; ?>&from=member'">
                                    <td>#<?php echo $row["ticketID"]; ?></td>
                                    <td><?php echo htmlspecialchars($row["subject"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["category"]); ?></td>
                                    <td class="priority-<?php echo strtolower($row["priority"]); ?>">
                                        <?php echo $row["priority"]; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($row["status"]); ?>">
                                            <?php echo $row["status"]; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date("M j, Y", strtotime($row["createdAt"])); ?></td>
                                    <td>
                                        <?php 
                                        if (!empty($row["lastReplyAt"]) && $row["lastReplyAt"] != "0000-00-00 00:00:00") {
                                            echo date("M j, Y", strtotime($row["lastReplyAt"]));
                                        } else {
                                            echo "No replies yet";
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-tickets">No support tickets yet</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Mobile Card View -->
                <div class="tickets-cards">
                    <?php if ($ticketCount > 0): ?>
                        <?php 
                        // Reset pointer to beginning for cards
                        mysqli_data_seek($result, 0);
                        while ($row = mysqli_fetch_assoc($result)): ?>
                            <!-- <div class="ticket-card" onclick="window.location.href='mTicketDetails.php?id= 
                            <?php echo $row['ticketID']; ?>'">-->
                            <div class="ticket-card" onclick="window.location.href='../../pages/CommonPages/ticketThread.php?ticket_id=<?php echo $row['ticketID']; ?>&from=member'">
                                <div class="ticket-header">
                                    <div class="ticket-id">#<?php echo $row["ticketID"]; ?></div>
                                    <div class="ticket-status">
                                        <span class="status-badge status-<?php echo strtolower($row["status"]); ?>">
                                            <?php echo $row["status"]; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="ticket-subject"><?php echo htmlspecialchars($row["subject"]); ?></div>
                                <div class="ticket-details">
                                    <div class="ticket-detail">
                                        <span class="detail-label">Category</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($row["category"]); ?></span>
                                    </div>
                                    <div class="ticket-detail">
                                        <span class="detail-label">Priority</span>
                                        <span class="detail-value priority-<?php echo strtolower($row["priority"]); ?>">
                                            <?php echo $row["priority"]; ?>
                                        </span>
                                    </div>
                                    <div class="ticket-detail">
                                        <span class="detail-label">Created</span>
                                        <span class="detail-value"><?php echo date("M j, Y", strtotime($row["createdAt"])); ?></span>
                                    </div>
                                    <div class="ticket-detail">
                                        <span class="detail-label">Last Reply</span>
                                        <span class="detail-value">
                                            <?php 
                                            if (!empty($row["lastReplyAt"]) && $row["lastReplyAt"] != "0000-00-00 00:00:00") {
                                                echo date("M j, Y", strtotime($row["lastReplyAt"]));
                                            } else {
                                                echo "No replies yet";
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-tickets">No support tickets yet</div>
                    <?php endif; ?>
                </div>
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
                    <a href="../../pages/MemberPages/mProfile.php">My Account</a><br>
                    <a href="../../pages/MemberPages/mChat.php">My Chat</a><br>
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

        <script>
            const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
            const unreadCount = <?php echo $unread_count; ?>;
        </script>
        <script src="../../javascript/mainScript.js"></script>
    </body>
</html>
<?php
// Close the database connection
mysqli_close($connection);
?>