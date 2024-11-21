<?php    
    // All project functions should be placed here
    function getUsersFromDatabase($connection) {
        $users = [];
        $query = "SELECT email, password FROM users"; // Adjust columns as per your table structure
        $result = mysqli_query($connection, $query);
    
        while ($row = mysqli_fetch_assoc($result)) {
            $users[$row['email']] = $row['password']; // Assumes passwords are stored hashed
        }
    
        return $users;
    }

    function connectDatabase(): mysqli {
        $servername = 'localhost';
        $username = 'root';        
        $password = "";            
        $dbname = 'dct-ccs-finals'; 
    
        $conn = new mysqli($servername, $username, $password, $dbname);
    
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    
        return $conn;
    }

    function fetchTotalSub($db) {
        $query = "SELECT COUNT(*) AS total_students FROM students";
    $result = $db->query($query);

    if ($result) {
        $row = $result->fetch_assoc();
        return (int) $row['total_students'];
    } else {
        return 0; 
    }
    }

    function fetchTotalStud($db){
        $query = "SELECT COUNT(*) AS total_students FROM students";
    $result = $db->query($query);

    if ($result) {
        $row = $result->fetch_assoc();
        return (int) $row['total_students'];
    } else {
        return 0;
    }
}

    function fetchPassedStud ($conn) {
        $query = "
        SELECT COUNT(*) AS passed_count
        FROM (
            SELECT student_id, AVG(grade) AS avg_grade
            FROM students_subjects
            WHERE grade IS NOT NULL
            GROUP BY student_id
            HAVING avg_grade >= 75
        ) AS passed_students";
    $result = $conn->query($query);
    return $result->fetch_assoc()['passed_count'] ?? 0;
    }

    function fetchFailedStud($conn) {
        $query = "
        SELECT COUNT(*) AS failed_count
        FROM (
            SELECT student_id, AVG(grade) AS avg_grade
            FROM students_subjects
            WHERE grade IS NOT NULL
            GROUP BY student_id
            HAVING avg_grade < 75
        ) AS failed_students";
    $result = $conn->query($query);
    return $result->fetch_assoc()['failed_count'] ?? 0;
    }



    //For User Validations
    function authenticateUser($email, $password)
{
    $conn = connectDatabase();
    $hashedPassword = md5($password); // Use MD5 as per your project requirement

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $hashedPassword);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}



function loginUser($user)
{
    $_SESSION['loggedin'] = true;
    $_SESSION['user'] = $user;
}


