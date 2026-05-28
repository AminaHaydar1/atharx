<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['staging_order'])) {
    $order = $_SESSION['staging_order'];
    $gateway = htmlspecialchars($_POST['selected_gateway'] ?? 'credit_card');
    
    /* 1. DATABASE SYNC INTEGRATION POINT
          This is where you execute your queries:
          INSERT INTO orders (reference, item, specs, amount, client_id, status) 
          VALUES ('{$order['order_reference_id']}', '{$order['product_type']}', ...)
    */

    // 2. Clear out staging cache memory to avoid duplicate transaction submissions
    unset($_SESSION['staging_order']);

    // 3. Inform customer of success
    echo "
    <div style='background:#0b0813; color:#fff; height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; font-family:sans-serif;'>
        <div style='border:1px solid #22c55e; padding:40px; border-radius:12px; background:rgba(34,197,94,0.05); text-align:center; max-width:500px;'>
            <h1 style='color:#22c55e; margin-bottom:10px;'>Order Successfully Staged!</h1>
            <p style='color:#94a3b8; font-size:14px; line-height:1.5; margin-bottom:20px;'>
                Your custom asset array is locked in. The production blueprint queue has accepted Reference ID: <strong>{$order['order_reference_id']}</strong>.
            </p>
            <a href='dashboard.php' style='display:inline-block; padding:12px 24px; border-radius:6px; background:#a855f7; color:#fff; text-decoration:none; font-weight:bold;'>Go to Studio Dashboard</a>
        </div>
    </div>";
} else {
    header("Location: customize.php");
    exit();
}
?>