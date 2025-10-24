<?php
session_start();
include("dbConn.php");
include("sessionCheck.php");

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listingID = mysqli_real_escape_string($connection, $_POST['listingID']);
    $ownerID = mysqli_real_escape_string($connection, $_POST['ownerID']);
    $buyerID = mysqli_real_escape_string($connection, $_POST['buyerID']);
    $currentUserID = $_SESSION['userID'];
    
    try {
        // Verify that the current user is either the owner or buyer and has permission
        if ($currentUserID != $ownerID && $currentUserID != $buyerID) {
            echo json_encode(['success' => false, 'message' => 'You do not have permission to complete this trade']);
            exit;
        }
        
        // Verify that the listing exists and is active
        $verifyQuery = "SELECT listingID FROM tbltrade_listings WHERE listingID = ? AND status = 'active'";
        $verifyStmt = mysqli_prepare($connection, $verifyQuery);
        mysqli_stmt_bind_param($verifyStmt, "i", $listingID);
        mysqli_stmt_execute($verifyStmt);
        $verifyResult = mysqli_stmt_get_result($verifyStmt);
        
        if (mysqli_num_rows($verifyResult) === 0) {
            echo json_encode(['success' => false, 'message' => 'Listing not found or already completed']);
            exit;
        }
        
        // Start transaction to ensure all operations succeed or fail together
        mysqli_begin_transaction($connection);
        
        // Update listing status to inactive == COMPLETED Trade listing
        $updateQuery = "UPDATE tbltrade_listings SET status = 'inactive' WHERE listingID = ?";
        $updateStmt = mysqli_prepare($connection, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, "i", $listingID);
        
        $listingUpdated = mysqli_stmt_execute($updateStmt);
        
        if ($listingUpdated) {
            // Update owner's tradeCompleted count - increment by 1
            $updateOwnerQuery = "UPDATE tblusers SET tradeCompleted = tradeCompleted + 1 WHERE userID = ?";
            $updateOwnerStmt = mysqli_prepare($connection, $updateOwnerQuery);
            mysqli_stmt_bind_param($updateOwnerStmt, "i", $ownerID);
            $ownerUpdated = mysqli_stmt_execute($updateOwnerStmt);
            
            // Update buyer's tradeCompleted count - increment by 1
            $updateBuyerQuery = "UPDATE tblusers SET tradeCompleted = tradeCompleted + 1 WHERE userID = ?";
            $updateBuyerStmt = mysqli_prepare($connection, $updateBuyerQuery);
            mysqli_stmt_bind_param($updateBuyerStmt, "i", $buyerID);
            $buyerUpdated = mysqli_stmt_execute($updateBuyerStmt);
            
            if ($ownerUpdated && $buyerUpdated) {
                // Commit all transactions
                mysqli_commit($connection);
                echo json_encode(['success' => true, 'message' => 'Trade completed successfully! Both users trade counts updated.']);
            } else {
                // Rollback if any user update fails
                mysqli_rollback($connection);
                echo json_encode(['success' => false, 'message' => 'Failed to update user trade counts']);
            }
            
            mysqli_stmt_close($updateOwnerStmt);
            mysqli_stmt_close($updateBuyerStmt);
        } else {
            // Rollback if listing update fails
            mysqli_rollback($connection);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
        }
        
        mysqli_stmt_close($updateStmt);
        mysqli_stmt_close($verifyStmt);
        
    } catch (Exception $e) {
        // Rollback on any exception
        if (isset($connection)) {
            mysqli_rollback($connection);
        }
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>