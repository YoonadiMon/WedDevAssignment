<?php
session_start();
include("dbConn.php");

if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$userID = $_SESSION['userID'];

// Get unread message count
$unread_query = "SELECT COUNT(*) as unread_count 
                FROM tblmessages 
                WHERE receiverID = '$userID' AND isRead = FALSE";
$unread_result = mysqli_query($connection, $unread_query);

if ($unread_result) {
    $unread_data = mysqli_fetch_assoc($unread_result);
    echo json_encode([
        'success' => true, 
        'unread_count' => $unread_data['unread_count']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

mysqli_close($connection);
?>