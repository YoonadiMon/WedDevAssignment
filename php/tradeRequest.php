<?php
session_start();
include("dbConn.php");
include("sessionCheck.php");
include('tradeCompleted.php');

header('Content-Type: application/json');

if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$userID = $_SESSION['userID'];

// Handle different actions
switch ($action) {
    case 'getListings':
        $targetUserID = $_POST['targetUserID'];
        $requestType = $_POST['requestType'];
        
        $ownerID = ($requestType === 'request') ? $targetUserID : $userID;
        
        // Check if target user exists
        $checkUserQuery = "SELECT userID FROM tblusers WHERE userID = $targetUserID";
        $checkUserResult = mysqli_query($connection, $checkUserQuery);
        
        if (mysqli_num_rows($checkUserResult) === 0) {
            echo json_encode(['success' => true, 'listings' => [], 'userDeleted' => true]);
            break;
        }
        
        $query = "SELECT listingID, title, category, imageUrl 
                  FROM tbltrade_listings 
                  WHERE userID = $ownerID AND status = 'active'
                  ORDER BY dateListed DESC";
        
        $result = mysqli_query($connection, $query);
        $listings = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $listings[] = $row;
        }
        
        echo json_encode(['success' => true, 'listings' => $listings, 'userDeleted' => false]);
        break;
        
    case 'sendRequest':
        $conversationID = (int)$_POST['conversationID'];
        $receiverID = (int)$_POST['receiverID'];
        $listingID = (int)$_POST['listingID'];
        $requestType = $_POST['requestType'];
        
        // Check if receiver user exists
        $checkReceiverQuery = "SELECT userID FROM tblusers WHERE userID = $receiverID";
        $checkReceiverResult = mysqli_query($connection, $checkReceiverQuery);
        if (mysqli_num_rows($checkReceiverResult) === 0) {
            echo json_encode(['success' => false, 'message' => 'User is no longer available']);
            exit;
        }
        
        // Verify listing is still active
        $verifyQuery = "SELECT listingID FROM tbltrade_listings WHERE listingID = $listingID AND status = 'active'";
        $verifyResult = mysqli_query($connection, $verifyQuery);
        if (mysqli_num_rows($verifyResult) === 0) {
            echo json_encode(['success' => false, 'message' => 'Listing is no longer available']);
            exit;
        }
        
        // Calculate expiry date (30 days from now)
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        // Insert trade request
        $insertQuery = "INSERT INTO tbltrade_requests (conversationID, senderID, receiverID, listingID, requestType, expiresAt)
                        VALUES ($conversationID, $userID, $receiverID, $listingID, '$requestType', '$expiresAt')";
        
        if (mysqli_query($connection, $insertQuery)) {
            $requestID = mysqli_insert_ID($connection);
            
            // Insert message notification
            $messageText = "TRADE_REQUEST:" . $requestID;
            $msgQuery = "INSERT INTO tblmessages (conversationID, senderID, receiverID, messageText)
                         VALUES ($conversationID, $userID, $receiverID, '$messageText')";
            mysqli_query($connection, $msgQuery);
            
            // Update conversation time
            $updateConv = "UPDATE tblconversations SET lastMessageTime = NOW() WHERE conversationID = $conversationID";
            mysqli_query($connection, $updateConv);
            
            echo json_encode(['success' => true, 'message' => ucfirst($requestType) . ' sent successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send request']);
        }
        break;
        
    case 'respondRequest':
        $requestID = (int)$_POST['requestID'];
        $response = $_POST['response'];
        
        // Get request details
        $getQuery = "SELECT tr.*, tl.userID as listingOwnerID
                    FROM tbltrade_requests tr
                    LEFT JOIN tbltrade_listings tl ON tr.listingID = tl.listingID
                    WHERE tr.requestID = $requestID";
        $getResult = mysqli_query($connection, $getQuery);
        
        if ($row = mysqli_fetch_assoc($getResult)) {
            // Update request status
            $newStatus = ($response === 'accept') ? 'accepted' : (($response === 'decline') ? 'declined' : 'cancelled');
            $updateQuery = "UPDATE tbltrade_requests SET status = '$newStatus' WHERE requestID = $requestID";
            
            if (mysqli_query($connection, $updateQuery)) {
                if ($response === 'accept') {
                    $ownerID = $row['listingOwnerID'];
                    $buyerID = ($row['requestType'] === 'offer') ? $row['receiverID'] : $row['senderID'];
                    $listingID = $row['listingID'];
                    
                    // run tradeCompleted.php function to update listing and user
                    $tradeResult = completeTrade($ownerID, $buyerID, $listingID, $userID, $connection);
                    echo json_encode($tradeResult); 
                } else {
                    echo json_encode(['success' => true, 'message' => 'Request ' . $newStatus]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update request']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Request not found']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

mysqli_close($connection);
?>