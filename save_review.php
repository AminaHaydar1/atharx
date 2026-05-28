<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['comment'])) {
    $db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "athar_db"; 

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        die("Connection Error: " . $conn->connect_error);
    }

    $order_id = intval($_POST['order_id']);
    $user_id = intval($_SESSION['user_id']);
    
    $user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Authenticated Studio User';
    $comment = trim($_POST['comment']);

    if (!empty($comment)) {
        // Inserts data directly into your custom general_reviews table structure
        $insert_query = "INSERT INTO general_reviews (user_id, order_id, user_name, comment) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iiss", $user_id, $order_id, $user_name, $comment);
        $stmt->execute();
        $stmt->close();
    }
    
    $conn->close();
}

header("Location: dashboard.php");
exit();
?>