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
    
    
    function validateLoginCredentials($email, $password, $connection) {
        $errorArray = [];
    
        if (empty($email)) {
            $errorArray['email'] = 'Email Address is required!';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorArray['email'] = 'Email Address is invalid!';
        }
    
        if (empty($password)) {
            $errorArray['password'] = 'Password is required!';
        }
    
        if (empty($errorArray)) {
            if (!checkLoginCredentials($email, $password, $connection)) {
                $errorArray['credentials'] = 'Incorrect email or password!';
            }
        }
    
        return $errorArray;
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
    function displayErrors($errors) {
        if (empty($errors)) {
            return ''; 
        }
    
        $output = '
        <div class="alert alert-danger alert-dismissible fade show mx-auto my-5" style="margin-bottom: 20px;" role="alert">
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
    
        if (empty($student_data['ID'])) {
            $errorArray['ID'] = 'Student ID is required!';
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
        // Database connection
        $conn = new mysqli("localhost", "root", "", "dct_ccs_finals");
        
        // Query to find student ID
        $stmt = $conn->prepare("SELECT id FROM students WHERE student_id = ?");
        $stmt->bind_param("s", $studentID);
        $stmt->execute();
        $stmt->bind_result($studentIndex);
        $stmt->fetch();
    
        // Close connection
        $stmt->close();
        $conn->close();
    
        return $studentIndex ?? null;
    }

    function getSelectedStudentData($studentID) {
        // Database connection
        $conn = new mysqli("localhost", "root", "", "dct_ccs_finals");
    
        // Query to retrieve student data
        $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->bind_param("s", $studentID);
        $stmt->execute();
        $result = $stmt->get_result();
    
        // Fetch student data
        if ($student = $result->fetch_assoc()) {
            // Close connection
            $stmt->close();
            $conn->close();
            return $student;
        }
    
        // Close connection
        $stmt->close();
        $conn->close();
    
        return null;
    }
    
    
?>