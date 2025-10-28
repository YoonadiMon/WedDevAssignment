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

// get trade request data for inline rendering
function getTradeRequestData($requestID, $connection, $currentUserID) {
    $query = "SELECT tr.*, 
                     tl.title, tl.category, tl.imageUrl, tl.status as listingStatus,
                     sender.username as senderName, sender.fullName as senderFullName,
                     receiver.username as receiverName
              FROM tbltrade_requests tr
              LEFT JOIN tbltrade_listings tl ON tr.listingID = tl.listingID
              LEFT JOIN tblusers sender ON tr.senderID = sender.userID
              LEFT JOIN tblusers receiver ON tr.receiverID = receiver.userID
              WHERE tr.requestID = '$requestID'";
    
    $result = mysqli_query($connection, $query);
    $data = mysqli_fetch_assoc($result);
    
    if (!$data) return null;
    
    $isSender = $data['senderID'] == $currentUserID;
    $isExpired = strtotime($data['expiresAt']) < time();
    $status = $data['status'];
    
    // check if sender, receiver, or listing is deleted
    $senderDeleted = ($data['senderID'] === null || $data['senderName'] === null);
    $receiverDeleted = ($data['receiverID'] === null || $data['receiverName'] === null);
    $listingDeleted = ($data['listingID'] === null || $data['title'] === null);
    $isListingInactive = ($data['listingStatus'] !== 'active');
    
    // determine if request should be marked as cancelled due to deletions
    $isCancelledDueToDeletion = ($senderDeleted || $receiverDeleted || $listingDeleted);
    
    // display data
    $senderDisplay = $senderDeleted ? '[Deleted User]' : htmlspecialchars($data['senderName']);
    $requestTypeText = ($data['requestType'] === 'offer') ? 'offered' : 'requested';
    $imageUrl = $data['imageUrl'] ?: '../../assets/images/placeholder-image.jpg';
    
    return [
        'isSender' => $isSender,
        'isExpired' => $isExpired,
        'status' => $status,
        'senderDeleted' => $senderDeleted,
        'receiverDeleted' => $receiverDeleted,
        'listingDeleted' => $listingDeleted,
        'isListingInactive' => $isListingInactive,
        'isCancelledDueToDeletion' => $isCancelledDueToDeletion,
        'senderDisplay' => $senderDisplay,
        'requestTypeText' => $requestTypeText,
        'imageUrl' => $imageUrl,
        'title' => $data['title'],
        'category' => $data['category'],
        'senderFullName' => $data['senderFullName'] ?? 'Deleted User'
    ];
}

// Initialize variables
$conversationID = isset($_GET['conversationID']) ? (int)$_GET['conversationID'] : 0;
$startChatWithUserID = isset($_GET['start_chat_with']) ? (int)$_GET['start_chat_with'] : 0;
$temporaryChat = false;
$messages = [];
$otherUser = null;

