<?php
include '../../functions.php';

if (!isset($_GET['student_id'])) {
    header("Location: register.php");
    exit();
}

$studentId = $_GET['student_id'];
$conn = connectDatabase();

// Fetch student details
$stmt = $conn->prepare("SELECT student_id, first_name, last_name FROM students WHERE student_id = ?");
$stmt->bind_param("s", $studentId);
$stmt->execute();
$studentResult = $stmt->get_result();

if ($studentResult->num_rows === 0) {
    header("Location: register.php");
    exit();
}

$student = $studentResult->fetch_assoc();

// Fetch available subjects
$subjectQuery = "SELECT subject_code, subject_name FROM subjects";
$subjectResult = $conn->query($subjectQuery);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['subjects'])) {
        $subjects = $_POST['subjects'];

        foreach ($subjects as $subjectCode) {
            $assignStmt = $conn->prepare("INSERT INTO student_subjects (student_id, subject_code) VALUES (?, ?)");
            $assignStmt->bind_param("ss", $studentId, $subjectCode);
            $assignStmt->execute();
            $assignStmt->close();
        }
        header("Location: register.php?msg=subjects_attached");
        exit();
    } else {
        $error = "Please select at least one subject to attach.";
    }
}
$stmt->close();
$conn->close();
?>

<?php include('../partials/header.php'); ?>

<div class="d-flex">
    <!-- Include the sidebar -->
    <?php include '../partials/side-bar.php'; ?>

    <!-- Main content area -->
    <div class="container my-5">
        <h2>Attach Subject to Student</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="register.php">Register Student</a></li>
                <li class="breadcrumb-item active" aria-current="page">Attach Subject</li>
            </ol>
        </nav>

        <div class="card p-3 mb-4">
            <h5>Selected Student Information</h5>
            <ul>
                <li><strong>Student ID:</strong> <?= htmlspecialchars($student['student_id']) ?></li>
                <li><strong>Name:</strong> <?= htmlspecialchars($student['first_name'] . " " . $student['last_name']) ?></li>
            </ul>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <?php if ($subjectResult->num_rows > 0): ?>
                    <h6>Select Subjects to Attach:</h6>
                    <?php while ($subject = $subjectResult->fetch_assoc()): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="subjects[]" value="<?= htmlspecialchars($subject['subject_code']) ?>" id="subject-<?= htmlspecialchars($subject['subject_code']) ?>">
                            <label class="form-check-label" for="subject-<?= htmlspecialchars($subject['subject_code']) ?>">
                                <?= htmlspecialchars($subject['subject_code'] . " - " . $subject['subject_name']) ?>
                            </label>
                        </div>
                    <?php endwhile; ?>
                    <button type="submit" class="btn btn-primary mt-3">Attach Subjects</button>
                <?php else: ?>
                    <p class="text-muted">No subjects available to attach.</p>
                <?php endif; ?>
            </form>

            <!-- Display Attached Subjects Table -->
            <div class="mt-4">
                <h6>Attached Subjects</h6>
                <?php
                    // Fetch attached subjects if any
                    $studentId = $student['student_id'];
                    $attachedSubjectsQuery = "SELECT subjects.subject_code, subjects.subject_name FROM subjects
                                               JOIN student_subjects ON subjects.subject_code = student_subjects.subject_code
                                               WHERE student_subjects.student_id = '$studentId'";
                    $attachedSubjectsResult = $conn->query($attachedSubjectsQuery);

                    if ($attachedSubjectsResult->num_rows > 0):
                ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Subject Code</th>
                                <th>Subject Name</th>
                                <th>Option</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($subject = $attachedSubjectsResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($subject['subject_code']) ?></td>
                                    <td><?= htmlspecialchars($subject['subject_name']) ?></td>
                                    <td>
                                        <a href="detach.php?subject_code=<?= htmlspecialchars($subject['subject_code']) ?>&student_id=<?= htmlspecialchars($student['student_id']) ?>" class="btn btn-sm btn-danger">Detach</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No subjects attached to this student yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>



<?php include('../partials/footer.php'); ?>
