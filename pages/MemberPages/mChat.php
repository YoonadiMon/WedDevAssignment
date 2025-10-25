<?php
session_start();
include("../../php/dbConn.php");
include("../../php/sessionCheck.php");

$userID = $_SESSION['userID'];
$username = $_SESSION['username'];

// get initials
function getInitials($name) {
    if (empty($name) || $name === 'Deleted User') {
        return 'DU';
    }
    $words = explode(' ', trim($name));
    if (count($words) >= 2) {
        return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    }
    return strtoupper(substr($words[0], 0, 2));
}

// Initialize variables
$conversation_id = isset($_GET['conversation_id']) ? (int)$_GET['conversation_id'] : 0;
$start_chat_with_userID = isset($_GET['start_chat_with']) ? (int)$_GET['start_chat_with'] : 0;
$temporary_chat_mode = false;
$messages = [];
$other_user = null;

// Handle new message submission FIRST (before any other logic)
if (isset($_POST['send_message'])) {
    $message_text = mysqli_real_escape_string($connection, $_POST['message']);
    $receiver_id = (int)$_POST['receiver_id'];
    
    if (!empty($message_text)) {
        // Check if we're in temporary chat mode (no conversation_id yet)
        $current_conversation_id = isset($_POST['conversation_id']) ? (int)$_POST['conversation_id'] : 0;
        
        if ($current_conversation_id == 0 && $receiver_id > 0) {
            // Create new conversation for temporary chat
            $create_conv_query = "INSERT INTO tblconversations (user1ID, user2ID, lastMessageTime) 
                                 VALUES ('$userID', '$receiver_id', NOW())";
            
            if (mysqli_query($connection, $create_conv_query)) {
                $conversation_id = mysqli_insert_id($connection);
                
                // Now insert the message
                $insert_query = "INSERT INTO tblmessages (conversationID, senderID, receiverID, messageText) 
                                VALUES ('$conversation_id', '$userID', '$receiver_id', '$message_text')";
                
                if (mysqli_query($connection, $insert_query)) {
                    // Update conversation last message time
                    $update_conv = "UPDATE tblconversations SET lastMessageTime = NOW() WHERE conversationID = '$conversation_id'";
                    mysqli_query($connection, $update_conv);
                    
                    // Redirect to the new conversation
                    header("Location: mChat.php?conversation_id=$conversation_id");
                    exit();
                } else {
                    $_SESSION['error'] = "Error sending message: " . mysqli_error($connection);
                }
            } else {
                $_SESSION['error'] = "Error creating conversation: " . mysqli_error($connection);
            }
        } elseif ($current_conversation_id > 0) {
            // Existing conversation - insert message normally
            $insert_query = "INSERT INTO tblmessages (conversationID, senderID, receiverID, messageText) 
                            VALUES ('$current_conversation_id', '$userID', '$receiver_id', '$message_text')";
            
            if (mysqli_query($connection, $insert_query)) {
                // Update conversation last message time
                $update_conv = "UPDATE tblconversations SET lastMessageTime = NOW() WHERE conversationID = '$current_conversation_id'";
                mysqli_query($connection, $update_conv);
                
                // Redirect to refresh
                header("Location: mChat.php?conversation_id=$current_conversation_id");
                exit();
            } else {
                $_SESSION['error'] = "Error sending message: " . mysqli_error($connection);
            }
        }
    } else {
        $_SESSION['error'] = "Please enter a message.";
    }
    
    // Redirect back to current view
    if ($current_conversation_id > 0) {
        header("Location: mChat.php?conversation_id=$current_conversation_id");
    } elseif ($receiver_id > 0) {
        header("Location: mChat.php?start_chat_with=$receiver_id");
    } else {
        header("Location: mChat.php");
    }
    exit();
}

// Now handle the display logic
if ($start_chat_with_userID > 0 && $conversation_id == 0) {
    // Check if conversation already exists between these two users
    $check_conv_query = "SELECT conversationID FROM tblconversations 
                        WHERE (user1ID = '$userID' AND user2ID = '$start_chat_with_userID')
                        OR (user1ID = '$start_chat_with_userID' AND user2ID = '$userID')
                        LIMIT 1";
    
    $check_result = mysqli_query($connection, $check_conv_query);
    
    if ($check_result && mysqli_num_rows($check_result) > 0) {
        // Conversation exists, get the ID
        $existing_conv = mysqli_fetch_assoc($check_result);
        $conversation_id = $existing_conv['conversationID'];
        
        // Redirect to the existing conversation
        header("Location: mChat.php?conversation_id=$conversation_id");
        exit();
    } else {
        // Set up temporary chat mode
        $temporary_chat_mode = true;
        
        // Get the other user's details for the temporary chat
        $other_user_query = "SELECT userID, username, fullName FROM tblusers WHERE userID = '$start_chat_with_userID'";
        $other_user_result = mysqli_query($connection, $other_user_query);
        
        if ($other_user_result && mysqli_num_rows($other_user_result) > 0) {
            $other_user_data = mysqli_fetch_assoc($other_user_result);
            $other_user = [
                'id' => $other_user_data['userID'],
                'name' => $other_user_data['username'],
                'fullName' => $other_user_data['fullName']
            ];
        } else {
            $_SESSION['error'] = "User not found.";
            header("Location: mChat.php");
            exit();
        }
    }
}

