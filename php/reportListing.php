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
    $reason = mysqli_real_escape_string($connection, $_POST['reason']);
    $userID = $_SESSION['userID'];
    
    try {
        // Update reported status to 1
        $updateQuery = "UPDATE tbltrade_listings SET reported = 1 WHERE listingID = ?";
        $updateStmt = mysqli_prepare($connection, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, "i", $listingID);
        
        if (mysqli_stmt_execute($updateStmt)) {
            // may also log the report reason in a separate table
            // For now, no table yet
            echo json_encode(['success' => true, 'message' => 'Report submitted successfully']);
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