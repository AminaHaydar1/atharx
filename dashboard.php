<?php
session_start();

// Access Control Gatekeeper - Ensure customer session is verified
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'] ?? 1;

// --- DATABASE CONFIGURATION SETUP ---
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

// DYNAMIC SCHEMA DETECTION: Find out exactly what your general_reviews text column is named
$reviews_text_column = 'comment'; 
try {
    $reviewColumns = $pdo->query("DESCRIBE general_reviews")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('review_comment', $reviewColumns)) {
        $reviews_text_column = 'review_comment';
    } elseif (in_array('comment', $reviewColumns)) {
        $reviews_text_column = 'comment';
    } elseif (in_array('message', $reviewColumns)) {
        $reviews_text_column = 'message';
    } elseif (in_array('review', $reviewColumns)) {
        $reviews_text_column = 'review';
    }
} catch (Exception $schemaException) {
    // Structural safety fallback
}

// --- HANDLE INDEPENDENT GENERAL REVIEW FORM SUBMISSION ---
$message_status = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_general_review'])) {
    $order_id = intval($_POST['selected_order_id']);
    $review_text = trim($_POST['general_review_text']);
    
    if ($order_id > 0 && !empty($review_text)) {
        $reviewSql = "INSERT INTO general_reviews (user_id, order_id, {$reviews_text_column}) 
                      VALUES (:uid, :oid, :comment)
                      ON DUPLICATE KEY UPDATE {$reviews_text_column} = :comment_update";
        
        $pdo->prepare($reviewSql)->execute([
            ':uid'            => $user_id,
            ':oid'            => $order_id,
            ':comment'        => $review_text,
            ':comment_update' => $review_text
        ]);
        $message_status = "Review saved successfully inside general_reviews database!";
    } else {
        $message_status = "Please select an order reference and write a comment before saving.";
    }
}

// Fetch user profile information
$user_display_name = "Valued Customer";
try {
    $userStmt = $pdo->prepare("SELECT full_name FROM users WHERE id = :uid LIMIT 1");
    $userStmt->execute([':uid' => $user_id]);
    $userRow = $userStmt->fetch();
    if ($userRow && !empty($userRow['full_name'])) {
        $user_display_name = $userRow['full_name'];
    }
} catch (Exception $e) {
    $user_display_name = $_SESSION['username'] ?? "Valued Customer";
}