// If conversation selected, fetch messages
if ($conversation_id > 0) {
    // Get conversation details
    $conv_query = "SELECT c.*, 
                   u1.username as user1_name, u1.fullName as user1_fullName, 
                   u2.username as user2_name, u2.fullName as user2_fullName
                   FROM tblconversations c
                   LEFT JOIN tblusers u1 ON c.user1ID = u1.userID
                   LEFT JOIN tblusers u2 ON c.user2ID = u2.userID
                   WHERE c.conversationID = '$conversation_id' 
                   AND (c.user1ID = '$userID' OR c.user2ID = '$userID')";
    
    $conv_result = mysqli_query($connection, $conv_query);
    
    if ($conv_result && mysqli_num_rows($conv_result) > 0) {
        $conv = mysqli_fetch_assoc($conv_result);
        
        // Determine other user
        if ($conv['user1ID'] == $userID) {
            $other_user = [
                'id' => $conv['user2ID'],
                'name' => $conv['user2_name'] ?: 'Deleted User',
                'fullName' => $conv['user2_fullName'] ?: 'Deleted User'
            ];
        } else {
            $other_user = [
                'id' => $conv['user1ID'],
                'name' => $conv['user1_name'] ?: 'Deleted User',
                'fullName' => $conv['user1_fullName'] ?: 'Deleted User'
            ];
        }
        
        // Fetch messages
        $messages_query = "SELECT m.*, 
                          COALESCE(u.username, 'Deleted User') as username, 
                          COALESCE(u.fullName, 'Deleted User') as fullName
                          FROM tblmessages m
                          LEFT JOIN tblusers u ON m.senderID = u.userID
                          WHERE m.conversationID = '$conversation_id'
                          ORDER BY m.sentAt ASC";
        
        $messages_result = mysqli_query($connection, $messages_query);
        
        if ($messages_result) {
            while ($row = mysqli_fetch_assoc($messages_result)) {
                $messages[] = $row;
            }
        }
        
        // Mark messages as read
        $mark_read = "UPDATE tblmessages SET isRead = TRUE 
                     WHERE conversationID = '$conversation_id' 
                     AND receiverID = '$userID' 
                     AND isRead = FALSE";
        mysqli_query($connection, $mark_read);
    }
}

// Fetch all conversations for the current user
$conversations_query = "SELECT c.*, 
                       u1.username as user1_name, u1.fullName as user1_fullName,
                       u2.username as user2_name, u2.fullName as user2_fullName,
                       (SELECT COUNT(*) FROM tblmessages WHERE conversationID = c.conversationID 
                        AND receiverID = '$userID' AND isRead = FALSE) as unread_count,
                       (SELECT messageText FROM tblmessages WHERE conversationID = c.conversationID 
                        ORDER BY sentAt DESC LIMIT 1) as last_message
                       FROM tblconversations c
                       LEFT JOIN tblusers u1 ON c.user1ID = u1.userID
                       LEFT JOIN tblusers u2 ON c.user2ID = u2.userID
                       WHERE c.user1ID = '$userID' OR c.user2ID = '$userID'
                       ORDER BY c.lastMessageTime DESC";
$conversations_result = mysqli_query($connection, $conversations_query);

