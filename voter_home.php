<?php
session_start();
include 'db.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'voter') {
    header("Location: login.php");
    exit;
}


$query = "SELECT p.id AS position_id, p.name AS position_name, 
                 c.id AS candidate_id, c.name AS candidate_name 
          FROM positions p 
          JOIN candidates c ON p.id = c.position_id 
          ORDER BY p.id, c.id";
$result = $conn->query($query);

$positions = [];
while ($row = $result->fetch_assoc()) {
    $pos_id = $row['position_id'];
    if (!isset($positions[$pos_id])) {
        $positions[$pos_id] = [
            'position_name' => $row['position_name'],
            'candidates'    => []
        ];
    }
    $positions[$pos_id]['candidates'][] = [
        'candidate_id'   => $row['candidate_id'],
        'candidate_name' => $row['candidate_name']
    ];
}

$imageMapping = array(
    
    1 => 'ce21004.jpg',
    2 => 'ce21005.jpg',
    7=>'ce21004.jpg',
    8=>'ce21004.jpg',
    9=>'ce21004.jpg',
    10=>'ce21004.jpg',
    12=>'ce21004.jpg',
     11 => 'ce21004.jpg'
    
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>Voter Dashboard - Online Voting System</title>
   <link href="css/bootstrap/bootstrap.min.css" rel="stylesheet">
   <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
   <!-- HEADER with navigation buttons -->
   <header class="bg-primary text-white py-3">
      <div class="container d-flex justify-content-between align-items-center">
         <div class="d-flex align-items-center">
            <img src="img/logo.png" alt="MBSTU CSE Alumni Association Logo" class="me-3" style="height: 60px;">
            <div>
               <h1 class="h5 mb-0">MBSTU CSE Alumni Association</h1>
               <p class="mb-0">Election 2025-2026 | Online Voting System</p>
            </div>
         </div>
         <div>
            <a href="results.php" class="btn btn-light me-2">Show Results</a>
            <a href="logout.php" class="btn btn-danger">Log Out</a>
         </div>
      </div>
   </header>
   
   
   <main class="container my-5">
       <h2 class="mb-4 text-center">Cast Your Vote</h2>
       
       
       <?php
       if (isset($_SESSION['vote_success'])) {
           echo "<div class='alert alert-success text-center'>" . $_SESSION['vote_success'] . "</div>";
           unset($_SESSION['vote_success']);
       }
       if (isset($_SESSION['vote_error'])) {
           echo "<div class='alert alert-danger text-center'>" . $_SESSION['vote_error'] . "</div>";
           unset($_SESSION['vote_error']);
       }
       ?>
       
      
       <form method="POST" action="vote.php">
         <?php foreach ($positions as $position_id => $details): ?>
           <div class="card mb-4">
              <div class="card-header">
                 <h3 class="h5"><?php echo htmlspecialchars($details['position_name']); ?></h3>
              </div>
              <div class="card-body">
                 <?php foreach ($details['candidates'] as $candidate): ?>
                   <?php 
                   
                   $imageFile = isset($imageMapping[$candidate['candidate_id']]) ? $imageMapping[$candidate['candidate_id']] : $candidate['candidate_id'] . '.jpg';
                   ?>
                   <div class="form-check d-flex align-items-center mb-2">
                     
                      <img src="img/<?php echo htmlspecialchars($imageFile); ?>" alt="Candidate Icon" class="me-2 rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
                     
                      <input type="radio" 
                             name="vote[<?php echo $position_id; ?>]" 
                             id="candidate_<?php echo $candidate['candidate_id']; ?>" 
                             value="<?php echo $candidate['candidate_id']; ?>" 
                             class="form-check-input me-2" 
                             required>
                     
                      <label class="form-check-label" for="candidate_<?php echo $candidate['candidate_id']; ?>">
                         <?php echo htmlspecialchars($candidate['candidate_name']); ?>
                      </label>
                   </div>
                 <?php endforeach; ?>
              </div>
           </div>
         <?php endforeach; ?>
         

         <button type="submit" class="btn btn-success w-100 mt-3">Submit Vote</button>
       </form>
   </main>
   
   <!-- FOOTER -->
   <footer class="bg-dark text-white text-center py-3">
      <div class="container">
         &copy; <?php echo date("Y"); ?> Online Voting System
      </div>
   </footer>
   
   <!-- Bootstrap JS -->
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
