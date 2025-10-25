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
    <title>Chat - ReLeaf</title>
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">

    <style>
        body {
            max-width: 1400px;
            margin: 0;
        }

        /* Alert error Messages */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem;
            text-align: center;
            font-weight: 500;
            z-index: 1000;
            background: transparent;
        }

        .alert-error {
            background: var(--bg-color);
            color: var(--Red);
            border: 2px solid var(--Red);
        }

        .chat-container {
            width: 100vw;
            display: flex;
            align-items: stretch;
            justify-content: stretch;
            background-color: var(--bg-color-light);
            overflow: hidden;
        }

        .chat-content {
            flex: 1;
            display: flex;
            overflow: hidden;
            height: 100vh;
            margin-left: 0; 
            max-height: 100vh;
        }

        /* Sidebar Styles */
        .content-sidebar {
            width: 20rem;
            min-width: 20rem;
            background-color: var(--White);
            display: flex;
            flex-direction: column;
            height: 100vh;
            border-right: 1px solid var(--border-color);
            flex-shrink: 0;
            max-height: 100vh;
            overflow: hidden;
        }

        .content-sidebar-title {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 1.25rem;
            font-weight: 700;
            padding: 1rem;
        }

        .desktop-back-button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 1.5rem;
            height: auto;
            font-size: 0.875rem;
            cursor: pointer;
            padding: 0;
            margin: 0;
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            font-weight: 600;
        }

        .desktop-back-button:hover {
            background-color: var(--bg-color-light);
            transform: translateX(-0.125rem);
        }

        .content-sidebar-form {
            position: relative;
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .content-sidebar-form input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 1.25rem;
            background-color: var(--bg-color-light);
            font-size: 0.875rem;
            transition: all 0.2s ease;
            appearance: none;
        }

        .content-sidebar-form input:focus {
            outline: none;
            border-color: var(--btn-color);
            background-color: var(--White);
        }

        /* Message List Styles */
        .content-messages {
            overflow-y: auto;
            height: 100%;
            margin-top: 0;
            flex: 1;
        }

        .content-messages-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .content-message-title {
            margin: 0 1.25rem;
            color: var(--text-color-2);
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            margin-top: 1rem;
            position: relative;
            text-transform: uppercase;
            letter-spacing: 0.03125rem;
        }

        .content-message-title > * {
            position: relative;
            z-index: 1;
            padding: 0 0.5rem 0 0;
            background-color: var(--White);
        }

        .content-message-title::before {
            content: "";
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            left: 0;
            width: 100%;
            height: 0;
            z-index: 0;
            border-bottom: 1px solid var(--border-color);
        }

        .content-messages-list a {
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 1rem 1.25rem;
            margin-left: 0.5rem;
            transition: all 0.2s ease;
            border-bottom: 1px solid var(--border-color);
            color: inherit;
            min-height: 5rem;
            position: relative;
        }
        
        .content-messages-list a > div {
            display: flex;
            align-items: center;
            width: 100%;
            gap: 0.875rem;
        }

        .content-messages-list a:hover {
            background-color: var(--bg-color-light);
            transform: scale(1.05);
        }

        .content-messages-list > .active > a {
            background-color: var(--LightGreen);
            border-right: 0.1875rem solid var(--btn-color);
        }

        .content-message-avatar {
            width: 3rem;
            height: 3rem;
            margin-top: 0.25rem;
            flex-shrink: 0;
        }

        .avatar-initials {
            border-radius: 50%;
            background: var(--MainGreen);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: 700;
            color: var(--White);
            flex-shrink: 0;
        }

        .content-message-info {
            flex: 1;
            min-width: 0;
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            padding-right: 0.5rem;
        }

        .content-message-name {
            display: block;
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-heading);
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
            line-height: 1.3;
            width: 100%;
        }

        .content-message-text {
            font-size: 0.8125rem;
            color: var(--text-color-2);
            line-height: 1.3;
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: calc(100% - 3rem);
        }

        .content-message-more {
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: flex-start;
            gap: 0.25rem;
            position: absolute;
            right: 1.25rem;
            top: 1rem;
            flex-shrink: 0;
            min-width: 3.5rem;
            max-width: 4rem;
        }

        .content-message-unread {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--White);
            background-color: var(--btn-color);
            padding: 0.1875rem 0.375rem;
            border-radius: 0.625rem;
            min-width: 1.125rem;
            text-align: center;
        }

        .content-message-time {
            font-size: 0.75rem;
            color: var(--Gray);
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        /* Conversation Styles */
        .conversation {
            flex: 1;
            display: none;
            flex-direction: column;
            background-color: var(--bg-color-light-3);
            overflow: hidden;
            margin: 0;
            padding: 0;
        }

        .conversation-default {
            display: none;
            align-items: center;
            justify-content: center;
            padding: 2.5rem;
            color: var(--text-color-2);
            text-align: center;
            flex: 1;
            flex-direction: column;
        }

        .conversation-default i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--bg-color-light-2);
        }

        .conversation-default p {
            margin-top: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
        }

        .conversation.active {
            display: flex;
        }

        .conversation-top {
            padding: 1rem 1.5rem;
            background-color: var(--White);
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--bg-color-light);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            flex-shrink: 0;
        }

        .conversation-back {
            background-color: transparent;
            border: none;
            outline: none;
            width: 2.25rem;
            height: 2.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            cursor: pointer;
            color: var(--gray-3);
            margin-right: 1rem;
            display: none;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .conversation-back:hover {
            background-color: var(--gray-1);
            color: var(--gray-4);
        }

        .conversation-user {
            display: flex;
            align-items: center;
        }

        .conversation-user-avatar {
            width: 2.75rem;
            height: 2.75rem;
            margin-right: 0.75rem;
        }

        .conversation-user-name {
            font-weight: 600;
            font-size: 1.125rem;
            color: var(--text-heading);
        }

        .conversation-main {
            overflow-y: auto;
            overflow-x: hidden;
            flex: 1;
            padding: 1.5rem;
            background: linear-gradient(
                to bottom,
                var(--bg-color-light-3),
                var(--bg-color-light-2)
            );
        }

        .conversation-wrapper {
            list-style-type: none;
            max-width: none;
            margin: 0;
            width: 100%;
            padding: 0 1.25rem;
        }

        .conversation-item {
            display: flex;
            align-items: flex-end;
            margin-bottom: 1.25rem;
            gap: 0.75rem;
        }

        /* My messages on the right */
        .conversation-item.me {
            flex-direction: row-reverse;
        }

        /* Other user's messages on the left */
        .conversation-item:not(.me) {
            flex-direction: row;
        }

        .conversation-item-side {
            margin-left: 0.75rem;
            flex-shrink: 0;
        }

        .conversation-item.me .conversation-item-side {
            margin-right: 0.75rem;
            margin-left: 0;
        }

        .conversation-item-avatar {
            width: 2rem;
            height: 2rem;
        }

        .conversation-item-content {
            width: 100%;
        }

        .conversation-item-wrapper:not(:last-child) {
            margin-bottom: 0.375rem;
        }

        /* Message Bubble - Dynamic Width */
        .conversation-item-box {
            max-width: 70%;
            position: relative;
            width: fit-content;
        }

        /* Other user's message box align left */
        .conversation-item:not(.me) .conversation-item-box {
            margin-right: auto;
            margin-left: 0;
        }

        /* My message box align right */
        .conversation-item.me .conversation-item-box {
            margin-left: auto;
            margin-right: 0;
        }

        .conversation-item-text {
            padding: 0.875rem 1.125rem 0.625rem;
            background-color: var(--White);
            box-shadow: 0 2px 8px -1px rgba(0, 0, 0, 0.08);
            font-size: 0.875rem;
            border-radius: 1.125rem;
            line-height: 1.5;
            border: 1px solid var(--border-color);
            position: relative;
            width: fit-content;
            min-width: 3rem;
        }

        .conversation-item.me .conversation-item-text {
            background-color: var(--btn-color);
            box-shadow: 0 2px 12px -3px var(--btn-color-hover);
            color: rgba(255, 255, 255, 0.95);
            border-color: var(--btn-color-hover);
        }

        .conversation-item-text p {
            margin: 0;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
            hyphens: auto;
        }

        .conversation-item-time {
            font-size: 0.625rem;
            color: var(--text-color-2);
            display: block;
            text-align: right;
            margin-top: 0.375rem;
            line-height: 1;
            font-weight: 500;
        }

        .conversation-item.me .conversation-item-time {
            color: var(--Gray);
        }

        .coversation-divider {
            text-align: center;
            font-size: 0.75rem;
            color: var(--text-color-2);
            margin: 2rem 0 1.5rem 0;
            position: relative;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03125rem;
        }

        .coversation-divider::before {
            content: "";
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            left: 0;
            width: 100%;
            height: 0;
            border-bottom: 1px solid var(--border-color);
            z-index: 0;
        }

        .coversation-divider span {
            display: inline-block;
            padding: 0 1rem;
            position: relative;
            z-index: 1;
        }

        /* Form Styles */
        .conversation-form {
            padding: 1rem 1.5rem 1.25rem;
            background-color: var(--White);
            display: flex;
            align-items: flex-end;
            border-top: 1px solid var(--bg-color-light);
            flex-shrink: 0;
        }

        .conversation-form form {
            width: 100%;
            display: flex;
            align-items: flex-end;
            gap: 0.75rem;
        }

        .conversation-form-group {
            flex: 1;
            position: relative;
        }

        .conversation-form-input {
            background-color: var(--bg-color-light);
            border: 1px solid var(--border-color);
            border-radius: 1.5rem;
            outline: transparent;
            padding: 0.875rem 1.25rem;
            font: inherit;
            font-size: 0.875rem;
            resize: none;
            width: 100%;
            display: block;
            line-height: 1.5;
            max-height: calc(1.25rem + ((0.875rem * 1.5) * 6));
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .conversation-form-input:focus {
            border-color: var(--btn-color-hover);
            background-color: var(--White);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .conversation-form-input::placeholder {
            color: var(--text-color-2);
        }

        .conversation-form-button {
            width: 2.75rem;
            height: 2.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            border: none;
            background-color: transparent;
            outline: none;
            font-size: 1.125rem;
            color: inherit;
            cursor: pointer;
            flex-shrink: 0;
            transition: background 0.2s ease;
            padding: 0;
        }

        .conversation-form-button:hover {
            background: var(--bg-color-light);
        }

        .conversation-form-button img {
            width: 24px;
            height: 24px;
        }

        /* Empty State */
        .empty-conversation {
            padding: 2.5rem 1.25rem;
            text-align: center;
            color: var(--text-color-2);
        }

        .empty-conversation i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* === RESPONSIVE DESIGN === */
        /* Tablet */
        @media (max-width: 1024px) {
            .content-sidebar {
                width: 16rem;
                min-width: 16rem;
            }
        }

        /* Mobile */
        @media (max-width: 768px) {
            .chat-container {
                flex-direction: column;
            }

            .content-sidebar {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                border-right: none;
                z-index: 10;
                display: flex;
                flex-direction: column;
            }

            .content-message-text {
                max-width: calc(100% - 1rem);
            }
            
            .content-message-more {
                min-width: 3rem;
                max-width: 3.5rem;
            }
            
            .content-message-info {
                padding-right: 0.5rem;
            }
            
            .content-messages-list a {
                min-height: 4.5rem;
                padding: 0.75rem 1rem;
            }

            .conversation.active {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                z-index: 20;
            }

            .conversation-back {
                display: flex !important;
            }

            .conversation-default.active {
                display: flex;
            }

            .conversation-main {
                padding: 1rem;
            }

            .conversation-form {
                padding: 0.75rem 1rem;
            }

            .conversation-form-input {
                padding: 0.75rem 1rem;
            }

            .conversation-form-button {
                width: 2.5rem;
                height: 2.5rem;
            }

            .conversation-item {
                align-items: flex-start !important;
                gap: 0.5rem;
            }

            .conversation-item.me {
                flex-direction: row-reverse;
            }

            .conversation-item:not(.me) {
                flex-direction: row;
            }

            .conversation-item-side {
                margin: 0;
                flex-shrink: 0;
            }

            .conversation-item.me .conversation-item-side {
                margin: 0;
            }

            .conversation-item:not(.me) .conversation-item-box {
                margin-left: 0;
            }

            .conversation-wrapper {
                padding: 0 0.625rem;
            }

            .conversation-item-box {
                max-width: 85%;
                min-width: 4rem;
            }

            /* Hide sidebar when conversation is active on mobile */
            .conversation.active ~ .content-sidebar {
                display: none;
            }

            /* Show sidebar when no conversation is active */
            .conversation-default.active {
                display: none;
            }

            .conversation-default.active + .content-sidebar,
            .conversation-default.active ~ .content-sidebar {
                display: flex;
                position: relative;
                z-index: 10;
            }

            .conversation-back {
                display: flex !important;
            }
        }

        /* Small Mobile */
        @media (max-width: 480px) {
            .content-message-more {
                min-width: 2.5rem;
                max-width: 3rem;
            }
            
            .content-message-info {
                padding-right: 0.5rem;
            }
            
            .content-message-time {
                font-size: 0.7rem;
            }
            
            .content-messages-list a {
                min-height: 4rem;
                padding: 0.5rem 0.75rem;
            }
            
            .content-message-avatar {
                width: 2.5rem;
                height: 2.5rem;
                margin-right: 0.75rem;
            }
            
            .conversation-main {
                padding: 0.75rem;
            }

            .conversation-form {
                padding: 0.5rem 0.75rem;
            }

            .conversation-item-box {
                max-width: 90%;
            }

            .conversation-item:not(.me) .conversation-item-box {
                margin-left: 0;
            }

            .conversation-item-text {
                margin-right: 0;
            }

            .content-sidebar-title {
                padding: 1rem;
                font-size: 1.125rem;
            }

            .content-sidebar-form {
                padding: 0 1rem 1rem 1rem;
            }
        }

        /* === DARK MODE === */
        .dark-mode .chat-container {
            background-color: var(--bg-color-dark);
        }

        .dark-mode .content-sidebar {
            background-color: var(--bg-color-dark-2);
            border-right-color: var(--border-color);
        }

        .dark-mode .content-sidebar-title {
            border-bottom-color: var(--border-color);
            color: var(--text-heading);
        }

        .dark-mode .content-sidebar-form input {
            background-color: var(--bg-color-dark);
            border-color: var(--border-color);
            color: var(--text-heading);
        }

        .dark-mode .content-sidebar-form input:focus {
            background-color: var(--bg-color-dark-3);
            border-color: var(--btn-color-hover);
        }

        .dark-mode .content-messages-list a {
            color: var(--text-heading);
        }

        .dark-mode .content-messages-list a:hover {
            background-color: var(--bg-color-dark-3);
        }

        .dark-mode .content-messages-list > .active > a {
            background-color: var(--bg-color-dark-4);
        }

        .dark-mode .content-message-title {
            color: var(--text-color-2);
        }

        .dark-mode .content-message-title > * {
            background-color: var(--bg-color-dark-2);
        }

        .dark-mode .content-message-title::before {
            border-bottom-color: var(--border-color);
        }

        .dark-mode .conversation {
            background-color: var(--bg-color-dark);
        }

        .dark-mode .conversation-top {
            background-color: var(--bg-color-dark-2);
            border-bottom-color: var(--border-color);
        }

        .dark-mode .conversation-main {
            background: linear-gradient(
                to bottom,
                var(--bg-color-dark),
                var(--bg-color-dark-2)
            );
        }

        .dark-mode .conversation-item-text {
            background-color: var(--bg-color-dark-2);
            border-color: var(--border-color);
            color: var(--text-heading);
            box-shadow: 0 2px 8px -1px rgba(0, 0, 0, 0.3);
        }

        .dark-mode .conversation-item.me .conversation-item-text {
            background-color: var(--btn-color);
            border-color: var(--btn-color-hover);
            color: var(--text-color-3);
        }

        .dark-mode .conversation-form {
            background-color: var(--bg-color-dark-2);
            border-top-color: var(--border-color);
        }

        .dark-mode .conversation-form-input {
            background-color: var(--bg-color-dark-3);
            border-color: var(--border-color);
            color: var(--text-heading);
        }

        .dark-mode .conversation-form-input:focus {
            background-color: var(--bg-color-dark-2);
            border-color: var(--btn-color-hover);
        }

        .dark-mode .conversation-form-button:hover {
            background: var(--bg-color-dark-3);
        }

        .dark-mode .conversation-default {
            color: var(--text-color-2);
        }

        .dark-mode .alert-error {
            background: var(--bg-color-dark-3);
            color: var(--Red);
        }
    </style>
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