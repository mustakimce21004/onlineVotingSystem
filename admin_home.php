<?php
session_start();
include 'db.php';

// Ensure only logged-in admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$message = "";

// Add voter
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_voter'])) {
    $alumni_id = trim($_POST['alumni_id']);
    $name      = trim($_POST['name']);
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phone']);
    $password  = trim($_POST['password']);
    $status    = trim($_POST['status']);

    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $message = "<div class='alert alert-danger'>A voter with this email already exists.</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (alumni_id, name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, 'voter', ?)");
        $stmt->bind_param("ssssss", $alumni_id, $name, $email, $phone, $password, $status);
    
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Voter added successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error adding voter: " . $stmt->error . "</div>";
        }
    }
}

// Edit voter
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_voter'])) {
    $voter_id  = $_POST['voter_id'];
    $alumni_id = trim($_POST['alumni_id']);
    $name      = trim($_POST['name']);
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phone']);
    $status    = trim($_POST['status']);

    $stmt = $conn->prepare("UPDATE users SET alumni_id=?, name=?, email=?, phone=?, status=? WHERE id=?");
    $stmt->bind_param("sssssi", $alumni_id, $name, $email, $phone, $status, $voter_id);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Voter updated successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error updating voter: " . $stmt->error . "</div>";
    }
}

// Delete voter
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_voter'])) {
    $voter_id = $_POST['voter_id'];

    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $voter_id);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Voter deleted successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error deleting voter: " . $stmt->error . "</div>";
    }
}

// Retrieve voters
$voters = [];
$stmt = $conn->prepare("SELECT id, alumni_id, name, email, phone, status FROM users WHERE role = 'voter' ORDER BY id");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $voters[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Online Voting System</title>
    <link href="css/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

<header class="bg-primary text-white py-3">
    <div class="container d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0">Admin Dashboard</h1>
        <nav>
            <a href="admin_home.php" class="btn btn-light me-2">Dashboard</a>
            <a href="results.php" class="btn btn-light me-2">View Results</a>
            <a href="add_candidate.php" class="btn btn-light me-2">Manage Candidates</a>
            <a href="#voters" class="btn btn-light me-2">Manage Voters</a>
            <a href="manage_positions.php" class="btn btn-light me-2">Manage Positions</a> <!-- New Button -->
            <a href="logout.php" class="btn btn-danger">Log Out</a>
        </nav>
    </div>
</header>

<main class="container my-5">
    <div class="alert alert-info text-center">Welcome, Admin! Use the options above to manage the election.</div>

    <section id="voters" class="mb-5">
        <h2 class="h4 mb-3">Manage Voters</h2>
        <?php echo $message; ?>

        <form method="POST" class="mb-4">
            <input type="hidden" name="add_voter" value="1">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="alumni_id" class="form-label">Alumni ID</label>
                    <input type="text" class="form-control" name="alumni_id" required>
                </div>
                <div class="col-md-4">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="col-md-4">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="col-md-4">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" class="form-control" name="phone" required>
                </div>
                <div class="col-md-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="text" class="form-control" name="password" required>
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Add Voter</button>
        </form>

        <h3 class="h5">Existing Voters</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Alumni ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($voters as $voter): ?>
                    <tr>
                        <form method="POST">
                            <td><input type="text" name="alumni_id" value="<?= $voter['alumni_id']; ?>" required></td>
                            <td><input type="text" name="name" value="<?= $voter['name']; ?>" required></td>
                            <td><input type="email" name="email" value="<?= $voter['email']; ?>" required></td>
                            <td><input type="text" name="phone" value="<?= $voter['phone']; ?>" required></td>
                            <td>
                                <select name="status">
                                    <option value="active" <?= ($voter['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?= ($voter['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </td>
                            <td>
                                <input type="hidden" name="voter_id" value="<?= $voter['id']; ?>">
                                <button type="submit" name="edit_voter" class="btn btn-warning btn-sm">Save</button>
                                <button type="submit" name="delete_voter" class="btn btn-danger btn-sm" onclick="return confirm('Delete voter?');">Delete</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
