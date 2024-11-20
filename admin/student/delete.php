<?php
include '../../functions.php';
session_start(); // Ensure session is started

// Database connection
$conn = connectDatabase();

// Check if student ID is provided
if (!isset($_GET['id'])) {
    header("Location: register.php");
    exit();
}

$studentId = intval($_GET['id']); // Use intval to ensure valid input

// Fetch student data from the database for confirmation
$stmt = $conn->prepare("SELECT student_id, first_name, last_name FROM students WHERE id = ?");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: register.php");
    exit();
}

$studentData = $result->fetch_assoc();

// Handle the deletion
if (isset($_POST['confirmDelete'])) {
    $deleteStmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $deleteStmt->bind_param("i", $studentId);

    if ($deleteStmt->execute()) {
        header("Location: register.php?msg=deleted");
        exit();
    } else {
        $error = "Error deleting student: " . $conn->error;
    }

    $deleteStmt->close();
}

$stmt->close();
$conn->close();
?>

<?php
$pageTitle = "Delete Student";
include('../partials/header.php');
?>

<div class="d-flex m-0 p-0">
    <!-- Include the sidebar -->
    <?php include '../partials/side-bar.php'; ?>

    <!-- Main content area -->
    <div class="container my-5">
        <h2>Delete Student</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="register.php">Register Student</a></li>
                <li class="breadcrumb-item active" aria-current="page">Delete Student</li>
            </ol>
        </nav>

        <div class="card p-3 mb-4">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <p>Are you sure you want to delete the following student record?</p>
            <ul>
                <li><strong>Student ID:</strong> <?= htmlspecialchars($studentData['student_id']) ?></li>
                <li><strong>First Name:</strong> <?= htmlspecialchars($studentData['first_name']) ?></li>
                <li><strong>Last Name:</strong> <?= htmlspecialchars($studentData['last_name']) ?></li>
            </ul>

            <form method="POST">
                <button type="submit" name="confirmDelete" class="btn btn-danger">Delete Student Record</button>
                <a href="register.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php include('../partials/footer.php'); ?>
