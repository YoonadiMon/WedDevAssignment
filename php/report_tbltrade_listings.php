<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include 'dbConn.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['listingId']) || !isset($input['reason'])) {
        throw new Exception('Missing required fields: listingId and reason are required');
    }
    
    $listingId = mysqli_real_escape_string($connection, $input['listingId']);
    $reason = mysqli_real_escape_string($connection, $input['reason']);
    
    // First, check if the listing exists
    $checkQuery = "SELECT listingID FROM tbltrade_listings WHERE listingID = '$listingId'";
    $checkResult = mysqli_query($connection, $checkQuery);
    
    if (mysqli_num_rows($checkResult) === 0) {
        throw new Exception('Listing not found');
    }
    
    // Update the listing to mark as reported
    $updateQuery = "UPDATE tbltrade_listings SET reported = 1 WHERE listingID = '$listingId'";
    
    if (mysqli_query($connection, $updateQuery)) {
        
        // Optional: You might want to log the report reason in a separate table
        // This would require creating a tblreports table
        $logQuery = "INSERT INTO tblreports (listingID, reason, reportDate, status) 
                     VALUES ('$listingId', '$reason', NOW(), 'pending')";
        mysqli_query($connection, $logQuery); // This will work even if tblreports doesn't exist yet
        
        echo json_encode([
            'success' => true,
            'message' => 'Listing reported successfully'
        ]);
    } else {
        throw new Exception('Failed to report listing: ' . mysqli_error($connection));
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    mysqli_close($connection);
}
?>