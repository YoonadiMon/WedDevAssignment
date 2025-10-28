<?php
// pass $ownerID, $buyerID, $listingID, $currentUserID from tradeRequest.php
function completeTrade($ownerID, $buyerID, $listingID, $currentUserID, $connection) {
    // Verify permissions
    if ($currentUserID != $ownerID && $currentUserID != $buyerID) {
        return ['success' => false, 'message' => 'You do not have permission to complete this trade'];
    }

    // Verify listing is active
    $verifyQuery = "SELECT listingID FROM tbltrade_listings WHERE listingID = '$listingID' AND status = 'active'";
    $verifyResult = mysqli_query($connection, $verifyQuery);

    if (mysqli_num_rows($verifyResult) === 0) {
        return ['success' => false, 'message' => 'Listing not found or already completed'];
    }

    // Start transaction for data consistency
    mysqli_begin_transaction($connection);

    try {
        // Update listing status
        $updateListing = "UPDATE tbltrade_listings SET status = 'inactive' WHERE listingID = '$listingID'";
        if (!mysqli_query($connection, $updateListing)) {
            throw new Exception('Failed to update listing status');
        }
        
        // Update owner's trade count
        $updateOwner = "UPDATE tblusers SET tradesCompleted = tradesCompleted + 1 WHERE userID = '$ownerID'";
        if (!mysqli_query($connection, $updateOwner)) {
            throw new Exception('Failed to update owner trade count');
        }
        
        // Update buyer's trade count
        $updateBuyer = "UPDATE tblusers SET tradesCompleted = tradesCompleted + 1 WHERE userID = '$buyerID'";
        if (!mysqli_query($connection, $updateBuyer)) {
            throw new Exception('Failed to update buyer trade count');
        }
        
        // Commit transaction if all queries succeeded
        mysqli_commit($connection);
        return ['success' => true, 'message' => 'Trade completed successfully!'];
        
    } catch (Exception $e) {
        // Undo if any error occur
        mysqli_rollback($connection);
        return ['success' => false, 'message' => 'Failed to complete trade: ' . $e->getMessage()];
    }
}
?>
