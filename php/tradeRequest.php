<?php
session_start();
include("dbConn.php");
include("sessionCheck.php");

header('Content-Type: application/json');

if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$userID = $_SESSION['userID'];

// Handle different actions
switch ($action) {
    case 'get_listings':
        $targetUserID = mysqli_real_escape_string($connection, $_POST['targetUserID']);
        $requestType = mysqli_real_escape_string($connection, $_POST['requestType']);
        
        $ownerID = ($requestType === 'request') ? $targetUserID : $userID;
        
        // Check if target user exists
        $checkUserQuery = "SELECT userID FROM tblusers WHERE userID = ?";
        $checkUserStmt = mysqli_prepare($connection, $checkUserQuery);
        mysqli_stmt_bind_param($checkUserStmt, "i", $targetUserID);
        mysqli_stmt_execute($checkUserStmt);
        $checkUserResult = mysqli_stmt_get_result($checkUserStmt);
        
        if (mysqli_num_rows($checkUserResult) === 0) {
            echo json_encode(['success' => true, 'listings' => [], 'userDeleted' => true]);
            mysqli_stmt_close($checkUserStmt);
            break;
        }
        mysqli_stmt_close($checkUserStmt);
        
        $query = "SELECT listingID, title, category, imageUrl 
                  FROM tbltrade_listings 
                  WHERE userID = ? AND status = 'active'
                  ORDER BY dateListed DESC";
        
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "i", $ownerID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $listings = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $listings[] = $row;
        }
        
        echo json_encode(['success' => true, 'listings' => $listings, 'userDeleted' => false]);
        mysqli_stmt_close($stmt);
        break;
        
    case 'send_request':
        $conversationID = (int)$_POST['conversationID'];
        $receiverID = (int)$_POST['receiverID'];
        $listingID = (int)$_POST['listingID'];
        $requestType = mysqli_real_escape_string($connection, $_POST['requestType']);
        
        // Check if receiver user exists
        $checkReceiverQuery = "SELECT userID FROM tblusers WHERE userID = ?";
        $checkReceiverStmt = mysqli_prepare($connection, $checkReceiverQuery);
        mysqli_stmt_bind_param($checkReceiverStmt, "i", $receiverID);
        mysqli_stmt_execute($checkReceiverStmt);
        $checkReceiverResult = mysqli_stmt_get_result($checkReceiverStmt);
        
        if (mysqli_num_rows($checkReceiverResult) === 0) {
            echo json_encode(['success' => false, 'message' => 'User is no longer available']);
            mysqli_stmt_close($checkReceiverStmt);
            exit;
        }
        mysqli_stmt_close($checkReceiverStmt);
        
        // Verify listing is still active
        $verifyQuery = "SELECT listingID FROM tbltrade_listings WHERE listingID = ? AND status = 'active'";
        $verifyStmt = mysqli_prepare($connection, $verifyQuery);
        mysqli_stmt_bind_param($verifyStmt, "i", $listingID);
        mysqli_stmt_execute($verifyStmt);
        $verifyResult = mysqli_stmt_get_result($verifyStmt);
        
        if (mysqli_num_rows($verifyResult) === 0) {
            echo json_encode(['success' => false, 'message' => 'Listing is no longer available']);
            mysqli_stmt_close($verifyStmt);
            exit;
        }
        mysqli_stmt_close($verifyStmt);
        
        // Calculate expiry date (30 days from now)
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        // Insert trade request
        $insertQuery = "INSERT INTO tbltrade_requests (conversationID, senderID, receiverID, listingID, requestType, expiresAt) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $insertStmt = mysqli_prepare($connection, $insertQuery);
        mysqli_stmt_bind_param($insertStmt, "iiiiss", $conversationID, $userID, $receiverID, $listingID, $requestType, $expiresAt);
        
        if (mysqli_stmt_execute($insertStmt)) {
            $requestID = mysqli_insert_id($connection);
            
            // Insert message notification
            $messageText = "TRADE_REQUEST:" . $requestID;
            $msgQuery = "INSERT INTO tblmessages (conversationID, senderID, receiverID, messageText) 
                         VALUES (?, ?, ?, ?)";
            $msgStmt = mysqli_prepare($connection, $msgQuery);
            mysqli_stmt_bind_param($msgStmt, "iiis", $conversationID, $userID, $receiverID, $messageText);
            mysqli_stmt_execute($msgStmt);
            
            // Update conversation time
            $updateConv = "UPDATE tblconversations SET lastMessageTime = NOW() WHERE conversationID = ?";
            $updateStmt = mysqli_prepare($connection, $updateConv);
            mysqli_stmt_bind_param($updateStmt, "i", $conversationID);
            mysqli_stmt_execute($updateStmt);
            
            echo json_encode(['success' => true, 'message' => ucfirst($requestType) . ' sent successfully!']);
            
            mysqli_stmt_close($msgStmt);
            mysqli_stmt_close($updateStmt);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send request']);
        }
        
        mysqli_stmt_close($insertStmt);
        break;
        
    case 'respond_request':
        $requestID = (int)$_POST['requestID'];
        $response = mysqli_real_escape_string($connection, $_POST['response']); // 'accept', 'decline', or 'cancel'
        
        // Get request details
        $getQuery = "SELECT tr.*, tl.userID as listingOwnerID
                     FROM tbltrade_requests tr
                     LEFT JOIN tbltrade_listings tl ON tr.listingID = tl.listingID
                     WHERE tr.requestID = ?";
        $getStmt = mysqli_prepare($connection, $getQuery);
        mysqli_stmt_bind_param($getStmt, "i", $requestID);
        mysqli_stmt_execute($getStmt);
        $getResult = mysqli_stmt_get_result($getStmt);
        
        if ($row = mysqli_fetch_assoc($getResult)) {
            // Check if listing or users still exist
            if ($row['senderID'] === null || $row['receiverID'] === null || $row['listingID'] === null) {
                echo json_encode(['success' => false, 'message' => 'This request is no longer valid due to deleted user or listing']);
                mysqli_stmt_close($getStmt);
                exit;
            }
            
            // Verify user has permission
            if ($response === 'cancel' && $row['senderID'] != $userID) {
                echo json_encode(['success' => false, 'message' => 'Permission denied']);
                mysqli_stmt_close($getStmt);
                exit;
            }
            
            if (($response === 'accept' || $response === 'decline') && $row['receiverID'] != $userID) {
                echo json_encode(['success' => false, 'message' => 'Permission denied']);
                mysqli_stmt_close($getStmt);
                exit;
            }
            
            // Update request status
            $newStatus = ($response === 'accept') ? 'accepted' : (($response === 'decline') ? 'declined' : 'cancelled');
            $updateQuery = "UPDATE tbltrade_requests SET status = ? WHERE requestID = ?";
            $updateStmt = mysqli_prepare($connection, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, "si", $newStatus, $requestID);
            
            if (mysqli_stmt_execute($updateStmt)) {
                // If accepted, complete the trade
                if ($response === 'accept') {
                    $ownerID = $row['listingOwnerID'];
                    $buyerID = ($row['requestType'] === 'offer') ? $row['receiverID'] : $row['senderID'];
                    $listingID = $row['listingID'];
                    
                    // Verify listing is still active
                    $verifyListingQuery = "SELECT status FROM tbltrade_listings WHERE listingID = ?";
                    $verifyListingStmt = mysqli_prepare($connection, $verifyListingQuery);
                    mysqli_stmt_bind_param($verifyListingStmt, "i", $listingID);
                    mysqli_stmt_execute($verifyListingStmt);
                    $verifyListingResult = mysqli_stmt_get_result($verifyListingStmt);
                    
                    if ($listingRow = mysqli_fetch_assoc($verifyListingResult)) {
                        if ($listingRow['status'] === 'active') {
                            // Complete the trade
                            mysqli_begin_transaction($connection);
                            
                            // Update listing status to inactive
                            $updateListingQuery = "UPDATE tbltrade_listings SET status = 'inactive' WHERE listingID = ?";
                            $updateListingStmt = mysqli_prepare($connection, $updateListingQuery);
                            mysqli_stmt_bind_param($updateListingStmt, "i", $listingID);
                            
                            if (mysqli_stmt_execute($updateListingStmt)) {
                                // Update owner's tradesCompleted count
                                $updateOwnerQuery = "UPDATE tblusers SET tradesCompleted = tradesCompleted + 1 WHERE userID = ?";
                                $updateOwnerStmt = mysqli_prepare($connection, $updateOwnerQuery);
                                mysqli_stmt_bind_param($updateOwnerStmt, "i", $ownerID);
                                mysqli_stmt_execute($updateOwnerStmt);
                                
                                // Update buyer's tradesCompleted count
                                $updateBuyerQuery = "UPDATE tblusers SET tradesCompleted = tradesCompleted + 1 WHERE userID = ?";
                                $updateBuyerStmt = mysqli_prepare($connection, $updateBuyerQuery);
                                mysqli_stmt_bind_param($updateBuyerStmt, "i", $buyerID);
                                mysqli_stmt_execute($updateBuyerStmt);
                                
                                mysqli_commit($connection);
                                
                                mysqli_stmt_close($updateListingStmt);
                                mysqli_stmt_close($updateOwnerStmt);
                                mysqli_stmt_close($updateBuyerStmt);
                                
                                echo json_encode(['success' => true, 'message' => 'Trade completed successfully!']);
                            } else {
                                mysqli_rollback($connection);
                                echo json_encode(['success' => false, 'message' => 'Failed to complete trade']);
                            }
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Listing is no longer available']);
                        }
                    }
                    
                    mysqli_stmt_close($verifyListingStmt);
                } else {
                    echo json_encode(['success' => true, 'message' => 'Request ' . $newStatus]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update request']);
            }
            
            mysqli_stmt_close($updateStmt);
        } else {
            echo json_encode(['success' => false, 'message' => 'Request not found']);
        }
        
        mysqli_stmt_close($getStmt);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

mysqli_close($connection);
?>