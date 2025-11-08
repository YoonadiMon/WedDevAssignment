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
        $verifyQuery = "SELECT userID FROM tbltrade_listings WHERE listingID = '$listingID' AND userID = '$userID'";
        $verifyResult = mysqli_query($connection, $verifyQuery);
        
        if (!$verifyResult) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
            exit;
        }
        
        if (mysqli_num_rows($verifyResult) === 0) {
            echo json_encode(['success' => false, 'message' => 'Listing not found or you do not have permission to delete it']);
            exit;
        }
        
        // Update listing status to inactive  == DELETED Trade listing
        $updateQuery = "UPDATE tbltrade_listings SET status = 'inactive' WHERE listingID = '$listingID' AND userID = '$userID'";
        $updateResult = mysqli_query($connection, $updateQuery);
        
        if ($updateResult) {
            echo json_encode(['success' => true, 'message' => 'Listing deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>