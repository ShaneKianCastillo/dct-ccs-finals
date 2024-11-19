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
    
    
?>