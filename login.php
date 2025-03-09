<?php
session_start();
include 'db.php';

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include PHPMailer classes
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = trim($_POST['identifier']);
    $password   = trim($_POST['password']);
    $role       = $_POST['role']; // 'voter' or 'admin'
    
    if ($role == 'voter') {
        // Lookup by alumni_id for voters
        $stmt = $conn->prepare("SELECT * FROM users WHERE alumni_id = ? AND role = 'voter' LIMIT 1");
        $stmt->bind_param("s", $identifier);
    } else {
        // Lookup by name for admin
        $stmt = $conn->prepare("SELECT * FROM users WHERE name = ? AND role = 'admin' LIMIT 1");
        $stmt->bind_param("s", $identifier);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Using plain text comparison for testing (no hashing)
        if ($password === $user['password']) {
            if ($role == 'voter') {
                // Generate a 6-digit OTP valid for 5 minutes
                $otp = rand(100000, 999999);
                $expires_at = date("Y-m-d H:i:s", strtotime("+5 minutes"));
                
                // Save the OTP record in the otp_verifications table
                $stmt2 = $conn->prepare("INSERT INTO otp_verifications (user_id, otp_code, expires_at) VALUES (?, ?, ?)");
                $stmt2->bind_param("iss", $user['id'], $otp, $expires_at);
                $stmt2->execute();
                
                // Set up PHPMailer to send the OTP email
                $mail = new PHPMailer\PHPMailer\PHPMailer();
                try {
                    $mail->isSMTP();                                      // Use SMTP
                    $mail->Host       = 'smtp.gmail.com';                 // SMTP server (for Gmail)
                    $mail->SMTPAuth   = true;                             // Enable SMTP authentication
                    $mail->Username   = 'mustakimbillah30679@gmail.com';           // Replace with your email address
                    $mail->Password   = 'usumfkqpipffnwja'; // Replace with your email password or app password
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; // TLS encryption
                    $mail->Port       = 587;                              // TCP port for TLS
                    
                    // Recipients
                    $mail->setFrom('mustakimbillah30679@gmail.com', 'Online Voting System');
                    $mail->addAddress($user['email']);                   // Send OTP to the voter's email
                    
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Your OTP Code';
                    $mail->Body    = 'Your OTP is: <b>' . $otp . '</b>. It is valid for 5 minutes.';
                    $mail->AltBody = 'Your OTP is: ' . $otp . '. It is valid for 5 minutes.';
                    
                    $mail->send();
                } catch (Exception $e) {
                    echo "OTP could not be sent. Mailer Error: " . $mail->ErrorInfo;
                    exit;
                }
                
                // Save temporary user ID for OTP verification
                $_SESSION['temp_user_id'] = $user['id'];
                header("Location: otp_verify.php");
                exit;
            } else {
                // Admin login: set session and redirect to admin dashboard
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                header("Location: admin_home.php");
                exit;
            }
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="shortcut icon" href="img/favicon.ico">
  <title>OnlineVoting</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link href="css/bootstrap/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
  <!-- HEADER -->
  <header class="bg-primary text-white py-4">
    <div class="container d-flex align-items-center">
      <img src="img/logo.png" alt="MBSTU CSE Alumni Association Logo" class="me-3" style="height: 80px;">
      <div>
        <h1 class="display-6 mb-0">MBSTU CSE Alumni Association</h1>
        <p class="mb-0">Election 2025-2026 | Online Voting System</p>
      </div>
    </div>
  </header>
  
  <!-- MAIN: LOGIN FORM -->
  <main class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
      <h2 class="text-center mb-4">LOGIN</h2>
      <?php if(isset($error)) { ?>
        <div class="alert alert-danger text-center"><?php echo $error; ?></div>
      <?php } ?>
      <form action="login.php" method="POST">
        <!-- Role Selection -->
        <div class="mb-3">
          <label for="role" class="form-label">Login as:</label>
          <select id="role" name="role" class="form-select">
            <option value="voter">Alumni/Voter</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <!-- Identifier -->
        <div class="mb-3">
          <label for="identifier" class="form-label">Identifier</label>
          <input type="text" id="identifier" name="identifier" class="form-control" placeholder="Alumni ID or Admin Name" required>
        </div>
        <!-- Password -->
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <!-- Submit -->
        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>
    </div>
  </main>
  
  <!-- FOOTER -->
  <footer class="bg-dark text-white py-3">
    <div class="container-fluid">
      <div class="row m-0">
        <div class="col text-center">
          &copy; 2025 Online Voting System
        </div>
      </div>
    </div>
  </footer>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
