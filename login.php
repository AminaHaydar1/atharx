<?php
// 1. Initialize secure session management
session_start();

// 2. Connect with your system database connection gateway
require_once 'confi.php';

// 3. Process incoming login form POST data packages
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize user email to prevent basic scripting or spacing errors
    $email    = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);

    // Basic form layout validation checks
    if (empty($email) || empty($password)) {
        header("Location: login.html?error=emptyfields");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: login.html?error=invalidemail");
        exit();
    }

    // 4. Query the database using Prepared Statements (Prevents SQL Injection)
    $sql = "SELECT id, full_name, password_hash FROM users WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // Bind the parameter securely
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        // 5. Verify if the profile exists in the database
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $fullName, $passwordHash);
            $stmt->fetch();

            // 6. Cryptographic verification of the password string against the hash
            if (password_verify($password, $passwordHash)) {
                
                // Regenerate session ID immediately to block session fixation attacks
                session_regenerate_id(true);

                // Populate secure session identification parameters
                $_SESSION['user_id']   = $id;
                $_SESSION['user_name'] = $fullName;
                $_SESSION['logged_in'] = true;

                // Close statements and database connection structures
                $stmt->close();
                $conn->close();

                // UPDATED REDIRECTION: Sends the user directly to their private account dashboard
                header("Location: dashboard.php");
                exit();
                
            } else {
                $stmt->close();
                header("Location: login.html?error=wrongpassword");
                exit();
            }
        } else {
            $stmt->close();
            header("Location: login.html?error=usernotfound");
            exit();
        }
    } else {
        header("Location: login.html?error=servererror");
        exit();
    }
} else {
    header("Location: login.html");
    exit();
}
?>