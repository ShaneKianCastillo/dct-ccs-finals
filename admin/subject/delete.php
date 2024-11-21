<?php
$titlePage = 'Delete a Subject';
include '../../functions.php';
session_start(); // Ensure session is started


// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "dct-ccs-finals";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if subject code is provided
if (!isset($_GET['code'])) {
    header("Location: add.php");
    exit();
}

$subjectCode = $_GET['code'];

// Fetch subject data from the database for confirmation
$stmt = $conn->prepare("SELECT subject_code, subject_name FROM subjects WHERE subject_code = ?");
$stmt->bind_param("s", $subjectCode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: add.php");
    exit();
}

$subjectData = $result->fetch_assoc();

// Handle the deletion
if (isset($_POST['confirmDelete'])) {
    $deleteStmt = $conn->prepare("DELETE FROM subjects WHERE subject_code = ?");
    $deleteStmt->bind_param("s", $subjectCode);

    if ($deleteStmt->execute()) {
        header("Location: add.php?msg=deleted");
        exit();
    } else {
        $error = "Error deleting subject: " . $conn->error;
    }

    $deleteStmt->close();
}

$stmt->close();
$conn->close();
?>

<?php
include('../partials/header.php');
?>

<div class="d-flex m-0 p-0">
    <!-- Include the sidebar -->
    <?php include '../partials/side-bar.php'; ?>

    <!-- Main content area -->
    <div class="container my-5">
        <h2>Delete Subject</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../root/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="add.php">Add Subject</a></li>
                <li class="breadcrumb-item active" aria-current="page">Delete Subject</li>
            </ol>
        </nav>

        <div class="card p-3 mb-4">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <p>Are you sure you want to delete the following subject record?</p>
            <ul>
                <li><strong>Subject Code:</strong> <?= htmlspecialchars($subjectData['subject_code']) ?></li>
                <li><strong>Subject Name:</strong> <?= htmlspecialchars($subjectData['subject_name']) ?></li>
            </ul>

            <form method="POST">
                <button type="submit" name="confirmDelete" class="btn btn-danger">Delete Subject Record</button>
                <a href="add.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php include('../partials/footer.php'); ?>