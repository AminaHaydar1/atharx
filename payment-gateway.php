<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html?error=authentication_required");
    exit();
}

if (!isset($_SESSION['pending_order'])) {
    header("Location: customize.php?error=no_active_configuration");
    exit();
}

$order_data = $_SESSION['pending_order'];
$total_price = floatval($order_data['total_price']);

$order_processed = false;
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = "localhost";
    $db_user = "root";          
    $db_pass = "";              
    $db_name = "athar_db"; 

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        die("Database Connection Failure: " . $conn->connect_error);
    }

    $user_id        = $_SESSION['user_id']; 
    $product_name   = $conn->real_escape_string($order_data['product_name']);
    $specifications = $conn->real_escape_string($order_data['specifications']);
    $quantity       = intval($order_data['quantity']);
    $images_serialized = $conn->real_escape_string(json_encode($order_data['images']));

    $payment_method = $_POST['payment_method'];

    if ($payment_method === 'card') {
        // User paid with card: Save details and generate Card ID
        $cardholder_name = "'" . $conn->real_escape_string($_POST['cardholder_name']) . "'";
        $raw_card_num    = preg_replace('/\D/', '', $_POST['card_number']);
        $card_last4      = "'" . substr($raw_card_num, -4) . "'";
        $card_transaction_id = "'CARD_ID_" . strtoupper(bin2hex(random_bytes(8))) . "'";
        $payment_status_flag = "Paid via Card";
    } else {
        // User chose Cash/Alternative: Set card fields to NULL explicitly
        $cardholder_name = "NULL";
        $card_last4      = "NULL";
        $card_transaction_id = "NULL";
        $payment_status_flag = "Pending (Cash on Delivery)";
    }

    $order_status_flag = "Processing";

    $sql_insert_query = "INSERT INTO user_orders (user_id, product_name, specifications, quantity, total_price, design_images, cardholder_name, card_last4, card_transaction_id, payment_status, order_status) 
                         VALUES ('$user_id', '$product_name', '$specifications', '$quantity', '$total_price', '$images_serialized', $cardholder_name, $card_last4, $card_transaction_id, '$payment_status_flag', '$order_status_flag')";

    if ($conn->query($sql_insert_query) === TRUE) {
        $order_processed = true;
        unset($_SESSION['pending_order']);
    } else {
        $error_message = "SQL Error: " . $conn->error;
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout Gateway | Athar Studio</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', system-ui, sans-serif; }
        body {
            background-color: #0b0813; color: #ffffff; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;
            background-image: radial-gradient(circle at 50% 50%, rgba(168, 85, 247, 0.12) 0%, transparent 60%);
        }
        .gateway-card {
            background: rgba(15, 12, 28, 0.85); backdrop-filter: blur(16px); border: 1px solid rgba(168, 85, 247, 0.2);
            border-radius: 20px; width: 100%; max-width: 480px; padding: 35px; box-shadow: 0 15px 35px rgba(0,0,0,0.6);
        }
        .success-view { text-align: center; }
        .success-icon { width: 60px; height: 60px; border-radius: 50%; background: rgba(34,197,94,0.1); border: 2px solid #22c55e; color: #22c55e; font-size: 28px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
        
        h1 { font-size: 22px; margin-bottom: 8px; font-weight: 700; text-align: center; }
        .price-tag { text-align: center; color: #a855f7; font-size: 24px; font-weight: 800; margin-bottom: 25px; }
        
        .method-selector { display: flex; gap: 10px; margin-bottom: 20px; }
        .method-option { flex: 1; text-align: center; padding: 12px; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; cursor: pointer; background: rgba(0,0,0,0.2); font-size: 13px; font-weight: 600; }
        .method-option.active { border-color: #a855f7; background: rgba(168, 85, 247, 0.1); color: #a855f7; }
        
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; margin-bottom: 6px; }
        .form-control { width: 100%; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 12px; color: #fff; font-size: 14px; }
        .form-control:focus { border-color: #a855f7; outline: none; }
        
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        
        .btn-submit {
            background: linear-gradient(to right, #a855f7, #3b82f6); color: #fff; border: none;
            width: 100%; padding: 14px; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; transition: all 0.2s; margin-top: 10px;
        }
        .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 0 15px rgba(168, 85, 247, 0.4); }
        .btn-redirect { display: block; text-decoration: none; text-align: center; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1); width: 100%; padding: 12px; border-radius: 8px; margin-top: 15px; }
    </style>
</head>
<body>

    <div class="gateway-card">
        <?php if ($order_processed): ?>
            <div class="success-view">
                <div class="success-icon">✓</div>
                <h1>Order Confirmed</h1>
                <p style="color: #94a3b8; font-size: 14px; margin-bottom: 20px;">Your custom asset configurations have been processed successfully.</p>
                <a href="dashboard.php" class="btn-submit" style="display:inline-block; text-decoration:none;">Go to Dashboard</a>
            </div>
        <?php else: ?>
            <h1>Checkout Process</h1>
            <div class="price-tag">$<?php echo number_format($total_price, 2); ?></div>

            <form method="POST" action="">
                <input type="hidden" name="payment_method" id="payment_method_input" value="card">
                <div class="method-selector">
                    <div class="method-option active" id="opt_card" onclick="setMethod('card')">Credit Card</div>
                    <div class="method-option" id="opt_cash" onclick="setMethod('cash')">Cash / COD</div>
                </div>

                <div id="card_fields_wrapper">
                    <div class="form-group">
                        <label>Cardholder Name</label>
                        <input type="text" name="cardholder_name" id="card_name" class="form-control" placeholder="John Doe" required>
                    </div>
                    <div class="form-group">
                        <label>Credit Card Number</label>
                        <input type="text" name="card_number" id="card_num" class="form-control" placeholder="•••• •••• •••• ••••" maxlength="19" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Expiration</label>
                            <input type="text" id="card_exp" class="form-control" placeholder="MM/YY" maxlength="5" required>
                        </div>
                        <div class="form-group">
                            <label>CVV</label>
                            <input type="password" id="card_cvv" class="form-control" placeholder="•••" maxlength="4" required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Complete Order Placement</button>
                <a href="customize.php" class="btn-redirect">Cancel</a>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function setMethod(method) {
            document.getElementById('payment_method_input').value = method;
            
            const cardOpt = document.getElementById('opt_card');
            const cashOpt = document.getElementById('opt_cash');
            const cardFields = document.getElementById('card_fields_wrapper');
            
            const inputs = cardFields.querySelectorAll('input');

            if (method === 'card') {
                cardOpt.classList.add('active');
                cashOpt.classList.remove('active');
                cardFields.style.display = 'block';
                // Make fields required
                inputs.forEach(inpt => inpt.setAttribute('required', 'true'));
            } else {
                cashOpt.classList.add('active');
                cardOpt.classList.remove('active');
                cardFields.style.display = 'none';
                // Remove required status so form submits smoothly
                inputs.forEach(inpt => inpt.removeAttribute('required'));
            }
        }
    </script>
</body>
</html>