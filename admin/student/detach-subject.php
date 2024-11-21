<?php
session_start();
ob_start(); // Start output buffering to prevent "headers already sent" issues


$titlePage = "Detach a Subject";
require_once '../partials/header.php';

require_once '../partials/side-bar.php';
require_once '../../functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize variables
$error_message = '';
$success_message = '';

if (isset($_GET['id'])) {
    $record_id = intval($_GET['id']);

    // Fetch student and subject data based on the record ID
    $connection = connectDatabase();

    if (!$connection || $connection->connect_error) {
        $error_message = "Database connection failed: " . $connection->connect_error;
    } else {
        $query = "SELECT students.id AS student_id, students.first_name, students.last_name, 
                         subjects.subject_code, subjects.subject_name 
                  FROM students_subjects 
                  JOIN students ON students_subjects.student_id = students.id 
                  JOIN subjects ON students_subjects.subject_id = subjects.id 
                  WHERE students_subjects.id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $record_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $record = $result->fetch_assoc();

            // Handle form submission for detaching the subject
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['detach_subject'])) {
                $delete_query = "DELETE FROM students_subjects WHERE id = ?";
                $delete_stmt = $connection->prepare($delete_query);
                $delete_stmt->bind_param('i', $record_id);

                if ($delete_stmt->execute()) {
                    $success_message = "Subject successfully detached.";
                    // Redirect to the attach page with the correct student ID
                    header("Location: attach-subject.php?id=" . htmlspecialchars($record['student_id']));
                    exit;
                } else {
                    $error_message = "Failed to detach the subject. Please try again.";
                }
            }
        } else {
            $error_message = "Record not found.";
            // Redirect to the attach page if no record is found
            header("Location: attach-subject.php");
            exit;
        }
    }
} else {
    // Redirect to the attach-subject page if no ID is provided
    $error_message = "Invalid request. No ID provided.";
    header("Location: attach-subject.php");
    exit;
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
    <h1 class="h2">Detach a Subject</h1>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="../student/register.php">Register Student</a></li>
            <li class="breadcrumb-item"><a href="attach-subject.php?id=<?php echo htmlspecialchars($record['student_id'] ?? ''); ?>">Attach Subject to Student</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detach Subject from Student</li>
        </ol>
    </nav>

    <!-- Display Messages -->
    <?php echo displayAlert($error_message, 'danger'); ?>
    <?php echo displayAlert($success_message, 'success'); ?>

    <?php if (isset($record)): ?>
        <div class="card">
            <div class="card-body">
                <p><strong>Confirm Detachment:</strong></p>
                <ul>
                    <li><strong>Student ID:</strong> <?php echo htmlspecialchars($record['student_id']); ?></li>
                    <li><strong>Name:</strong> <?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></li>
                    <li><strong>Subject Code:</strong> <?php echo htmlspecialchars($record['subject_code']); ?></li>
                    <li><strong>Subject Name:</strong> <?php echo htmlspecialchars($record['subject_name']); ?></li>
                </ul>

                <!-- Detach Form -->
                <form method="post">
                    <a href="attach-subject.php?id=<?php echo htmlspecialchars($record['student_id']); ?>" class="btn btn-secondary">Cancel</a>
                    <button type="submit" name="detach_subject" class="btn btn-danger">Detach Subject</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php 
    require_once '../partials/footer.php'; 
    ob_end_flush();
?>
