<?php 
session_start();
include 'db.php';

// Ensure only logged-in admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$message = "";

// Add a new position
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_position'])) {
    $position_name = trim($_POST['position_name']);

    // Check if the position already exists
    $check_stmt = $conn->prepare("SELECT id FROM positions WHERE name = ?");
    $check_stmt->bind_param("s", $position_name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $message = "<div class='alert alert-danger'>This position already exists.</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO positions (name) VALUES (?)");
        $stmt->bind_param("s", $position_name);
        
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Position added successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error adding position: " . $stmt->error . "</div>";
        }
    }
}

// Edit an existing position
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_position'])) {
    $position_id = $_POST['position_id'];
    $position_name = trim($_POST['position_name']);

    $stmt = $conn->prepare("UPDATE positions SET name=? WHERE id=?");
    $stmt->bind_param("si", $position_name, $position_id);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Position updated successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error updating position: " . $stmt->error . "</div>";
    }
}

// Delete a position
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_position'])) {
    $position_id = $_POST['position_id'];

    $stmt = $conn->prepare("DELETE FROM positions WHERE id=?");
    $stmt->bind_param("i", $position_id);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Position deleted successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error deleting position: " . $stmt->error . "</div>";
    }
}

// Retrieve existing positions
$positions = [];
$stmt = $conn->prepare("SELECT * FROM positions ORDER BY id");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $positions[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Positions - Online Voting System</title>
    <link href="css/bootstrap/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<header class="bg-primary text-white py-3">
    <div class="container d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0">Manage Positions</h1>
        <nav>
            <a href="admin_home.php" class="btn btn-light me-2">Dashboard</a>
            <a href="manage_positions.php" class="btn btn-light me-2">Manage Positions</a>
            <a href="add_candidate.php" class="btn btn-light me-2">Manage Candidates</a>
            <a href="logout.php" class="btn btn-danger">Log Out</a>
        </nav>
    </div>
</header>

<main class="container my-5">
    <h2 class="mb-4 text-center">Manage Positions</h2>
    <?php echo $message; ?>

    <!-- Add Position Form -->
    <div class="card p-4 mx-auto" style="max-width: 500px;">
        <h4>Add New Position</h4>
        <form method="POST">
            <div class="mb-3">
                <label for="position_name" class="form-label">Position Name</label>
                <input type="text" name="position_name" id="position_name" class="form-control" required>
            </div>
            <button type="submit" name="add_position" class="btn btn-primary w-100">Add Position</button>
        </form>
    </div>

    <!-- Existing Positions -->
    <h3 class="mt-5">Existing Positions</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Position Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($positions as $position): ?>
                <tr>
                    <form method="POST">
                        <td><?php echo $position['id']; ?></td>
                        <td>
                            <input type="text" name="position_name" value="<?php echo htmlspecialchars($position['name']); ?>" required class="form-control">
                        </td>
                        <td>
                            <input type="hidden" name="position_id" value="<?php echo $position['id']; ?>">
                            <button type="submit" name="edit_position" class="btn btn-warning btn-sm">Update</button>
                            <button type="submit" name="delete_position" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this position?');">Delete</button>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

</body>
</html>
