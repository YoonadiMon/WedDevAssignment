<?php
// Database connection and query for current user's tickets
include("../../php/dbConn.php");

// Assuming current user ID is 6 (as per requirements)
$currentUserID = 6;

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
            }

            .help-section {
                background-color: var(--bg-color);
                border-radius: 8px;
                padding: 20px 10px 15px 10px;
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
            }

            .open-ticket-btn:hover {
                background-color: var(--MainGreen);
            }

            .tickets-section {
                background-color: var(--DarkerGray);
                border-radius: 8px;
                padding: 30px;
                margin-bottom: 30px;
            }

            .tickets-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 30px;
            }

            .tickets-header h3 {
                font-size: 22px;
                font-weight: 400;
                color: var(--White);
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
            }

            .add-new-btn:hover {
                background-color: var(--LightGreen);
                color: var(--Black);
            }

            .tickets-table {
                width: 100%;
                border-collapse: collapse;
            }

            .tickets-table thead {
                border-bottom: 1px solid var(--DarkGray);
            }

            .tickets-table th {
                padding: 15px;
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
                padding: 20px 15px;
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
        </style>
    </head>

    <body>
        <div id="cover" class="" onclick="hideMenu()"></div>

        <!-- Logo + Name & Navbar -->
        <header>
            <!-- Logo + Name -->
            <section class="c-logo-section">
                <a href="../../pages/MemberPages/memberIndex.html" class="c-logo-link">
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
                        <a href="../../pages/CommonPages/mainTrade.html">Trade</a>
                        <a href="../../pages/CommonPages/aboutUs.html">About</a>
                    </div>
                </div>

            </nav>

            <!-- Menu Links Desktop + Tablet -->
            <nav class="c-navbar-desktop">
                <a href="../../pages/MemberPages/memberIndex.html">Home</a>
                <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
                <a href="../../pages/CommonPages/mainEvent.html">Event</a>
                <a href="../../pages/CommonPages/mainTrade.html">Trade</a>
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
        
        </header>

        <hr>

        <!-- Main Content -->
        <main>
            <!-- Content Section -->
            <!-- Help Section -->
            <div class="help-section">
                <div class="help-icon">?</div>
                <h2>Need some help?</h2>
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
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr onclick="window.location.href='mTicketDetails.php?id=<?php echo $row['ticketID']; ?>'">
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
                    <a href="../../pages/CommonPages/mainTrade.html">Trade</a>
                </div>
            </section>
        </footer>

        <script>const isAdmin = false;</script>
        <script src="../../javascript/mainScript.js"></script>
    </body>
</html>
<?php
// Close the database connection
mysqli_close($connection);
?>