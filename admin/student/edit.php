<?php
$titlePage = 'Edit Student';
include '../../functions.php';

// Establish a database connection
$conn = connectDatabase();

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Redirect if no student ID is provided
if (!isset($_GET['id'])) {
    header("Location: register.php");
    exit();
}

$studentId = $_GET['id'];
$errorArray = [];

// Fetch student details from the database
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();
$studentData = $result->fetch_assoc();

if (!$studentData) {
    header("Location: register.php");
    exit();
}

// Handle form submission for updating the student
if (isset($_POST['updateStudent'])) {
    $newStudentId = trim($_POST['studentId']);
    $newFirstName = trim($_POST['firstName']);
    $newLastName = trim($_POST['lastName']);

    // Validate the updated student data
    if (empty($newStudentId) || empty($newFirstName) || empty($newLastName)) {
        $errorArray[] = "All fields are required!";
    } else {
        // Check for duplicate Student ID (other than the current student)
        $checkStmt = $conn->prepare("SELECT * FROM students WHERE student_id = ? AND id != ?");
        $checkStmt->bind_param("si", $newStudentId, $studentId);
        $checkStmt->execute();
        $duplicateCheck = $checkStmt->get_result();

        if ($duplicateCheck->num_rows > 0) {
            $errorArray[] = "A student with this ID already exists!";
        } else {
            // Update the student in the database
            $updateStmt = $conn->prepare("UPDATE students SET student_id = ?, first_name = ?, last_name = ? WHERE id = ?");
            $updateStmt->bind_param("sssi", $newStudentId, $newFirstName, $newLastName, $studentId);

            if ($updateStmt->execute()) {
                header("Location: register.php");
                exit();
            } else {
                $errorArray[] = "Failed to update student. Please try again.";
            }
        }

        $checkStmt->close();
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
        <h2>Edit Student Details</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="register.php">Register Student</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Student</li>
            </ol>
        </nav>

        <div class="card p-3 mb-4">
            <form method="post">
                <div class="mb-3">
                    <label for="studentId" class="form-label">Student ID</label>
                    <input type="text" name="studentId" class="form-control" id="studentId" value="<?= htmlspecialchars($studentData['student_id']) ?>">
                </div>
                <div class="mb-3">
                    <label for="firstName" class="form-label">First Name</label>
                    <input type="text" name="firstName" class="form-control" id="firstName" value="<?= htmlspecialchars($studentData['first_name']) ?>">
                </div>
                <div class="mb-3">
                    <label for="lastName" class="form-label">Last Name</label>
                    <input type="text" name="lastName" class="form-control" id="lastName" value="<?= htmlspecialchars($studentData['last_name']) ?>">
                </div>
                <button type="submit" class="btn btn-primary" name="updateStudent">Update Student</button>
            </form>
        </div>
    </div>
</div>

<?php include('../partials/footer.php'); ?>
