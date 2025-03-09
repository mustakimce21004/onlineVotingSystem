<?php
session_start();
include 'db.php';

// Allow access for logged-in users (both voters and admins)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Determine dashboard link based on user role
$dashboard_link = ($_SESSION['role'] == 'admin') ? "admin_home.php" : "voter_home.php";

// Retrieve election results: join candidates with positions, ordered by position and vote count
$query = "SELECT p.id AS position_id, p.name AS position_name, c.name AS candidate_name, c.votes
          FROM positions p
          JOIN candidates c ON p.id = c.position_id
          ORDER BY p.id, c.votes DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>Election Results - Online Voting System</title>
   <link href="css/bootstrap/bootstrap.min.css" rel="stylesheet">
   <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
   <!-- Header with Navigation Buttons -->
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
            <h1 class="h3 mb-0">Election Results</h1>
        </div>
        <div>
            <a href="<?php echo $dashboard_link; ?>" class="btn btn-light me-2">Dashboard</a>
            <a href="logout.php" class="btn btn-danger">Log Out</a>
        </div>
    </div>
</header>

   
   <!-- Main Content: Display Results -->
   <main class="container my-5">
      <h2 class="mb-4 text-center">Results</h2>
      <?php 
      $current_position = null;
      if ($result->num_rows > 0) {
         while ($row = $result->fetch_assoc()) {
             if ($current_position !== $row['position_name']) {
                 if ($current_position !== null) {
                     // Close the previous table
                     echo "</tbody></table>";
                 }
                 $current_position = $row['position_name'];
                 echo "<h3>" . htmlspecialchars($current_position) . "</h3>";
                 echo "<table class='table table-striped'><thead><tr><th>Candidate Name</th><th>Votes</th></tr></thead><tbody>";
             }
             echo "<tr>";
             echo "<td>" . htmlspecialchars($row['candidate_name']) . "</td>";
             echo "<td>" . $row['votes'] . "</td>";
             echo "</tr>";
         }
         // Close the last table
         echo "</tbody></table>";
      } else {
         echo "<p class='text-center'>No results available yet.</p>";
      }
      ?>
   </main>
   
   <!-- Footer -->
   <footer class="bg-dark text-white text-center py-3">
      <div class="container">
         &copy; <?php echo date("Y"); ?> Online Voting System
      </div>
   </footer>
   
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