if (isset($_POST['send_message'])) {
    $messageText = $_POST['message'];
    $receiverID = $_POST['receiverID'];
    
    if (!empty($messageText)) {
        // check if we're in temporary chat mode (no conversationID yet)
        $currentConversationID = isset($_POST['conversationID']) ? $_POST['conversationID'] : 0;
        
        if ($currentConversationID == 0 && $receiverID > 0) {
            $createConvQuery = "INSERT INTO tblconversations (user1ID, user2ID, lastMessageTime) 
                                 VALUES ('$userID', '$receiverID', NOW())";
            
            if (mysqli_query($connection, $createConvQuery)) {
                $conversationID = mysqli_insert_id($connection);
                
                $insertQuery = "INSERT INTO tblmessages (conversationID, senderID, receiverID, messageText) 
                                VALUES ('$conversationID', '$userID', '$receiverID', '$messageText')";
                
                if (mysqli_query($connection, $insertQuery)) {
                    $updateConv = "UPDATE tblconversations SET lastMessageTime = NOW() WHERE conversationID = '$conversationID'";
                    mysqli_query($connection, $updateConv);
                    
                    header("Location: mChat.php?conversationID=$conversationID");
                    exit();
                } else {
                    error_log("Error sending message: " . mysqli_error($connection));
                    $_SESSION['error'] = "Error sending message";
                }
            } else {
                error_log("Error creating conversation: " . mysqli_error($connection));
                $_SESSION['error'] = "Error creating conversation";
            }
        } elseif ($currentConversationID > 0) {
            $insertQuery = "INSERT INTO tblmessages (conversationID, senderID, receiverID, messageText) 
                            VALUES ('$currentConversationID', '$userID', '$receiverID', '$messageText')";
            
            if (mysqli_query($connection, $insertQuery)) {
                $updateConv = "UPDATE tblconversations SET lastMessageTime = NOW() WHERE conversationID = '$currentConversationID'";
                mysqli_query($connection, $updateConv);
                
                header("Location: mChat.php?conversationID=$currentConversationID");
                exit();
            } else {
                error_log("Error sending message: " . mysqli_error($connection));
                $_SESSION['error'] = "Error sending message";
            }
        }
    } else {
        $_SESSION['error'] = "Please enter a message.";
    }
    
    // Redirect back to current view
    if ($currentConversationID > 0) {
        header("Location: mChat.php?conversationID=$currentConversationID");
    } elseif ($receiverID > 0) {
        header("Location: mChat.php?start_chat_with=$receiverID");
    } else {
        header("Location: mChat.php");
    }
    exit();
}

// check if convo exist or new convo
if ($startChatWithUserID > 0 && $conversationID == 0) {
    $checkQuery = "SELECT conversationID FROM tblconversations 
                    WHERE (user1ID = '$userID' AND user2ID = '$startChatWithUserID')
                    OR (user1ID = '$startChatWithUserID' AND user2ID = '$userID')
                    LIMIT 1";
    
    $checkResult = mysqli_query($connection, $checkQuery);
    
    if ($checkResult && mysqli_num_rows($checkResult) > 0) {
        // if convo exists, get the ID
        $existingConv = mysqli_fetch_assoc($checkResult);
        $conversationID = $existingConv['conversationID'];
        
        // redirect to the existing conversation
        header("Location: mChat.php?conversationID=$conversationID");
        exit();
    } else {
        // temporary chat mode
        $temporaryChat = true;
        
        $otherUserQuery = "SELECT userID, username, fullName FROM tblusers WHERE userID = '$startChatWithUserID'";
        $otherUserResult = mysqli_query($connection, $otherUserQuery);
        
        if ($otherUserResult && mysqli_num_rows($otherUserResult) > 0) {
            $otherUserData = mysqli_fetch_assoc($otherUserResult);
            $otherUser = [
                'id' => $otherUserData['userID'],
                'name' => $otherUserData['username'],
                'fullName' => $otherUserData['fullName']
            ];
        } else {
            $_SESSION['error'] = "User not found.";
            header("Location: mChat.php");
            exit();
        }
    }
}

// if conversation selected, fetch messages
if ($conversationID > 0) {
    // conversation details
    $convQuery = "SELECT c.*, 
                   u1.username as user1Name, u1.fullName as user1FullName, 
                   u2.username as user2Name, u2.fullName as user2FullName
                   FROM tblconversations c
                   LEFT JOIN tblusers u1 ON c.user1ID = u1.userID
                   LEFT JOIN tblusers u2 ON c.user2ID = u2.userID
                   WHERE c.conversationID = '$conversationID' 
                   AND (c.user1ID = '$userID' OR c.user2ID = '$userID')";
    
    $convResult = mysqli_query($connection, $convQuery);
    
    if ($convResult && mysqli_num_rows($convResult) > 0) {
        $conv = mysqli_fetch_assoc($convResult);
        
        // Determine other user
        if ($conv['user1ID'] == $userID) {
            $otherUser = [
                'id' => $conv['user2ID'],
                'name' => $conv['user2Name'] ?: 'Deleted User',
                'fullName' => $conv['user2FullName'] ?: 'Deleted User'
            ];
        } else {
            $otherUser = [
                'id' => $conv['user1ID'],
                'name' => $conv['user1Name'] ?: 'Deleted User',
                'fullName' => $conv['user1FullName'] ?: 'Deleted User'
            ];
        }
        
        $messagesQuery = "SELECT m.*, 
                          COALESCE(u.username, 'Deleted User') as username, 
                          COALESCE(u.fullName, 'Deleted User') as fullName
                          FROM tblmessages m
                          LEFT JOIN tblusers u ON m.senderID = u.userID
                          WHERE m.conversationID = '$conversationID'
                          ORDER BY m.sentAt ASC";
        
        $messagesResult = mysqli_query($connection, $messagesQuery);
        
        if ($messagesResult) {
            while ($row = mysqli_fetch_assoc($messagesResult)) {
                $messages[] = $row;
            }
        }
        
        // Mark messages as read
        $markRead = "UPDATE tblmessages SET isRead = TRUE 
                     WHERE conversationID = '$conversationID' 
                     AND receiverID = '$userID' 
                     AND isRead = FALSE";
        mysqli_query($connection, $markRead);
    }
}