// Fetch user orders records
$stmt = $pdo->prepare("SELECT * FROM user_orders WHERE user_id = :uid ORDER BY id DESC");
$stmt->execute([':uid' => $user_id]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard Workspace | Athar Studio</title>
    <style>
        body { 
            background-color: #0b0813; 
            color: white; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            padding: 40px; 
            background-image: radial-gradient(circle at 50% 10%, rgba(168, 85, 247, 0.1) 0%, transparent 60%);
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(168, 85, 247, 0.2);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .welcome-msg h2 { 
            font-size: 28px; 
            font-weight: 800; 
            margin: 0;
            background: linear-gradient(to right, #fff, #c084fc); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
        }
        .welcome-msg p { color: #94a3b8; margin: 5px 0 0 0; font-size: 14px; }
        
        .home-nav-btn {
            background: rgba(255, 255, 255, 0.03);
            color: #cbd5e1;
            text-decoration: none;
            padding: 10px 22px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .home-nav-btn:hover {
            background: #a855f7;
            color: #fff;
            border-color: #a855f7;
            box-shadow: 0 0 15px rgba(168, 85, 247, 0.4);
            transform: translateY(-1px);
        }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: rgba(15,12,28,0.6); backdrop-filter: blur(8px); border-radius: 12px; overflow: hidden; border: 1px solid rgba(168, 85, 247, 0.1); margin-bottom: 40px; }
        th, td { padding: 16px; border-bottom: 1px solid rgba(255,255,255,0.04); text-align: center; font-size: 14px; vertical-align: middle; }
        th { background-color: rgba(168, 85, 247, 0.15); color: #c084fc; font-weight: 700; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; border-bottom: 1px solid rgba(168, 85, 247, 0.3); }
        tr:hover { background-color: rgba(255,255,255,0.02); }
        
        .payment-badge { padding: 6px 12px; border-radius: 6px; font-weight: bold; font-size: 11px; text-transform: uppercase; display: inline-block; letter-spacing: 0.5px; }
        .badge-card { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.4); }
        .badge-offline { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); }
        
        /* IMAGE GRID IN STABLE LAYOUT MATRIX */
        .thumb-matrix { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; max-width: 140px; margin: 0 auto; }
        .img-preview-thumb { width: 55px; height: 55px; object-fit: cover; border-radius: 6px; border: 1px solid rgba(168, 85, 247, 0.4); background: #120e24; display: block; }
        
        .review-section-card {
            max-width: 600px;
            margin: 40px 0;
            background: rgba(15, 12, 28, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(168, 85, 247, 0.2);
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.3);
        }
        .review-section-card h3 { margin-top: 0; margin-bottom: 15px; font-size: 18px; color: #c084fc; font-weight: 700; }
        .form-group { display: flex; flex-direction: column; gap: 8px; margin-bottom: 15px; text-align: left; }
        .form-group label { font-size: 13px; color: #94a3b8; font-weight: 600; }
        .form-select, .form-textarea {
            width: 100%;
            padding: 12px;
            background: #120e24;
            border: 1px solid rgba(168, 85, 247, 0.3);
            border-radius: 8px;
            color: white;
            font-size: 14px;
            box-sizing: border-box;
            font-family: inherit;
        }
        .form-select:focus, .form-textarea:focus { border-color: #a855f7; outline: none; }
        .form-textarea { resize: vertical; }
        .submit-review-btn {
            background: linear-gradient(to right, #a855f7, #3b82f6);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .submit-review-btn:hover { opacity: 0.95; }
        .status-alert {
            padding: 10px 14px;
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #4ade80;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 15px;
            text-align: left;
        }

        .no-orders-wrapper { text-align: center; color: #94a3b8; padding: 60px; font-size: 15px; border: 1px dashed rgba(255,255,255,0.08); border-radius: 12px; background: rgba(15,12,28,0.3); }
    </style>
</head>
<body>

    <div class="dashboard-header">
        <div class="welcome-msg">
            <h2>Welcome back, <?php echo htmlspecialchars($user_display_name); ?>!</h2>
            <p>Monitor layout metrics, track pipelines, and manage your service experience records.</p>
        </div>
        <a href="index.html" class="home-nav-btn">← Back to Store Home</a>
    </div>

    <?php if (!empty($message_status)): ?>
        <div class="status-alert"><?php echo htmlspecialchars($message_status); ?></div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="no-orders-wrapper">You haven't submitted any custom asset configurations to your operation rack yet.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Tracking Reference</th>
                    <th>Product Details</th>
                    <th>Design Asset Previews</th>
                    <th>Product Layout Specs</th>
                    <th>Saved Database Comment Note</th>
                    <th>Billing Channel</th>
                    <th>Amount Paid</th>
                    <th>Fulfillment Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong style="color: #3b82f6; font-family: monospace; font-size: 14px;"><?php echo htmlspecialchars($order['card_transaction_id']); ?></strong></td>
                        
                        <td><strong><?php echo htmlspecialchars($order['product_name']); ?></strong></td>
                        
                        <td>
                            <?php if (!empty($order['design_images'])): 
                                $image_list = explode(',', $order['design_images']); ?>
                                <div class="thumb-matrix">
                                    <?php foreach ($image_list as $img_path): 
                                        $clean_path = trim($img_path);
                                        if (!empty($clean_path)): ?>
                                            <img src="<?php echo htmlspecialchars($clean_path); ?>" class="img-preview-thumb" alt="Upload Preview">
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span style="color: #475569; font-style: italic; font-size: 12px;">No Files</span>
                            <?php endif; ?>
                        </td>

                        <td style="text-align: left; max-width: 180px; font-size: 13px; color: #cbd5e1; line-height: 1.4;">
                            <?php echo !empty($order['specifications']) ? nl2br(htmlspecialchars(trim($order['specifications']))) : '<span style="color:#475569; font-style:italic;">Standard specs.</span>'; ?>
                        </td>

                        <td style="text-align: left; max-width: 200px; font-size: 13px; color: #a7f3d0;">
                            <?php 
                            $reviewStmt = $pdo->prepare("SELECT {$reviews_text_column} FROM general_reviews WHERE user_id = :uid AND order_id = :oid LIMIT 1");
                            $reviewStmt->execute([':uid' => $user_id, ':oid' => $order['id']]);
                            $saved_comment = $reviewStmt->fetchColumn();
                            
                            if ($saved_comment) {
                                echo htmlspecialchars($saved_comment);
                            } else {
                                echo '<span style="color: #475569; font-style: italic;">No comment logged yet. Use form below.</span>';
                            }
                            ?>
                        </td>

                        <td>
                            <?php 
                            $raw_method = strtoupper(trim($order['cardholder_name']));
                            if ($raw_method === 'CARD' || strpos($raw_method, 'CARD') !== false): ?>
                                <span class="payment-badge badge-card">💳 CREDIT CARD</span>
                            <?php else: ?>
                                <span class="payment-badge badge-offline">💵 <?php echo htmlspecialchars($order['cardholder_name']); ?></span>
                            <?php endif; ?>
                        </td>

                        <td style="color:#22c55e; font-weight:800; font-size: 15px;">$<?php echo number_format($order['total_price'], 2); ?></td>
                        
                        <td><span style="color:#c084fc; font-weight:700; text-transform: uppercase; font-size:12px;"><?php echo htmlspecialchars($order['order_status']); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="review-section-card">
            <h3>Add or Update Order Review Notes</h3>
            <form action="dashboard.php" method="POST">
                <div class="form-group">
                    <label for="selected_order_id">Choose Order Tracking Reference</label>
                    <select name="selected_order_id" id="selected_order_id" class="form-select" required>
                        <option value="">-- Select an Order Reference --</option>
                        <?php foreach ($orders as $order): ?>
                            <option value="<?php echo $order['id']; ?>">
                                <?php echo htmlspecialchars($order['card_transaction_id']) . " (" . htmlspecialchars($order['product_name']) . ")"; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="general_review_text">Your Comment / Review Message</label>
                    <textarea name="general_review_text" id="general_review_text" class="form-textarea" rows="4" required placeholder="Type your experience details or small adjustment notes here... This will save directly into the general_reviews database."></textarea>
                </div>
                
                <div style="text-align: right;">
                    <button type="submit" name="submit_general_review" class="submit-review-btn">Save Comment Note</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

</body>
</html>