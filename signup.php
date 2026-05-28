<?php
// 1. Enable strict error reporting to catch any hidden issues instantly
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. Database connection configuration settings
    $db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "athar_db"; 

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        die("Critical Engine Error: Database connection failed. " . $conn->connect_error);
    }

    // 3. Capture form inputs from signup.html and trim extra spaces
    $full_name    = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email        = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone_number = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
    $password_raw = isset($_POST['password']) ? $_POST['password'] : '';

    // 4. Input validation check: Ensure core items are present
    if (empty($full_name) || empty($email) || empty($password_raw)) {
        header("Location: signup.html?error=empty_fields");
        exit();
    }

    // 5. Check for duplicate accounts using your exact 'full_name' column
    $check_query = "SELECT id FROM users WHERE email = ? OR full_name = ? LIMIT 1";
    $stmt = $conn->prepare($check_query);
    if (!$stmt) {
        die("SQL error during account lookup validation. Check if 'full_name' column exists: " . $conn->error);
    }
    
    $stmt->bind_param("ss", $email, $full_name);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        // User already exists, bounce back to form with error flag
        header("Location: signup.html?error=credential_taken");
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();

    // 6. Securely hash the password using industry-standard BCRYPT
    $password_secure = password_hash($password_raw, PASSWORD_BCRYPT);

    // 7. Insert new user record using your exact 'password_hash' column name
    $insert_query = "INSERT INTO users (full_name, email, phone_number, password_hash) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    if (!$stmt) {
        die("Insert query initialization failed! Verify that your 'users' table has the 'phone_number' and 'password_hash' columns: " . $conn->error);
    }
    
    $stmt->bind_param("ssss", $full_name, $email, $phone_number, $password_secure);
    
    if ($stmt->execute()) {
        // Registration successful! Redirect directly to your login page
        header("Location: login.html?success=account_created");
        exit();
    } else {
        // Something went wrong inside the database engine during execution
        header("Location: signup.html?error=system_fault");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    // If someone tries to access this script without submitting the form, send them back to signup
    header("Location: signup.html");
    exit();
}
?>