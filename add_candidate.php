<?php
session_start();
include 'db.php';

// Only allow access for logged-in admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$message = "";

// Add candidate
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_candidate'])) {
    $candidate_name = trim($_POST['candidate_name']);
    $position_id = $_POST['position_id'];
    if (!empty($candidate_name) && !empty($position_id)) {
        $stmt = $conn->prepare("INSERT INTO candidates (name, position_id, votes) VALUES (?, ?, 0)");
        $stmt->bind_param("si", $candidate_name, $position_id);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Candidate added successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error adding candidate.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Please fill in all fields.</div>";
    }
}

// Delete candidate
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM candidates WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Candidate deleted successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error deleting candidate.</div>";
    }
}

// Edit candidate
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_candidate'])) {
    $edit_id = $_POST['edit_id'];
    $candidate_name = trim($_POST['candidate_name']);
    $position_id = $_POST['position_id'];
    if (!empty($candidate_name) && !empty($position_id)) {
        $stmt = $conn->prepare("UPDATE candidates SET name = ?, position_id = ? WHERE id = ?");
        $stmt->bind_param("sii", $candidate_name, $position_id, $edit_id);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Candidate updated successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error updating candidate.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Please fill in all fields.</div>";
    }
}

// Retrieve positions for dropdown
$positions_result = $conn->query("SELECT id, name FROM positions ORDER BY name");
$positions_list = [];
while ($row = $positions_result->fetch_assoc()) {
    $positions_list[] = $row;
}

// Retrieve candidates
$query = "SELECT c.id, c.name AS candidate_name, p.name AS position_name, c.position_id 
          FROM candidates c 
          JOIN positions p ON c.position_id = p.id
          ORDER BY p.name, c.name";
$result = $conn->query($query);
$candidates = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Candidates</title>
  <link href="css/bootstrap/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-center">Manage Candidates</h2>
      <a href="admin_home.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <?php echo $message; ?>
    <div class="card p-4 mx-auto" style="max-width: 500px;">
      <form method="POST" action="">
        <input type="hidden" name="edit_id" id="edit_id">
        <div class="mb-3">
          <label for="candidate_name" class="form-label">Candidate Name</label>
          <input type="text" name="candidate_name" id="candidate_name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="position_id" class="form-label">Select Position</label>
          <select name="position_id" id="position_id" class="form-select" required>
            <option value="">-- Select Position --</option>
            <?php foreach ($positions_list as $position): ?>
              <option value="<?php echo $position['id']; ?>"><?php echo htmlspecialchars($position['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" name="add_candidate" id="submitBtn" class="btn btn-primary w-100">Add Candidate</button>
      </form>
    </div>
    
    <h3 class="mt-5">Existing Candidates</h3>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Candidate Name</th>
          <th>Position</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($candidates as $row): ?>
          <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['candidate_name']); ?></td>
            <td><?php echo htmlspecialchars($row['position_name']); ?></td>
            <td>
              <button class="btn btn-warning btn-sm edit-btn" data-id="<?php echo $row['id']; ?>" 
                      data-name="<?php echo htmlspecialchars($row['candidate_name']); ?>" 
                      data-position="<?php echo $row['position_id']; ?>">Edit</button>
              <a href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <script>
    document.querySelectorAll('.edit-btn').forEach(button => {
      button.addEventListener('click', function() {
        document.getElementById('edit_id').value = this.dataset.id;
        document.getElementById('candidate_name').value = this.dataset.name;
        document.getElementById('position_id').value = this.dataset.position;
        document.getElementById('submitBtn').innerText = 'Update Candidate';
        document.getElementById('submitBtn').name = 'edit_candidate';
      });
    });
  </script>
</body>
</html>
