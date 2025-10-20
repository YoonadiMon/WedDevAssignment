<?php
session_start();
include("../../php/dbConn.php");

$user_type = '';
$user_id = '';

// Force user type based on URL if provided
if (isset($_GET['from'])) {
    if ($_GET['from'] === 'member') {
        unset($_SESSION['admin_id']); // remove admin session
        $user_type = 'member';
        $user_id = 4;
        $_SESSION['user_id'] = $user_id;
    } elseif ($_GET['from'] === 'admin') {
        unset($_SESSION['user_id']); // remove member session
        $user_type = 'admin';
        $user_id = 1;
        $_SESSION['admin_id'] = $user_id;
    }
}
// If no GET parameter, check existing sessions
elseif (isset($_SESSION['admin_id'])) {
    $user_type = 'admin';
    $user_id = $_SESSION['admin_id'];
} elseif (isset($_SESSION['user_id'])) {
    $user_type = 'member';
    $user_id = $_SESSION['user_id'];
}
// Default fallback
else {
    $user_type = 'admin';
    $user_id = 1;
    $_SESSION['admin_id'] = $user_id;
}

// Store user type in session for consistency
$_SESSION['user_type'] = $user_type;
echo "<script>console.log(" . json_encode($_SESSION['user_type']) . ");</script>";

// Get ticket ID from URL
if (!isset($_GET['ticket_id'])) {
    // Redirect based on user type
    if ($user_type === 'admin') {
        header("Location: ../../pages/adminPages/aHelpTicket.php");
    } else {
        header("Location: ../../pages/MemberPages/mContactSupport.php");
    }
    exit();
}
$ticket_id = mysqli_real_escape_string($connection, $_GET['ticket_id']);

// Fetch ticket details
$ticket_query = "SELECT t.*, u.username, u.email 
                 FROM tbltickets t 
                 LEFT JOIN tblusers u ON t.userID = u.userID 
                 WHERE t.ticketID = '$ticket_id'";
$ticket_result = mysqli_query($connection, $ticket_query);

if (!$ticket_result || mysqli_num_rows($ticket_result) == 0) {
    // Redirect based on user type
    if ($user_type === 'admin') {
        header("Location: ../../pages/adminPages/aHelpTicket.php?error=Ticket not found");
    } else {
        header("Location: ../../pages/MemberPages/mContactSupport.php?error=Ticket not found");
    }
    exit();
}

$ticket = mysqli_fetch_assoc($ticket_result);

// Permission checks based on user type
if ($user_type === 'admin') {
    // Check if admin can access this ticket (either not assigned or assigned to this admin)
    if ($ticket['adminAssignedID'] != NULL && $ticket['adminAssignedID'] != $user_id) {
        header("Location: ../../pages/adminPages/aHelpTicket.php?error=You don't have permission to access this ticket");
        exit();
    }

    // If ticket is not assigned, assign it to current admin
    if ($ticket['adminAssignedID'] == NULL) {
        $assign_query = "UPDATE tbltickets SET adminAssignedID = '$user_id', updatedAt = NOW() WHERE ticketID = '$ticket_id'";
        mysqli_query($connection, $assign_query);
        $ticket['adminAssignedID'] = $user_id;
    }
} else {
    // Member permission check - can only access their own tickets
    if ($ticket['userID'] != $user_id) {
        header("Location: ../../pages/MemberPages/mContactSupport.php?error=You don't have permission to access this ticket");
        exit();
    }
}

// Fetch all responses for this ticket
$responses_query = "SELECT tr.*, 
                   CASE 
                       WHEN tr.responderType = 'admin' THEN 'Admin'
                       ELSE u.username 
                   END as responder_name,
                   CASE 
                       WHEN tr.responderType = 'admin' THEN 'admin'
                       ELSE 'member'
                   END as user_type
                   FROM tblticket_responses tr
                   LEFT JOIN tblusers u ON tr.responderId = u.userID AND tr.responderType = 'member'
                   WHERE tr.ticketID = '$ticket_id'
                   ORDER BY tr.createdAt ASC";
$responses_result = mysqli_query($connection, $responses_query);

$responses = [];
if ($responses_result) {
    while ($row = mysqli_fetch_assoc($responses_result)) {
        $responses[] = $row;
    }
}