$conversations = [];
if ($conversations_result) {
    while ($row = mysqli_fetch_assoc($conversations_result)) {
        // Determine the other user in conversation
        if ($row['user1ID'] == $userID) {
            $row['other_userID'] = $row['user2ID'];
            $row['other_username'] = $row['user2_name'] ?: 'Deleted User';
            $row['other_user_fullName'] = $row['user2_fullName'] ?: 'Deleted User';
        } else {
            $row['other_userID'] = $row['user1ID'];
            $row['other_username'] = $row['user1_name'] ?: 'Deleted User';
            $row['other_user_fullName'] = $row['user1_fullName'] ?: 'Deleted User';
        }
        $conversations[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.2.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style/style.css">
    
    <!-- chat-specific styles -->
    <link rel="stylesheet" href="../../style/chatStyle.css">
    <title>Chat - ReLeaf</title>
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">
</head>

<body>
    <div id="cover" class="" onclick="hideMenu()"></div>

    <!-- Chat Container -->
    <section class="chat-container">
        <div class="chat-content">
            <!-- Conversations Sidebar -->
            <div class="content-sidebar">
                <div class="content-sidebar-title">
                    <a href="../../pages/MemberPages/memberIndex.php" class="desktop-back-button">
                        <img src="../../assets/images/icon-back-light.svg" alt="Back" />
                    </a>
                    <span>Chats</span>
                </div>
                <form action="" class="content-sidebar-form">
                    <input type="search" id="chat-search-input" class="c-input content-sidebar-input" placeholder="Search conversations..." />
                </form>

                <div class="content-messages">
                    <ul class="content-messages-list">
                        <li class="content-message-title"><div>Recent</div></li>
                        <?php if (empty($conversations)): ?>
                            <div class="empty-conversation">
                                <i class="ri-message-3-line"></i>
                                <p>No conversations yet</p>
                                <small>Start a new conversation to begin messaging</small>
                            </div>
                        <?php else: ?>
                            <?php foreach ($conversations as $conv): ?>
                                <li class="<?php echo $conv['conversationID'] == $conversation_id ? 'active' : ''; ?>">
                                    <a href="mChat.php?conversation_id=<?php echo $conv['conversationID']; ?>">
                                        <div>
                                            <span class="content-message-avatar avatar-initials">
                                                <?php echo getInitials($conv['other_user_fullName']); ?>
                                            </span>
                                            <span class="content-message-info">
                                                <span class="content-message-name"><?php echo htmlspecialchars($conv['other_username']); ?></span>
                                                <span class="content-message-text">
                                                    <?php 
                                                    $lastMessage = $conv['last_message'] ?? 'No messages yet';
                                                    echo htmlspecialchars($lastMessage); 
                                                    ?>
                                                </span>
                                            </span>
                                            <span class="content-message-more">
                                                <span class="content-message-time">
                                                    <?php 
                                                    echo date('g:i A', strtotime($conv['lastMessageTime']));
                                                    ?>
                                                </span>
                                                <?php if ($conv['unread_count'] > 0): ?>
                                                    <span class="content-message-unread"><?php echo $conv['unread_count']; ?></span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Default/No Conversation Selected -->
            <?php if (!$conversation_id && !$temporary_chat_mode): ?>
                <div class="conversation conversation-default active">
                    <p>Select a chat to start messaging!</p>
                    <small>Choose a conversation from the sidebar or start a new one</small>
                </div>
            <?php else: ?>
                <!-- Active Conversation -->
                <div class="conversation active">
                    <div class="conversation-top">
                        <button type="button" class="conversation-back" onclick="showConversationList()">
                            <img src="../../assets/images/icon-back-light.svg" alt="icon-back" id="back-icon" />
                        </button>
                        <div class="conversation-user">
                            <div class="conversation-user-avatar avatar-initials">
                                <?php echo getInitials($other_user['fullName']); ?>
                            </div>
                            <div>
                                <div class="conversation-user-name"><?php echo htmlspecialchars($other_user['name']); ?></div>
                                <?php if ($temporary_chat_mode): ?>
                                    <small style="color: var(--MainGreen); font-size: 0.8rem;">New conversation</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Alert Error Messages -->
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-error">
                            <?php 
                            echo htmlspecialchars($_SESSION['error']); 
                            unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <div class="conversation-main" id="conversationMain">
                        <ul class="conversation-wrapper">
                            <?php if (empty($messages)): ?>
                                <div class="coversation-divider"><span>Start Conversation</span></div>
                                <div class="empty-conversation">
                                    <i class="ri-chat-1-line"></i>
                                    <p>No messages yet</p>
                                    <small>Send a message to start the conversation</small>
                                </div>
                            <?php else: ?>
                                <?php 
                                $last_date = '';
                                foreach ($messages as $msg): 
                                    $msg_date = date('Y-m-d', strtotime($msg['sentAt']));
                                    $is_me = $msg['senderID'] == $userID;
                                    
                                    // Show date divider only when date changes
                                    if ($last_date != $msg_date) {
                                        $last_date = $msg_date;
                                        $display_date = date('F j, Y', strtotime($msg['sentAt']));
                                        echo '<div class="coversation-divider"><span>' . $display_date . '</span></div>';
                                    }
                                ?>
                                    <li class="conversation-item <?php echo $is_me ? 'me' : ''; ?>">
                                        <div class="conversation-item-side">
                                            <div class="conversation-item-avatar avatar-initials">
                                                <?php echo getInitials($msg['fullName']); ?>
                                            </div>
                                        </div>
                                        <div class="conversation-item-content">
                                            <div class="conversation-item-wrapper">
                                                <div class="conversation-item-box">
                                                    <div class="conversation-item-text">
                                                        <p><?php echo nl2br(htmlspecialchars($msg['messageText'])); ?></p>
                                                        <div class="conversation-item-time">
                                                            <?php echo date('g:i A', strtotime($msg['sentAt'])); ?>
                                                            <?php if ($is_me && $msg['isRead']): ?>
                                                                <span style="margin-left: 4px;">✓✓</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                                                                
                    <?php if ($conversation_id > 0 || $temporary_chat_mode): ?>
                        <div class="conversation-form">
                            <form method="POST" id="messageForm">
                                <input type="hidden" name="receiver_id" value="<?php echo $other_user['id']; ?>">
                                <input type="hidden" name="conversation_id" value="<?php echo $conversation_id; ?>">
                                
                                <div class="conversation-form-group">
                                    <textarea class="conversation-form-input" 
                                            name="message" 
                                            rows="1" 
                                            placeholder="Type your message..."
                                            required></textarea>
                                </div>
                                
                                <button type="submit" name="send_message" class="conversation-form-button">
                                    <img src="../../assets/images/send-icon-light.svg" alt="send" id="send-icon" />
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script>
        const isAdmin = false;

        // Function to show conversation list/sidebar
        function showConversationList() {
            const sidebar = document.querySelector('.content-sidebar');
            const conversation = document.querySelector('.conversation.active');
            const conversationDefault = document.querySelector('.conversation-default');
            
            if (window.innerWidth <= 768) {
                // Mobile: Show sidebar, hide conversation
                if (sidebar) sidebar.style.display = 'flex';
                if (conversation) conversation.style.display = 'none';
                if (conversationDefault) conversationDefault.style.display = 'none';
                
                // Update URL without page reload
                const newUrl = window.location.origin + window.location.pathname;
                window.history.pushState({}, '', newUrl);
            } else {
                // Desktop: Navigate to main chat page
                window.location.href = 'mChat.php';
            }
        }

        // Auto-scroll to bottom on page load
        document.addEventListener('DOMContentLoaded', function() {
            const conversationMain = document.getElementById('conversationMain');
            if (conversationMain) {
                conversationMain.scrollTop = conversationMain.scrollHeight;
            }

            // Auto-hide alerts after 3 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 300);
                }, 3000);
            });

            // Mobile: Hide sidebar if conversation is active on mobile
            const urlParams = new URLSearchParams(window.location.search);
            const conversationId = urlParams.get('conversation_id');
            const startChatWith = urlParams.get('start_chat_with');
            
            if (window.innerWidth <= 768 && (conversationId || startChatWith)) {
                document.querySelector('.content-sidebar').style.display = 'none';
            }

            // Handle browser back button
            window.addEventListener('popstate', function(event) {
                if (window.innerWidth <= 768) {
                    showConversationList();
                }
            });

            // Prevent form submission from interfering with navigation
            const messageForm = document.getElementById('messageForm');
            if (messageForm) {
                // Store the current state before form submission
                messageForm.addEventListener('submit', function() {
                    if (window.innerWidth <= 768) {
                        sessionStorage.setItem('shouldShowConversation', 'true');
                    }
                });
            }
            
            // Check if we should show conversation list after page load (after form submission)
            if (window.innerWidth <= 768 && sessionStorage.getItem('shouldShowConversation') === 'true') {
                sessionStorage.removeItem('shouldShowConversation');
                // Small delay to ensure page is fully loaded
                setTimeout(() => {
                    const currentUrlParams = new URLSearchParams(window.location.search);
                    if (!currentUrlParams.has('conversation_id') && !currentUrlParams.has('start_chat_with')) {
                        showConversationList();
                    }
                }, 100);
            }
        });

        // Auto-expand textarea
        document.querySelectorAll('.conversation-form-input').forEach(function(item) {
            item.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
        });

        // Search functionality
        const chatSearchInput = document.getElementById('chat-search-input');
        if (chatSearchInput) {
            chatSearchInput.addEventListener('input', function() {
                const searchValue = this.value.toLowerCase();
                const listItems = document.querySelectorAll('.content-messages-list > li:not(.content-message-title)');

                listItems.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    if (text.includes(searchValue)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                // Show both sidebar and conversation on desktop
                document.querySelector('.content-sidebar').style.display = 'flex';
            }
        });
    </script>
    <script src="../../javascript/mainScript.js"></script>
</body>
</html>

<?php mysqli_close($connection); ?>