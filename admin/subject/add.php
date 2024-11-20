<?php 
session_start();
include '../partials/header.php';
include '../../functions.php';

$servername = "localhost";
$username = "root";
$password = ""; 
$database = "dct-ccs-finals";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$pageTitle = "Add Subject";  
$errorArray = [];
$code = "";
$name = "";

// Handle Add Subject Form Submission
if (isset($_POST['addButton'])) {
    $code = trim($_POST['subCode']);
    $name = trim($_POST['subName']);

    $errorArray = validateSubjectData(['subject_code' => $code, 'subject_name' => $name]);

    if (empty($errorArray)) {
        $duplicateErrors = checkDuplicateSubjectData($conn, [
            'subject_code' => $code,
            'subject_name' => $name
        ]);

        if (!empty($duplicateErrors)) {
            $errorArray = array_merge($errorArray, $duplicateErrors);
        }
    }

    if (empty($errorArray)) {
        $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name) VALUES (?, ?)");
        $stmt->bind_param("ss", $code, $name);

        if ($stmt->execute()) {
            $code = "";
            $name = ""; 
        } else {
            $errorArray[] = "Error adding subject: " . $conn->error;
        }

        $stmt->close();
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../partials/side-bar.php'; ?>

        <main class="col-md-10 ms-sm-auto col-lg-10 px-md-4">
            <div class="container my-5">
                <!-- Display Errors -->
                <?php if (!empty($errorArray)) { echo displayErrors($errorArray); } ?>
                
                <!-- Add Subject Form -->
                <h2>Add a New Subject</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../root/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Add Subject</li>
                    </ol>
                </nav>
                
                <div class="card p-4 mb-4">
                    <form method="post">       
                        <div class="mb-3">
                            <label for="subjectCode" class="form-label">Subject Code</label>
                            <input type="text" name="subCode" class="form-control" id="subjectCode" placeholder="Enter Subject Code" value="<?= htmlspecialchars($code) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="subjectName" class="form-label">Subject Name</label>
                            <input type="text" name="subName" class="form-control" id="subjectName" placeholder="Enter Subject Name" value="<?= htmlspecialchars($name) ?>">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block w-100" name="addButton">Add Subject</button>
                    </form>
                </div>

                <!-- Subject List Table -->
                <div class="card p-4">
                    <h5 class="card-title">Subject List</h5>
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Subject Code</th>
                                <th scope="col">Subject Name</th>
                                <th scope="col">Option</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("SELECT * FROM subjects");
                            if ($result->num_rows > 0):
                                while ($subject = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($subject['subject_code']) ?></td>
                                        <td><?= htmlspecialchars($subject['subject_name']) ?></td>
                                        <td>
                                            <a href="edit.php?code=<?= urlencode($subject['subject_code']) ?>" class="btn btn-info btn-sm">Edit</a>
                                            <a href="delete.php?code=<?= urlencode($subject['subject_code']) ?>" class="btn btn-danger btn-sm">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile;
                            else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">No subjects found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include('../partials/footer.php'); ?>
