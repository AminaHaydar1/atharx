<?php
// 1. Initialize secure session management
session_start();

// 2. Access Control Gatekeeper: Ensure user is authenticated
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html?error=authentication_required");
    exit();
}

// 3. Establish database connection stack
$db_host = "localhost"; 
$db_user = "root"; 
$db_pass = ""; 
$db_name = "athar_db"; 

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) { 
    die("Database Connection Error: " . $e->getMessage()); 
}

// --- STATE A: PROCESSING INITIAL POST FROM CUSTOMIZATION WORKSPACE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_type'])) {
    
    $product_type   = htmlspecialchars(trim($_POST['product_type'] ?? 'Custom Asset'));
    $custom_specs   = htmlspecialchars(trim($_POST['custom_specs'] ?? 'No custom specifications provided.'));
    $unit_price     = isset($_POST['price']) ? floatval($_POST['price']) : 0.00;
    $quantity       = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $total_price    = $unit_price * $quantity; 

    // Handle Server Upload Storage Layout Directory
    $upload_directory = 'uploads/custom_designs/';
    if (!file_exists($upload_directory)) {
        mkdir($upload_directory, 0755, true);
    }

    $saved_file_paths = [];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /* --- PROCESSING PIPELINE PART A: MULTIPLE IMAGES ARRAY --- */
    if (isset($_FILES['design_images']) && is_array($_FILES['design_images']['name']) && !empty($_FILES['design_images']['name'][0])) {
        $total_uploaded_files = count($_FILES['design_images']['name']);
        for ($i = 0; $i < $total_uploaded_files; $i++) {
            if ($_FILES['design_images']['error'][$i] === UPLOAD_ERR_OK) {
                $file_tmp_path = $_FILES['design_images']['tmp_name'][$i];
                $file_raw_name = $_FILES['design_images']['name'][$i];
                $file_extension = strtolower(pathinfo($file_raw_name, PATHINFO_EXTENSION));

                if (in_array($file_extension, $allowed_extensions)) {
                    $unique_filename = 'multi_' . uniqid() . '_' . time() . '_' . $i . '.' . $file_extension;
                    $destination_target = $upload_directory . $unique_filename;
                    if (move_uploaded_file($file_tmp_path, $destination_target)) {
                        $saved_file_paths[] = $destination_target;
                    }
                }
            }
        }
    }

    /* --- PROCESSING PIPELINE PART B: SINGLE IMAGE RECOVERY BRIDGE --- */
    if (isset($_FILES['design_image']) && $_FILES['design_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['design_image']['tmp_name'];
        $file_raw_name = $_FILES['design_image']['name'];
        $file_extension = strtolower(pathinfo($file_raw_name, PATHINFO_EXTENSION));

                if (in_array($file_extension, $allowed_extensions)) {
                    $unique_filename = 'single_' . uniqid() . '_' . time() . '.' . $file_extension;
                    $destination_target = $upload_directory . $unique_filename;
                    if (move_uploaded_file($file_tmp_path, $destination_target)) {
                        $saved_file_paths[] = $destination_target;
                    }
                }
            }

    $order_reference_id = 'ATHAR-' . strtoupper(substr(md5(uniqid()), 0, 8));
    $serialized_files = !empty($saved_file_paths) ? implode(',', $saved_file_paths) : '';

    // Cache order configurations to active session storage bucket
    $_SESSION['pending_checkout'] = [
        'order_id'       => $order_reference_id,
        'product_name'   => $product_type,
        'specifications' => $custom_specs,
        'quantity'       => $quantity,
        'total_price'    => $total_price,
        'images'         => $saved_file_paths,
        'serialized'     => $serialized_files
    ];

// --- STATE B: FINAL GATEWAY SUBMISSION SUBMIT PROCESSING ---
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_gateway_charge'])) {
    
    if (!isset($_SESSION['pending_checkout'])) {
        header("Location: customize.php");
        exit();
    }

    $checkout_data = $_SESSION['pending_checkout'];
    $chosen_method = $_POST['payment_method'] ?? 'card'; // dynamic capture from checkout UI choice
    
    if ($chosen_method === 'card') {
        $payment_status = 'Paid';
        $order_status   = 'In Production';
        $cardholder     = strtoupper(trim($_POST['cc_name'] ?? 'CREDIT CARD HOLDER'));
    } else {
        $payment_status = 'Pending Whish Verification';
        $order_status   = 'Processing';
        $cardholder     = 'WHISH ROUTING TRANSFERS';
    }

    // Direct Database Execution Matching Your Table Structures Completely
    $sql = "INSERT INTO user_orders (user_id, product_name, specifications, quantity, total_price, design_images, cardholder_name, card_transaction_id, payment_status, order_status) 
            VALUES (:user_id, :product_name, :specifications, :quantity, :total_price, :design_images, :cardholder, :ref, :p_status, :o_status)";
    
    $pdo->prepare($sql)->execute([
        ':user_id'        => $_SESSION['user_id'] ?? 1,
        ':product_name'   => $checkout_data['product_name'],
        ':specifications' => $checkout_data['specifications'],
        ':quantity'       => $checkout_data['quantity'],
        ':total_price'    => $checkout_data['total_price'],
        ':design_images'  => $checkout_data['serialized'],
        ':cardholder'     => $cardholder,
        ':ref'            => $checkout_data['order_id'],
        ':p_status'       => $payment_status,
        ':o_status'       => $order_status
    ]);

    unset($_SESSION['pending_checkout']);
    header("Location: dashboard.php?order=complete");
    exit();

} else {
    // Session state fallback check
    if (isset($_SESSION['pending_checkout'])) {
        $checkout_data = $_SESSION['pending_checkout'];
        $order_reference_id = $checkout_data['order_id'];
        $product_type       = $checkout_data['product_name'];
        $custom_specs       = $checkout_data['specifications'];
        $quantity           = $checkout_data['quantity'];
        $total_price        = $checkout_data['total_price'];
        $saved_file_paths   = $checkout_data['images'];
    } else {
        header("Location: customize.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Studio Order | Athar Studio</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', system-ui, sans-serif; }
        body {
            background-color: #0b0813; color: #ffffff; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;
            background-image: radial-gradient(circle at 50% 50%, rgba(168, 85, 247, 0.12) 0%, transparent 60%);
        }
        .invoice-card {
            background: rgba(15, 12, 28, 0.85); backdrop-filter: blur(16px); border: 1px solid rgba(168, 85, 247, 0.3);
            border-radius: 20px; width: 100%; max-width: 540px; padding: 40px; box-shadow: 0 15px 35px rgba(0,0,0,0.6);
        }
        .header-status { text-align: center; margin-bottom: 25px; }
        .success-badge {
            background: rgba(168, 85, 247, 0.15); border: 1px solid #a855f7; color: #c084fc;
            display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
        }
        .invoice-card h1 { font-size: 24px; margin-top: 15px; font-weight: 700; color: #fff; }
        .details-list { margin-bottom: 25px; border-bottom: 1px dashed rgba(255,255,255,0.1); padding-bottom: 20px; }
        .detail-row { display: flex; justify-content: space-between; margin-bottom: 14px; font-size: 14px; }
        .detail-row .label { color: #94a3b8; font-weight: 500; }
        .detail-row .value { color: #ffffff; font-weight: 600; text-align: right; max-width: 60%; }
        
        .gallery-title { font-size: 12px; text-transform: uppercase; color: #a855f7; letter-spacing: 1px; font-weight: 700; margin-bottom: 12px; }
        .image-preview-grid { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 25px; }
        .uploaded-thumbnail { width: 75px; height: 75px; object-fit: cover; border-radius: 8px; border: 2px solid #a855f7; background: #120e24; display: block; }
        
        /* INTERACTIVE PAYMENT OPTION SELECTOR */
        .method-selector { display: flex; gap: 12px; margin-bottom: 20px; }
        .method-option { flex: 1; position: relative; }
        .method-option input { position: absolute; opacity: 0; width: 100%; height: 100%; cursor: pointer; z-index: 2; }
        .method-box {
            background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 14px; border-radius: 10px; text-align: center; font-size: 13px; font-weight: 700;
            text-transform: uppercase; color: #94a3b8; transition: all 0.2s;
        }
        .method-option input:checked + .method-box {
            background: rgba(168, 85, 247, 0.15); border-color: #a855f7; color: #fff;
            box-shadow: 0 0 12px rgba(168, 85, 247, 0.2);
        }

        .gateway-panel { background: rgba(0,0,0,0.25); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 12px; padding: 25px; }
        .input-field { width: 100%; padding: 12px 15px; background: #120e24; border: 1px solid rgba(168,85,247,0.4); border-radius: 8px; color: white; margin-bottom: 12px; box-sizing: border-box; font-size: 14px; }
        .input-field:focus { border-color: #a855f7; outline: none; }
        
        .action-row { display: flex; gap: 15px; margin-top: 15px; }
        .btn-primary {
            background: linear-gradient(to right, #a855f7, #3b82f6); color: #fff; border: none; width: 100%; padding: 14px;
            border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; text-align: center; text-decoration: none; transition: transform 0.2s;
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 0 15px rgba(168, 85, 247, 0.35); }
        .btn-secondary {
            background: rgba(255,255,255,0.04); color: #94a3b8; border: 1px solid rgba(255,255,255,0.08); padding: 14px 24px;
            border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; text-align: center; text-decoration: none;
        }
        .btn-secondary:hover { color: #fff; background: rgba(255,255,255,0.08); }
        .whish-alert { background: rgba(168, 85, 247, 0.08); border-left: 4px solid #a855f7; padding: 15px; border-radius: 0 8px 8px 0; font-size: 14px; color: #e9d5ff; line-height: 1.5; }
    </style>
</head>
<body>

    <div class="invoice-card">
        <div class="header-status">
            <span class="success-badge">Secure Gateway</span>
            <h1>Finalize Custom Order</h1>
        </div>

        <div class="details-list">
            <div class="detail-row">
                <span class="label">Reference Code</span>
                <span class="value" style="font-family: monospace; color:#3b82f6; font-size:15px;"><?php echo $order_reference_id; ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Product Line</span>
                <span class="value"><?php echo $product_type; ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Custom Specifications</span>
                <span class="value" style="font-size:12px;"><?php echo $custom_specs; ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Quantity</span>
                <span class="value"><?php echo $quantity; ?> units</span>
            </div>
            <div class="detail-row" style="margin-top: 15px; font-size: 18px;">
                <span class="label" style="color:#fff; font-weight:700;">Total Balance</span>
                <span class="value" style="color:#22c55e; font-weight:700;">$<?php echo number_format($total_price, 2); ?></span>
            </div>
        </div>

        <?php if (!empty($saved_file_paths)): ?>
            <div class="gallery-title">Design Blueprints Attached (<?php echo count($saved_file_paths); ?>)</div>
            <div class="image-preview-grid">
                <?php foreach ($saved_file_paths as $path): 
                    $clean_path = trim($path);
                    $final_src = (strpos($clean_path, 'http') === 0) ? $clean_path : "http://localhost/atharx/" . $clean_path;
                ?>
                    <img src="<?php echo htmlspecialchars($final_src); ?>" class="uploaded-thumbnail" alt="Mockup Detail">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="checkout.php" method="POST">
            
            <div class="method-selector">
                <div class="method-option">
                    <input type="radio" name="payment_method" value="card" checked id="select_card" onclick="switchTab('card')">
                    <div class="method-box">Credit Card</div>
                </div>
                <div class="method-option">
                    <input type="radio" name="payment_method" value="whish" id="select_whish" onclick="switchTab('whish')">
                    <div class="method-box">Whish Money</div>
                </div>
            </div>

            <div class="gateway-panel">
                <div id="panel_card">
                    <input type="text" name="cc_name" id="cc_name_field" placeholder="Cardholder Full Name" required class="input-field">
                    <input type="text" placeholder="Card Number (0000 0000 0000 0000)" class="input-field">
                    <div style="display: flex; gap: 10px;">
                        <input type="text" placeholder="MM/YY" class="input-field" style="flex: 1;">
                        <input type="password" placeholder="CVC" class="input-field" style="flex: 1;">
                    </div>
                </div>

                <div id="panel_whish" style="display: none;">
                    <div class="whish-alert">
                        Please forward the exact invoice total amount of <strong>$<?php echo number_format($total_price, 2); ?></strong> directly via your mobile app to our corporate registered Whish number:<br>
                        <strong style="color: #fff; font-size: 16px; display: block; margin-top: 8px; letter-spacing: 0.5px;">+961 76 123 456</strong>
                    </div>
                </div>

                <div class="action-row">
                    <a href="customize.php" class="btn-secondary">Modify</a>
                    <button type="submit" name="submit_gateway_charge" id="submit_btn" class="btn-primary">Authorize Payment</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function switchTab(method) {
            const cardPanel = document.getElementById('panel_card');
            const whishPanel = document.getElementById('panel_whish');
            const ccNameField = document.getElementById('cc_name_field');
            const submitBtn = document.getElementById('submit_btn');

            if (method === 'card') {
                cardPanel.style.display = 'block';
                whishPanel.style.display = 'none';
                ccNameField.required = true;
                submitBtn.innerText = "Authorize Payment";
                submitBtn.style.background = "linear-gradient(to right, #a855f7, #3b82f6)";
            } else {
                cardPanel.style.display = 'none';
                whishPanel.style.display = 'block';
                ccNameField.required = false;
                submitBtn.innerText = "Confirm Transfer & Log Order";
                submitBtn.style.background = "#a855f7";
            }
        }
    </script>
</body>
</html>