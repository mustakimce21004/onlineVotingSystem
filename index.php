<?php
session_start();
// Redirect logged-in users to their respective dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin_home.php");
        exit;
    } elseif ($_SESSION['role'] == 'voter') {
        header("Location: voter_home.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="MBSTU CSE Alumni Association Online Voting System">
  <meta name="author" content="Mustakim">
  <link rel="shortcut icon" href="img/favicon.ico">

  <title>MBSTU CSE Alumni Voting 2025-2026</title>
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  

  <link href="css/bootstrap/bootstrap.min.css" rel="stylesheet">


  <link href="css/style.css" rel="stylesheet">
</head>
<body>
 
  <header class="bg-primary text-white py-4">
    <div class="container d-flex align-items-center">
      <img src="img/logo.png" alt="MBSTU CSE Alumni Association Logo" class="me-3" style="height: 80px;">
      <div>
        <h1 class="display-6 mb-0">MBSTU CSE Alumni Association</h1>
        <p class="mb-0">Election 2025-2026 | Online Voting System</p>
      </div>
    </div>
  </header>

 
  <main class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
      <h2 class="text-center mb-4">LOGIN</h2>
      <form action="login.php" method="POST">
        <div class="mb-3">
          <label for="role" class="form-label">Login as:</label>
          <select id="role" name="role" class="form-select">
            <option value="voter">Alumni</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="identifier" class="form-label">Identifier</label>
          <input type="text" id="identifier" name="identifier" class="form-control" placeholder="Alumni ID or Admin Name" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>
    </div>
  </main>

  <!-- FOOTER -->
  <footer class="bg-dark text-white py-3">
    <div class="container text-center">
      &copy; 2025 MBSTU CSE Alumni Association | Election 2025-2026
    </div>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
