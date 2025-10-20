<?php
session_start();
include("dbConn.php");

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Admin not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listingID = mysqli_real_escape_string($connection, $_POST['listingID']);
    
    try {
        // Update reported status to 0
        $updateQuery = "UPDATE tbltrade_listings SET reported = 0 WHERE listingID = ?";
        $updateStmt = mysqli_prepare($connection, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, "i", $listingID);
        
        if (mysqli_stmt_execute($updateStmt)) {
            echo json_encode(['success' => true, 'message' => 'Report resolved successfully']);
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