// Handle new response submission
if (isset($_POST['send_response']) && isset($_POST['message'])) {
    $message = mysqli_real_escape_string($connection, $_POST['message']);
    
    if (!empty($message)) {
        // Use the correct user type and ID for response
        $insert_query = "INSERT INTO tblticket_responses (ticketID, responderId, responderType, message) 
                        VALUES ('$ticket_id', '$user_id', '$user_type', '$message')";
        
        if (mysqli_query($connection, $insert_query)) {
            // Update ticket's last reply time
            $update_query = "UPDATE tbltickets SET lastReplyAt = NOW(), updatedAt = NOW() WHERE ticketID = '$ticket_id'";
            mysqli_query($connection, $update_query);
            
            $success_message = "Response sent successfully!";
            
            // Refresh responses
            $responses_result = mysqli_query($connection, $responses_query);
            $responses = [];
            if ($responses_result) {
                while ($row = mysqli_fetch_assoc($responses_result)) {
                    $responses[] = $row;
                }
            }
        } else {
            $error_message = "Error sending response: " . mysqli_error($connection);
        }
    } else {
        $error_message = "Message cannot be empty";
    }
}

// Handle mark as solved (admin only)
if (isset($_POST['mark_solved']) && $user_type === 'admin') {
    $update_query = "UPDATE tbltickets SET status = 'solved', updatedAt = NOW() WHERE ticketID = '$ticket_id'";
    if (mysqli_query($connection, $update_query)) {
        $success_message = "Ticket marked as solved!";
        $ticket['status'] = 'solved';
    } else {
        $error_message = "Error updating ticket: " . mysqli_error($connection);
    }
}