function displayErrors($errors)
{
    if (empty($errors)) {
        return '';
    }

    $output = '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
    $output .= '<strong>Errors:</strong> Please address the following issues:<hr><ul>';
    foreach ($errors as $error) {
        $output .= '<li>' . htmlspecialchars($error) . '</li>';
    }
    $output .= '</ul><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    return $output;
}
    

    function checkLoginCredentials($email, $password, $connection) {
        $query = "SELECT password FROM users WHERE email = ?";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    
        if ($row = mysqli_fetch_assoc($result)) {
            $storedHash = $row['password'];
            return md5($password) === $storedHash; 
        }
    
        return false;
    }

    function displayErrorsLogin($errors) {
        if (empty($errors)) {
            return ''; 
        }
        
        $output = '
        <div class="alert alert-danger alert-dismissible fade show mx-auto my-2" style="max-width: 600px; margin-bottom: 20px;" role="alert">
            <strong>System Errors:</strong> Please correct the following errors.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <hr>
            <ul>';
    
        foreach ($errors as $error) {
            $output .= '<li>' . htmlspecialchars($error) . '</li>';
        }
        $output .= '</ul></div>';
    
        return $output;
    }
   

    //for subject
    function checkDuplicateSubjectData($conn, $subject_data) {
        $errors = [];
    
        $stmt = $conn->prepare("SELECT * FROM subjects WHERE subject_code = ? OR subject_name = ?");
        $stmt->bind_param("ss", $subject_data['subject_code'], $subject_data['subject_name']);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if ($row['subject_code'] === $subject_data['subject_code']) {
                    $errors[] = "A subject with this code already exists.";
                }
                if ($row['subject_name'] === $subject_data['subject_name']) {
                    $errors[] = "A subject with this name already exists.";
                }
            }
        }
    
        $stmt->close();
        return $errors;
    }
    
    function validateSubjectData($subject_data) {
        $errorArray = [];
    
        if (empty($subject_data['subject_code'])) {
            $errorArray['subject_code'] = 'Subject code is required!';
        }
    
        if (empty($subject_data['subject_name'])) {
            $errorArray['subject_name'] = 'Subject name is required!';
        }
        return $errorArray;
    }
    
    //for students
    function validateStudentData($student_data) {
        $errorArray = [];
    
        if (empty($student_data['ID'])) { // Assuming key is 'ID'
            $errorArray['ID'] = 'Student ID is required!';
        } elseif (!is_numeric($student_data['ID'])) {
            $errorArray['ID'] = 'Student ID must be numeric!';
        }
    
        if (empty($student_data['first_name'])) {
            $errorArray['first_name'] = 'First name is required!';
        }
    
        if (empty($student_data['last_name'])) {
            $errorArray['last_name'] = 'Last name is required!';
        }
    
        return $errorArray;
    }
    

    function checkDuplicateStudentData($student_data) {
        $errors = [];
    
        // Database connection
        $conn = new mysqli("localhost", "root", "", "dct_ccs_finals");
    
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    
        // Check for existing student by ID
        $stmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE student_id = ?");
        $stmt->bind_param("s", $student_data['ID']);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
    
        if ($count > 0) {
            $errors[] = "A student with this ID already exists.";
        }
    
        // Close connection
        $stmt->close();
        $conn->close();
    
        return $errors;
    }
    
    function getSelectedStudentIndex($studentID) {
        if (empty($studentID)) {
            return null;
        }
    
        // Database connection
        $conn = new mysqli("localhost", "root", "", "dct_ccs_finals");
    
        if ($conn->connect_error) {
            die("Database connection failed: " . $conn->connect_error);
        }
    
        // Query to find student ID
        $stmt = $conn->prepare("SELECT id FROM students WHERE student_id = ?");
        if (!$stmt) {
            die("Failed to prepare statement: " . $conn->error);
        }
    
        $stmt->bind_param("s", $studentID);
        $stmt->execute();
        $stmt->bind_result($studentIndex);
        $stmt->fetch();
    
        // Clean up
        $stmt->close();
        $conn->close();
    
        return $studentIndex ?: null;
    }

    function userLogout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start(); 
        }
        session_destroy(); 
        header("Location:../index.php"); 
        exit();
    }
    
    

    function getSelectedStudentData($student_id) {
        $connection = connectDatabase();
        $query = "SELECT * FROM students WHERE id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $stmt->close();
        $connection->close();
    
        return $student;
    }

    function verifyStudentData($data) {
        $validation_errors = [];
        if (empty($data['id_number'])) {
            $validation_errors[] = "Student ID is required.";
        }
        if (empty($data['first_name'])) {
            $validation_errors[] = "First Name is required.";
        }
        if (empty($data['last_name'])) {
            $validation_errors[] = "Last Name is required.";
        }
    
        return $validation_errors;
    }
    
    function isStudentIdDuplicate($data) {
        $db = connectDatabase();
        $sql = "SELECT * FROM students WHERE student_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('s', $data['id_number']);
        $stmt->execute();
        $res = $stmt->get_result();
    
        if ($res->num_rows > 0) {
            return "This Student ID is already taken.";
        }
    
        return '';
    }
    
    function createUniqueStudentId() {
        $db = connectDatabase();
        $query = "SELECT MAX(id) AS current_max FROM students";
        $result = $db->query($query);
        $data = $result->fetch_assoc();
        $db->close();
    
        return ($data['current_max'] ?? 0) + 1;
    }
    
    function sanitizeStudentId($id) {
        return substr($id, 0, 4);
    }
    
    function fetchStudentDetails($id) {
        $db = connectDatabase();
        $sql = "SELECT * FROM students WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $details = $result->fetch_assoc();
    
        $stmt->close();
        $db->close();
    
        return $details;
    }

    function displayAlert($messages, $alertType = 'danger') {
        // Return an empty string if there are no messages
        if (!$messages) {
            return '';
        }
    
        // Convert single message to an array for consistent handling
        $messages = (array) $messages;
    
        // Build the alert box HTML
        $alertHTML = '<div class="alert alert-' . htmlspecialchars($alertType) . ' alert-dismissible fade show" role="alert">';
        $alertHTML .= '<ul>';
        foreach ($messages as $message) {
            $alertHTML .= '<li>' . htmlspecialchars($message) . '</li>';
        }
        $alertHTML .= '</ul>';
        $alertHTML .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        $alertHTML .= '</div>';
    
        return $alertHTML;
    }
    
    
    
    
    
    
    
?>