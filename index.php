<?php
session_start();


include 'functions.php'; // Include reusable functions

$email = "";
$password = "";
$errorArray = []; // Initialize an array for errors

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // Retrieve and sanitize input
    $email = htmlspecialchars(stripslashes(trim($_POST['email'])));
    $password = $_POST['password'];

    // Validate inputs
    if (empty($email)) {
        $errorArray['email'] = 'Email Address is required!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorArray['email'] = 'Invalid Email Address!';
    }

    if (empty($password)) {
        $errorArray['password'] = 'Password is required!';
    }

    // Authenticate user if no errors
    if (empty($errorArray)) {
        $user = authenticateUser($email, $password);

        if ($user) {
            // Successful authentication
            loginUser($user); // Store user info in session
            header("Location: admin/dashboard.php");
            exit();
        } else {
            // Authentication failed
            $errorArray['credentials'] = 'Invalid email or password!';
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title><?= htmlspecialchars($pageTitle) ?></title>
</head>

<body class="bg-secondary-subtle">
    <div class="d-flex align-items-center justify-content-center vh-100">
        <div class="col-3">
            <!-- Display error messages -->
            <?= displayErrors($errorArray) ?>

            <div class="card">
                <div class="card-body">
                    <h1 class="h3 mb-4 fw-normal">Login</h1>
                    <form method="post" action="">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="email" name="email" placeholder="user@example.com" value="<?= htmlspecialchars($email) ?>">
                            <label for="email">Email address</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                            <label for="password">Password</label>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
