<?php
session_start();
include("dbConn.php");
include("sessionCheck.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$userID = $_POST['userID'];
$stageID = $_POST['stageID'];
$score = $_POST['score'];
$totalPoints = $_POST['totalPoints'];
$percentage = $_POST['percentage'];

try {
    // Check if user already has progress for this stage
    $checkQuery = "SELECT progressID FROM tbluser_quiz_progress WHERE userID = ? AND stageID = ?";
    $stmt = $connection->prepare($checkQuery);
    $stmt->bind_param("ii", $userID, $stageID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $isNewCompletion = ($result->num_rows === 0);
    
    if ($result->num_rows > 0) {
        // Update existing progress
        $updateQuery = "UPDATE tbluser_quiz_progress SET score = ?, completed = TRUE, completedAt = NOW() WHERE userID = ? AND stageID = ?";
        $stmt = $connection->prepare($updateQuery);
        $stmt->bind_param("iii", $score, $userID, $stageID);
    } else {
        // Insert new progress
        $insertQuery = "INSERT INTO tbluser_quiz_progress (userID, stageID, score, completed, completedAt) VALUES (?, ?, ?, TRUE, NOW())";
        $stmt = $connection->prepare($insertQuery);
        $stmt->bind_param("iii", $userID, $stageID, $score);
    }
    
    if ($stmt->execute()) {
        // Always add points when completing a stage
        $updatePointsQuery = "UPDATE tblusers SET point = point + ? WHERE userID = ?";
        $stmt = $connection->prepare($updatePointsQuery);
        $stmt->bind_param("ii", $totalPoints, $userID);
        
        if ($stmt->execute()) {
            // Get the updated points total
            $getUserQuery = "SELECT point FROM tblusers WHERE userID = ?";
            $stmt = $connection->prepare($getUserQuery);
            $stmt->bind_param("i", $userID);
            $stmt->execute();
            $userResult = $stmt->get_result();
            $userData = $userResult->fetch_assoc();
            $newTotalPoints = $userData['point'];
            
            echo json_encode([
                'success' => true, 
                'message' => $isNewCompletion ? 'New stage completed!' : 'Stage completed again!',
                'points_added' => $totalPoints,
                'new_total_points' => $newTotalPoints,
                'score' => $score,
                'stage_completed' => true
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update user points']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save progress']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$connection->close();
?>