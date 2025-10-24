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
    $userID = $_SESSION['userID'];
    
    try {
        // Verify that the listing belongs to the current user
        $verifyQuery = "SELECT userID FROM tbltrade_listings WHERE listingID = ? AND userID = ?";
        $verifyStmt = mysqli_prepare($connection, $verifyQuery);
        mysqli_stmt_bind_param($verifyStmt, "ii", $listingID, $userID);
        mysqli_stmt_execute($verifyStmt);
        $verifyResult = mysqli_stmt_get_result($verifyStmt);
        
        if (mysqli_num_rows($verifyResult) === 0) {
            echo json_encode(['success' => false, 'message' => 'Listing not found or you do not have permission to delete it']);
            exit;
        }
        
        // Update listing status to inactive  == DELETED Trade listing
        $updateQuery = "UPDATE tbltrade_listings SET status = 'inactive' WHERE listingID = ? AND userID = ?";
        $updateStmt = mysqli_prepare($connection, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, "ii", $listingID, $userID);
        
        if (mysqli_stmt_execute($updateStmt)) {
            echo json_encode(['success' => true, 'message' => 'Listing deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
        }
        
        mysqli_stmt_close($updateStmt);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>