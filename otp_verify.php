<?php
session_start();
include 'db.php';

if (!isset($_SESSION['temp_user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $otp_entered = trim($_POST['otp']);
    $user_id = $_SESSION['temp_user_id'];
    
    $stmt = $conn->prepare("SELECT * FROM otp_verifications WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $record = $result->fetch_assoc();
        if ($record['otp_code'] == $otp_entered && strtotime($record['expires_at']) >= time()) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = 'voter';
            unset($_SESSION['temp_user_id']);
            header("Location: voter_home.php");
            exit;
        } else {
            $error = "Invalid or expired OTP!";
        }
    } else {
        $error = "OTP record not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>OTP Verification - Online Voting System</title>
   <link href="css/bootstrap/bootstrap.min.css" rel="stylesheet">
   <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
   <div class="container my-5">
      <?php if(isset($error)) { ?>
         <div class="alert alert-danger text-center"><?php echo $error; ?></div>
      <?php } ?>
      <div class="card shadow p-4 mx-auto" style="max-width: 400px;">
         <h2 class="text-center mb-4">OTP Verification</h2>
         <form method="POST" action="otp_verify.php">
            <div class="mb-3">
               <label for="otp" class="form-label">Enter OTP:</label>
               <input type="text" name="otp" id="otp" class="form-control" placeholder="6-digit OTP" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Verify OTP</button>
         </form>
      </div>
   </div>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