// Handle reopen ticket
if (isset($_POST['reopen_ticket'])) {
    $update_query = "UPDATE tbltickets SET status = 'open', updatedAt = NOW() WHERE ticketID = '$ticket_id'";
    if (mysqli_query($connection, $update_query)) {
        $success_message = "Ticket reopened!";
        $ticket['status'] = 'open';
    } else {
        $error_message = "Error reopening ticket: " . mysqli_error($connection);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #<?php echo $ticket_id; ?> - <?php echo ucfirst($user_type); ?> Support</title>
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">
    <link rel="stylesheet" href="../../style/style.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <style>
        .ticket-detail-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .ticket-header {
            background: var(--bg-color);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px var(--shadow-color);
            margin-bottom: 20px;
        }

        .ticket-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 15px;
        }

        .ticket-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-category { background: var(--sec-bg-color); color: var(--text-color); }
        .badge-priority { color: white; }
        .priority-urgent { background: #ef4444; }
        .priority-high { background: #f97316; }
        .priority-medium { background: #f59e0b; }
        .priority-low { background: #10b981; }
        .badge-status { color: white; }
        .status-open { background: #10b981; }
        .status-in_progress { background: #f59e0b; }
        .status-solved { background: #6b7280; }

        .conversation-container {
            background: var(--bg-color);
            border-radius: 8px;
            box-shadow: 0 2px 10px var(--shadow-color);
            margin-bottom: 20px;
            max-height: 600px;
            overflow-y: auto;
        }

        .message {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .message:last-child {
            border-bottom: none;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .message-sender {
            font-weight: 600;
            color: var(--text-heading);
        }

        .sender-admin { color: #10b981; }
        .sender-member { color: #3b82f6; }

        .message-time {
            font-size: 12px;
            color: var(--Gray);
        }

        .message-content {
            color: var(--text-color);
            line-height: 1.5;
        }

        .response-form {
            background: var(--bg-color);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px var(--shadow-color);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background: var(--sec-bg-color);
            color: var(--text-color);
            font-family: inherit;
            resize: vertical;
            min-height: 100px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-color);
            text-decoration: none;
            margin-bottom: 20px;
            padding: 8px 16px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            transition: all 0.3s;
        }

        .back-link:hover {
            background: var(--btn-color-hover);
        }

        @media (max-width: 768px) {
            .ticket-detail-container {
                padding: 10px;
            }
            
            .ticket-meta {
                flex-direction: column;
                gap: 8px;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div id="cover" class="" onclick="hideMenu()"></div>
    
    <!-- Header -->
    <header>
        <?php if ($user_type === 'admin'): ?>
            <!-- Admin Header -->
            <section class="c-logo-section">
                <a href="../../pages/adminPages/adminIndex.php" class="c-logo-link">
                    <img src="../../assets/images/Logo.png" alt="Logo" class="c-logo">
                    <div class="c-text">ReLeaf</div>
                </a>
            </section>
            <!-- Add admin navigation here -->
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
        <div class="ticket-detail-container">
            <!-- Back button -->
            <?php 
            // Determine the correct back URL
            $back_url = '';
            if ($user_type === 'admin') {
                $back_url = '../../pages/adminPages/aHelpTicket.php';
            } else {
                $back_url = '../../pages/MemberPages/mContactSupport.php';
            }
            ?>
            <a href="<?php echo $back_url; ?>" class="back-link">
                ← Back to Tickets
            </a>

            <!-- Success/Error Messages -->
            <?php if (isset($success_message)): ?>
                <div class="message success-message" style="background: var(--MainGreen); color: white; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="message error-message" style="background: #f44336; color: white; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Ticket Header -->
            <div class="ticket-header">
                <h1><?php echo htmlspecialchars($ticket['subject']); ?></h1>
                <?php if ($user_type === 'admin'): ?>
                    <p><strong>From:</strong> <?php echo htmlspecialchars($ticket['username']); ?> (<?php echo htmlspecialchars($ticket['email']); ?>)</p>
                <?php endif; ?>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($ticket['description']); ?></p>
                
                <div class="ticket-meta">
                    <span class="ticket-badge badge-category"><?php echo formatCategory($ticket['category']); ?></span>
                    <span class="ticket-badge badge-priority priority-<?php echo $ticket['priority']; ?>"><?php echo formatPriority($ticket['priority']); ?> Priority</span>
                    <span class="ticket-badge badge-status status-<?php echo $ticket['status']; ?>"><?php echo ucfirst($ticket['status']); ?></span>
                    <span class="ticket-badge badge-category">Ticket #<?php echo $ticket_id; ?></span>
                    <span class="ticket-badge badge-category">Created: <?php echo date('M j, Y g:i A', strtotime($ticket['createdAt'])); ?></span>
                </div>
            </div>

            <!-- Conversation Thread -->
            <div class="conversation-container">
                <?php if (empty($responses)): ?>
                    <div class="message">
                        <p style="text-align: center; color: var(--Gray);">No messages yet. Start the conversation!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($responses as $response): ?>
                        <div class="message">
                            <div class="message-header">
                                <span class="message-sender sender-<?php echo $response['user_type']; ?>">
                                    <?php echo htmlspecialchars($response['responder_name']); ?>
                                    <?php if ($response['user_type'] == 'admin'): ?>
                                        (Admin)
                                    <?php endif; ?>
                                </span>
                                <span class="message-time">
                                    <?php echo date('M j, Y g:i A', strtotime($response['createdAt'])); ?>
                                </span>
                            </div>
                            <div class="message-content">
                                <?php echo nl2br(htmlspecialchars($response['message'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Response Form -->
            <?php if ($ticket['status'] !== 'solved'): ?>
                <div class="response-form">
                    <form method="POST">
                        <div class="form-group">
                            <textarea 
                                name="message" 
                                class="form-control" 
                                placeholder="Type your response here..." 
                                required
                            ></textarea>
                        </div>
                        <div class="form-actions">
                            <?php if ($user_type === 'admin'): ?>
                                <?php if ($ticket['status'] === 'solved'): ?>
                                    <button type="submit" name="reopen_ticket" class="c-btn c-btn-secondary">
                                        Reopen Ticket
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="mark_solved" class="c-btn c-btn-secondary">
                                        Mark as Solved
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                            <button type="submit" name="send_response" class="c-btn c-btn-primary">
                                Send Response
                            </button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="response-form" style="text-align: center; padding: 30px;">
                    <p style="color: var(--Gray); margin-bottom: 15px;">This ticket has been solved.</p>
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="reopen_ticket" class="c-btn c-btn-primary">
                            Reopen Ticket
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <hr>
    <!-- Footer -->
    <footer>
        <?php if ($user_type === 'member'): ?>
            <!-- Member Footer -->
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
                    <a href="../../pages/CommonPages/mainEvent.html">Events</a><br>
                    <a href="../../pages/CommonPages/mainBlog.html">Blogs</a><br>
                    <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                </div>
            </section>
        <?php else: ?>
            <!-- Add admin footer here if needed -->
        <?php endif; ?>
    </footer>

    <script src="../../javascript/mainScript.js"></script>
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

mysqli_close($connection);
?>