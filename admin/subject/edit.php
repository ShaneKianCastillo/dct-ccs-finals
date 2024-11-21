<?php
$titlePage = 'Edit a Subject';
include '../../functions.php';


// Establish a database connection
$conn = new mysqli('localhost', 'root', '', 'dct-ccs-finals');

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Redirect if no subject code is provided
if (!isset($_GET['code'])) {
    header("Location: add.php");
    exit();
}

$subjectCode = $_GET['code'];
$errorArray = [];

// Fetch subject details from the database
$stmt = $conn->prepare("SELECT * FROM subjects WHERE subject_code = ?");
$stmt->bind_param("s", $subjectCode);
$stmt->execute();
$result = $stmt->get_result();
$subjectData = $result->fetch_assoc();

if (!$subjectData) {
    header("Location: add.php");
    exit();
}

// Handle form submission for updating the subject
if (isset($_POST['updateSubject'])) {
    $newCode = $_POST['subCode'];
    $newName = $_POST['subName'];

    // Validate the updated subject data
    $errorArray = validateSubjectData([
        'subject_code' => $newCode,
        'subject_name' => $newName
    ]);

    if (empty($errorArray)) {
        // Update the subject in the database
        $updateStmt = $conn->prepare("UPDATE subjects SET subject_code = ?, subject_name = ? WHERE subject_code = ?");
        $updateStmt->bind_param("sss", $newCode, $newName, $subjectCode);

        if ($updateStmt->execute()) {
            header("Location: add.php");
            exit();
        } else {
            $errorArray[] = "Failed to update subject. Please try again.";
        }
    }
}

// Close the database connection
$conn->close();


include('../partials/header.php');
?>

<div class="d-flex m-0 p-0">
    <?php include '../partials/side-bar.php'; ?>

    <div class="container my-5">
        <?php if (!empty($errorArray)): ?>
            <?= displayErrors($errorArray) ?>
        <?php endif; ?>
        <h2>Edit Subject Details</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../root/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="add.php">Add Subject</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Subject</li>
            </ol>
        </nav>

        <div class="card p-3 mb-4">
            <form method="post">
                <div class="mb-3">
                    <label for="subCode" class="form-label">Subject Code</label>
                    <input type="text" name="subCode" class="form-control" id="subCode" value="<?= htmlspecialchars($subjectData['subject_code']) ?>">
                </div>
                <div class="mb-3">
                    <label for="subName" class="form-label">Subject Name</label>
                    <input type="text" name="subName" class="form-control" id="subName" value="<?= htmlspecialchars($subjectData['subject_name']) ?>">
                </div>
                <button type="submit" class="btn btn-primary" name="updateSubject">Update Subject</button>
            </form>
        </div>
    </div>
</div>

<?php include('../partials/footer.php'); ?>
