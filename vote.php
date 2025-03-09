<?php
session_start();
include 'db.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'voter') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['vote'])) {
    $voter_id = $_SESSION['user_id'];
    $votes = $_POST['vote'];
    $voteSuccess = [];
    $voteErrors = [];
    
    
    foreach ($votes as $position_id => $candidate_id) {
        
        $stmt = $conn->prepare("SELECT id FROM votes WHERE voter_id = ? AND position_id = ?");
        $stmt->bind_param("ii", $voter_id, $position_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $voteErrors[] = "You have already voted for position " . $position_id . ".";
        } else {
            
            $stmt2 = $conn->prepare("INSERT INTO votes (voter_id, candidate_id, position_id) VALUES (?, ?, ?)");
            $stmt2->bind_param("iii", $voter_id, $candidate_id, $position_id);
            if ($stmt2->execute()) {
               
                $stmt3 = $conn->prepare("UPDATE candidates SET votes = votes + 1 WHERE id = ?");
                $stmt3->bind_param("i", $candidate_id);
                $stmt3->execute();
                $voteSuccess[] = "Vote recorded for position " . $position_id . ".";
            } else {
                $voteErrors[] = "Error recording vote for position " . $position_id . ".";
            }
        }
    }
    
    
    if (!empty($voteSuccess)) {
        $_SESSION['vote_success'] = implode("<br>", $voteSuccess);
    }
    if (!empty($voteErrors)) {
        $_SESSION['vote_error'] = implode("<br>", $voteErrors);
    }
    
    header("Location: voter_home.php");
    exit;
}
?>