// Fetch all conversations for the current user
$conversationsQuery = "SELECT c.*, 
                       u1.username as user1Name, u1.fullName as user1FullName,
                       u2.username as user2Name, u2.fullName as user2FullName,
                       (SELECT COUNT(*) FROM tblmessages WHERE conversationID = c.conversationID 
                        AND receiverID = '$userID' AND isRead = FALSE) as unreadCount,
                       (SELECT messageText FROM tblmessages WHERE conversationID = c.conversationID 
                        ORDER BY sentAt DESC LIMIT 1) as lastMessage
                       FROM tblconversations c
                       LEFT JOIN tblusers u1 ON c.user1ID = u1.userID
                       LEFT JOIN tblusers u2 ON c.user2ID = u2.userID
                       WHERE c.user1ID = '$userID' OR c.user2ID = '$userID'
                       ORDER BY c.lastMessageTime DESC";
$conversationsResult = mysqli_query($connection, $conversationsQuery);

$conversations = [];
if ($conversationsResult) {
    while ($row = mysqli_fetch_assoc($conversationsResult)) {
        // Determine the other user in conversation
        if ($row['user1ID'] == $userID) {
            $row['otherUserID'] = $row['user2ID'];
            $row['otherUsername'] = $row['user2Name'] ?: 'Deleted User';
            $row['otherUserFullName'] = $row['user2FullName'] ?: 'Deleted User';
        } else {
            $row['otherUserID'] = $row['user1ID'];
            $row['otherUsername'] = $row['user1Name'] ?: 'Deleted User';
            $row['otherUserFullName'] = $row['user1FullName'] ?: 'Deleted User';
        }
        $conversations[] = $row;
    }
}

// Calculate unread count for JavaScript
$unreadCount = 0;
foreach ($conversations as $conv) {
    $unreadCount += $conv['unreadCount'];
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

    <style>
        /* to debug */
        
    </style>
</head>

<body>
    <div id="cover" class="" onclick="hideMenu()"></div>

    <!-- Trade Modal -->
    <div class="trade-modal-overlay" id="tradeModalOverlay">
        <div class="trade-modal" id="tradeModal">
            <button class="trade-modal-close" onclick="closeTradeModal()">&times;</button>
            
            <!-- Initial Choice Screen -->
            <div id="tradeChoiceScreen">
                <div class="trade-modal-header">
                    <h2 class="trade-modal-title">Trading</h2>
                </div>
                <div class="trade-modal-content">
                    <button class="trade-choice-button" onclick="showRequestScreen()">
                        I want to request a listing
                    </button>
                    <button class="trade-choice-button" onclick="showOfferScreen()">
                        I want to offer a listing
                    </button>
                </div>
            </div>

            <!-- Request Screen -->
            <div id="tradeRequestScreen" style="display: none;">
                <div class="trade-modal-header">
                    <button class="trade-modal-back" onclick="backToChoice()">
                        <img src="../../assets/images/icon-back-light.svg" alt="Back" id="request-back-icon" />
                    </button>
                    <h2 class="trade-modal-title">Request Listing</h2>
                </div>
                <div class="trade-modal-content">
                    <label class="trade-select-label">I would like to request:</label>
                    <select class="c-input c-input-select" id="requestListingSelect" onchange="previewRequestListing()">
                        <option value="">Select a listing...</option>
                    </select>
                    
                    <!-- if no listings -->
                    <div class="no-listings-message" id="noListingsMessageRequest"></div>
                    
                    <div class="trade-listing-preview" id="requestPreview">
                        <img id="requestPreviewImage" src="" alt="">
                        <div class="trade-listing-details">
                            <h4 id="requestPreviewTitle"></h4>
                            <p id="requestPreviewCategory"></p>
                        </div>
                    </div>
                    
                    <button class="trade-send-button" id="requestSendBtn" disabled onclick="sendTradeRequest('request')">
                        Send Request
                    </button>
                </div>
            </div>

            <!-- Offer Screen -->
            <div id="tradeOfferScreen" style="display: none;">
                <div class="trade-modal-header">
                    <button class="trade-modal-back" onclick="backToChoice()">
                        <img src="../../assets/images/icon-back-light.svg" alt="Back" id="offer-back-icon" />
                    </button>
                    <h2 class="trade-modal-title">Offer Listing</h2>
                </div>
                <div class="trade-modal-content">
                    <label class="trade-select-label">I would like to offer:</label>
                    <select class="c-input c-input-select" id="offerListingSelect" onchange="previewOfferListing()">
                        <option value="">Select a listing...</option>
                    </select>

                    <!-- if no listings -->
                    <div class="no-listings-message" id="noListingsMessageOffer"></div>
                    
                    <div class="trade-listing-preview" id="offerPreview">
                        <img id="offerPreviewImage" src="" alt="">
                        <div class="trade-listing-details">
                            <h4 id="offerPreviewTitle"></h4>
                            <p id="offerPreviewCategory"></p>
                        </div>
                    </div>
                    
                    <button class="trade-send-button" id="offerSendBtn" disabled onclick="sendTradeRequest('offer')">
                        Send Offer
                    </button>
                </div>
            </div>
        </div>
    </div>

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
                                <li class="<?php echo $conv['conversationID'] == $conversationID ? 'active' : ''; ?>">
                                    <a href="mChat.php?conversationID=<?php echo $conv['conversationID']; ?>">
                                        <div>
                                            <span class="content-message-avatar avatar-initials">
                                                <?php echo getInitials($conv['otherUserFullName']); ?>
                                            </span>
                                            <span class="content-message-info">
                                                <span class="content-message-name"><?php echo htmlspecialchars($conv['otherUsername']); ?></span>
                                                <span class="content-message-text">
                                                    <?php 
                                                    $lastMessage = $conv['lastMessage'] ?? 'No messages yet';
                                                    if (strpos($lastMessage, 'TRADE_REQUEST:') === 0) {
                                                        echo 'Trade request sent';
                                                    } else {
                                                        echo htmlspecialchars($lastMessage);
                                                    }
                                                    ?>
                                                </span>
                                            </span>
                                            <span class="content-message-more">
                                                <span class="content-message-time">
                                                    <?php 
                                                    echo date('g:i A', strtotime($conv['lastMessageTime']));
                                                    ?>
                                                </span>
                                                <?php if ($conv['unreadCount'] > 0): ?>
                                                    <span class="content-message-unread"><?php echo $conv['unreadCount']; ?></span>
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
            <?php if (!$conversationID && !$temporaryChat): ?>
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
                                <?php echo getInitials($otherUser['fullName']); ?>
                            </div>
                            <div>
                                <div class="conversation-user-name"><?php echo htmlspecialchars($otherUser['name']); ?></div>
                                <?php if ($temporaryChat): ?>
                                    <small style="color: var(--MainGreen); font-size: 0.8rem;">New conversation</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Alert Messages -->

                    <!-- PHP error handling: database/connection issues -->
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-error">
                            <?php 
                            echo htmlspecialchars($_SESSION['error']); 
                            unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php 
                            echo htmlspecialchars($_SESSION['success']); 
                            unset($_SESSION['success']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <!-- JS Error handling: logic issues -->
                    <div class="alert alert-error" id="jsAlertError" style="display: none;"></div>
                    <div class="alert alert-success" id="jsAlertSuccess" style="display: none;"></div>

                    <!-- Main Chatbox -->
                    <div class="conversation-main" id="conversationMain">
                        <ul class="conversation-wrapper">
                            <?php if (empty($messages)): ?>
                                <div class="conversation-divider"><span>Start Conversation</span></div>
                                <div class="empty-conversation">
                                    <i class="ri-chat-1-line"></i>
                                    <p>No messages yet</p>
                                    <small>Trading only works after conversation is started</small>
                                </div>
                            <?php else: ?>
                                <?php 
                                $lastDate = '';
                                foreach ($messages as $msg): 
                                    $msgDate = date('Y-m-d', strtotime($msg['sentAt']));
                                    $isMe = $msg['senderID'] == $userID;
                                    
                                    if ($lastDate != $msgDate) {
                                        $lastDate = $msgDate;
                                        $displayDate = date('F j, Y', strtotime($msg['sentAt']));
                                        echo '<div class="conversation-divider"><span>' . $displayDate . '</span></div>';
                                    }
                                    
                                    // identify the msg type is text or trade request
                                    if (strpos($msg['messageText'], 'TRADE_REQUEST:') === 0) {
                                        $requestID = (int)str_replace('TRADE_REQUEST:', '', $msg['messageText']);
                                        $requestData = getTradeRequestData($requestID, $connection, $userID);
                                        ?>
                                        <li class="conversation-item <?php echo $isMe ? 'me' : ''; ?>">
                                            <div class="conversation-item-side">
                                                <div class="conversation-item-avatar avatar-initials">
                                                    <?php echo getInitials($msg['fullName']); ?>
                                                </div>
                                            </div>
                                            <div class="conversation-item-content">
                                                <div class="conversation-item-wrapper">
                                                    <div class="conversation-item-box" style="max-width: 100%;">
                                                        <!-- Trade request -->
                                                        <div class="trade-request-container">
                                                            <div class="trade-request-header">
                                                                <strong><?php echo $requestData['senderDisplay']; ?></strong> 
                                                                <?php echo $requestData['requestTypeText']; ?> this item:
                                                            </div>
                                                            
                                                            <!-- Listing preview -->
                                                            <?php if ($requestData['listingDeleted']): ?>
                                                                <div class="trade-request-preview deleted-listing">
                                                                    <img src="../../assets/images/placeholder-image.jpg" alt="Deleted Listing">
                                                                    <div class="trade-request-info">
                                                                        <h4>[Listing Deleted]</h4>
                                                                        <p class="trade-category">This listing is no longer available</p>
                                                                    </div>
                                                                </div>
                                                            <?php else: ?>
                                                                <div class="trade-request-preview">
                                                                    <img src="<?php echo htmlspecialchars($requestData['imageUrl']); ?>" 
                                                                         alt="<?php echo htmlspecialchars($requestData['title']); ?>"
                                                                         onerror="this.src='../../assets/images/placeholder-image.jpg'">
                                                                    <div class="trade-request-info">
                                                                        <h4><?php echo htmlspecialchars($requestData['title']); ?></h4>
                                                                        <p class="trade-category"><?php echo htmlspecialchars($requestData['category']); ?></p>
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?>
                                                            
                                                            <!-- Action buttons -->
                                                            <div class="trade-request-actions">
                                                                <?php if ($requestData['isCancelledDueToDeletion']): ?>
                                                                    <button class="trade-btn-disabled" disabled>This offer/request is cancelled</button>
                                                                <?php elseif ($requestData['status'] === 'accepted'): ?>
                                                                    <button class="trade-btn-disabled" disabled>Accepted</button>
                                                                <?php elseif ($requestData['status'] === 'declined'): ?>
                                                                    <button class="trade-btn-disabled" disabled>Declined</button>
                                                                <?php elseif ($requestData['status'] === 'cancelled'): ?>
                                                                    <button class="trade-btn-disabled" disabled>Cancelled</button>
                                                                <?php elseif ($requestData['isExpired']): ?>
                                                                    <button class="trade-btn-disabled" disabled>Expired</button>
                                                                <?php elseif ($requestData['isListingInactive']): ?>
                                                                    <button class="trade-btn-disabled" disabled>Unavailable</button>
                                                                <?php elseif ($requestData['isSender']): ?>
                                                                    <button class="trade-btn-cancel" 
                                                                            onclick="respondTradeRequest(<?php echo $requestID; ?>, 'cancel')">
                                                                        Cancel
                                                                    </button>
                                                                <?php else: ?>
                                                                    <button class="trade-btn-accept" 
                                                                            onclick="respondTradeRequest(<?php echo $requestID; ?>, 'accept')">
                                                                        Accept
                                                                    </button>
                                                                    <button class="trade-btn-decline" 
                                                                            onclick="respondTradeRequest(<?php echo $requestID; ?>, 'decline')">
                                                                        Decline
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                            
                                                            <!-- for pending requests -->
                                                            <?php if ($requestData['status'] === 'pending' && !$requestData['isCancelledDueToDeletion']): ?>
                                                                <p class="trade-request-note">* Offer/request will expire after 30 days</p>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <div class="conversation-item-time">
                                                            <?php echo date('g:i A', strtotime($msg['sentAt'])); ?>
                                                            <?php if ($isMe && $msg['isRead']): ?>
                                                                <span style="margin-left: 4px;">✓✓</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <?php
                                    } else {
                                ?>
                                    <li class="conversation-item <?php echo $isMe ? 'me' : ''; ?>">
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
                                                            <?php if ($isMe && $msg['isRead']): ?>
                                                                <span style="margin-left: 4px;">✓✓</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php 
                                    }
                                endforeach; 
                                ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                                                                
                    <?php if ($conversationID > 0 || $temporaryChat): ?>
                        <div class="conversation-form">
                            <button type="button" class="trade-form-button" onclick="openTradeModal()" title="Trade">
                                <span class="trade-icon"></span>
                            </button>
                            
                            <form method="POST" id="messageForm">
                                <input type="hidden" name="receiverID" value="<?php echo $otherUser['id']; ?>">
                                <input type="hidden" name="conversationID" value="<?php echo $conversationID; ?>">
                                
                                <div class="conversation-form-group">
                                    <textarea class="conversation-form-input" 
                                            name="message" 
                                            rows="1" 
                                            placeholder="Type your message..."
                                            required></textarea>
                                </div>
                                
                                <button type="submit" name="send_message" class="conversation-form-button">
                                    <img src="../../assets/images/send-icon.svg" alt="send"/>
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
        const unreadCount = <?php echo $unreadCount; ?>;

        const currentConversationID = <?php echo $conversationID ?: 0; ?>;
        const otherUserID = <?php echo $otherUser['id'] ?? 0; ?>;
        let currentListings = [];

        // Trade Modal Functions
        function openTradeModal() {
            document.getElementById('tradeModalOverlay').classList.add('active');
            showChoiceScreen();
        }

        function closeTradeModal() {
            document.getElementById('tradeModalOverlay').classList.remove('active');
            resetModal();
        }

        function showChoiceScreen() {
            document.getElementById('tradeChoiceScreen').style.display = 'block';
            document.getElementById('tradeRequestScreen').style.display = 'none';
            document.getElementById('tradeOfferScreen').style.display = 'none';
        }

        function backToChoice() {
            showChoiceScreen();
            resetSelections();
        }

        function resetModal() {
            showChoiceScreen();
            resetSelections();
        }

        function resetSelections() {
            document.getElementById('requestListingSelect').value = '';
            document.getElementById('offerListingSelect').value = '';
            document.getElementById('requestPreview').classList.remove('active');
            document.getElementById('offerPreview').classList.remove('active');
            document.getElementById('requestSendBtn').disabled = true;
            document.getElementById('offerSendBtn').disabled = true;
        }

        async function showRequestScreen() {
            document.getElementById('tradeChoiceScreen').style.display = 'none';
            document.getElementById('tradeRequestScreen').style.display = 'block';
            
            // Fetch other user's listings
            await fetchListings('request');
        }

        async function showOfferScreen() {
            document.getElementById('tradeChoiceScreen').style.display = 'none';
            document.getElementById('tradeOfferScreen').style.display = 'block';
            
            // Fetch current user's listings
            await fetchListings('offer');
        }

        async function fetchListings(type) {
            try {
                const formData = new FormData();
                formData.append('action', 'getListings');
                formData.append('targetUserID', otherUserID);
                formData.append('requestType', type);
                
                const response = await fetch('../../php/tradeRequest.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    currentListings = data.listings;
                    populateListingSelect(type, data.listings);
                } else {
                    closeTradeModal();
                    showAlert('Error loading listings', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                closeTradeModal();
                showAlert('Error loading listings', 'error');
            }
        }

        function populateListingSelect(type, listings) {
            const selectID = type === 'request' ? 'requestListingSelect' : 'offerListingSelect';
            const messageID = type === 'request' ? 'noListingsMessageRequest' : 'noListingsMessageOffer';
            const select = document.getElementById(selectID);
            const messageDiv = document.getElementById(messageID);
            
            select.innerHTML = '<option value="">Select a listing...</option>';
            
            // Clear any existing message first
            if (messageDiv) {
                messageDiv.innerHTML = '';
            }
            
            if (listings.length === 0) {
                select.disabled = true;
                messageDiv.style.display = "block";
                if (messageDiv) {
                    messageDiv.innerHTML = `
                        <p>
                            ${type === 'request' ? 'This user has no active listings at the moment.' : 'You have no active listings to offer.'}
                        </p>
                        <small>
                            ${type === 'request' ? 'Please check back later.' : 'Create a listing first to make an offer.'}
                        </small>
                    `;
                }
            } else {
                select.disabled = false;
                listings.forEach(listing => {
                    const option = document.createElement('option');
                    option.value = listing.listingID;
                    
                    // Truncate long titles
                    let title = listing.title;
                    if (title.length > 40) {
                        title = title.substring(0, 37) + '...';
                    }
                    
                    option.textContent = title;
                    option.dataset.image = listing.imageUrl || '../../assets/images/placeholder-image.jpg';
                    option.dataset.category = listing.category;
                    option.dataset.fullTitle = listing.title;
                    
                    select.appendChild(option);
                });
            }
        }

        function previewRequestListing() {
            previewListing('request');
        }

        function previewOfferListing() {
            previewListing('offer');
        }

        function previewListing(type) {
            const selectID = type === 'request' ? 'requestListingSelect' : 'offerListingSelect';
            const previewID = type === 'request' ? 'requestPreview' : 'offerPreview';
            const btnID = type === 'request' ? 'requestSendBtn' : 'offerSendBtn';
            
            const select = document.getElementById(selectID);
            const preview = document.getElementById(previewID);
            const btn = document.getElementById(btnID);
            
            if (select.value) {
                const option = select.options[select.selectedIndex];
                
                const imageID = type === 'request' ? 'requestPreviewImage' : 'offerPreviewImage';
                const titleID = type === 'request' ? 'requestPreviewTitle' : 'offerPreviewTitle';
                const categoryID = type === 'request' ? 'requestPreviewCategory' : 'offerPreviewCategory';
                
                document.getElementById(imageID).src = option.dataset.image;
                document.getElementById(titleID).textContent = option.dataset.fullTitle;
                document.getElementById(categoryID).textContent = 'Category: ' + option.dataset.category;
                
                preview.classList.add('active');
                btn.disabled = false;
            } else {
                preview.classList.remove('active');
                btn.disabled = true;
            }
        }

        async function sendTradeRequest(type) {
            const selectID = type === 'request' ? 'requestListingSelect' : 'offerListingSelect';
            const listingID = document.getElementById(selectID).value;
            
            if (!listingID) {
                closeTradeModal();
                showAlert('Please select a listing', 'error');
                return;
            }
            
            if (currentConversationID === 0) {
                closeTradeModal();
                showAlert('Please send a message first to start the conversation', 'error');
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'sendRequest');
                formData.append('conversationID', currentConversationID);
                formData.append('receiverID', otherUserID);
                formData.append('listingID', listingID);
                formData.append('requestType', type);
                
                const response = await fetch('../../php/tradeRequest.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    closeTradeModal();
                    
                    // Reload page to show the trade request
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert(data.message || 'Error occurred, please try again', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error occurred, please try again', 'error');
            }
        }

        async function respondTradeRequest(requestID, response) {
            if (!confirm(`Are you sure you want to ${response} this trade request?`)) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'respondRequest');
                formData.append('requestID', requestID);
                formData.append('response', response);
                
                const res = await fetch('../../php/tradeRequest.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await res.json();
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    
                    // Reload page to update UI
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert(data.message || 'Error occurred', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error occurred, please try again', 'error');
            }
        }

        function showAlert(message, type) {
            // Hide alert containers first
            const jsErrorAlert = document.getElementById('jsAlertError');
            const jsSuccessAlert = document.getElementById('jsAlertSuccess');
            
            if (type === 'error') {
                alertDiv = jsErrorAlert;
            } else if (type === 'success') {
                alertDiv = jsSuccessAlert;
            }
            
            // If JS alert container exists, use it
            if (alertDiv) {
                alertDiv.textContent = message;
                alertDiv.style.display = 'block';
                alertDiv.style.opacity = '1';
                alertDiv.style.transform = 'translateY(0)';
                
                // Auto-hide after 3 seconds
                setTimeout(() => {
                    alertDiv.style.opacity = '0';
                    alertDiv.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        alertDiv.style.display = 'none';
                    }, 300);
                }, 3000);
            } 
        }

        // Close modal when clicking outside
        document.getElementById('tradeModalOverlay').addEventListener('click', function(e) {
            if (e.target === this) {
                closeTradeModal();
            }
        });

        function showConversationList() {
            const sidebar = document.querySelector('.content-sidebar');
            const conversation = document.querySelector('.conversation.active');
            const conversationDefault = document.querySelector('.conversation-default');
            
            if (window.innerWidth <= 768) {
                if (sidebar) sidebar.style.display = 'flex';
                if (conversation) conversation.style.display = 'none';
                if (conversationDefault) conversationDefault.style.display = 'none';
                
                const newUrl = window.location.origin + window.location.pathname;
                window.history.pushState({}, '', newUrl);
            } else {
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

            const urlParams = new URLSearchParams(window.location.search);
            const conversationID = urlParams.get('conversationID');
            const startChatWith = urlParams.get('start_chat_with');
            
            if (window.innerWidth <= 768 && (conversationID || startChatWith)) {
                document.querySelector('.content-sidebar').style.display = 'none';
            }

            window.addEventListener('popstate', function(event) {
                if (window.innerWidth <= 768) {
                    showConversationList();
                }
            });

            const messageForm = document.getElementById('messageForm');
            if (messageForm) {
                messageForm.addEventListener('submit', function() {
                    if (window.innerWidth <= 768) {
                        sessionStorage.setItem('shouldShowConversation', 'true');
                    }
                });
            }
            
            if (window.innerWidth <= 768 && sessionStorage.getItem('shouldShowConversation') === 'true') {
                sessionStorage.removeItem('shouldShowConversation');
                setTimeout(() => {
                    const currentUrlParams = new URLSearchParams(window.location.search);
                    if (!currentUrlParams.has('conversationID') && !currentUrlParams.has('start_chat_with')) {
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
                document.querySelector('.content-sidebar').style.display = 'flex';
            }
        });
    </script>
    <script src="../../javascript/mainScript.js"></script>
</body>
</html>

<?php mysqli_close($connection); ?> 