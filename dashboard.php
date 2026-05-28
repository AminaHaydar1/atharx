<?php
// dashboard.php
session_start();

// Simulated tracking session verification loop
if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = "Creative Artisan"; 
}

// Handle dynamic workspace sign-out routing logic
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: index.html");
    exit();
}

// 1. Base array configuration to hold historical sample orders
$ordersHistory = [
    [
        'order_ref' => 'ATH-8C3D2F1E',
        'date' => '2026-05-24',
        'total_price' => 44.00,
        'payment_method' => 'cod',
        'status' => 'In Queue',
        'items' => [
            [
                'name' => 'Custom Wooden Engraving Block',
                'price' => 40.00,
                'specs' => 'Size: A5 | Material: Premium Walnut Wood',
                'quantity' => 1,
                'image_path' => 'image/blueprint-sample.jpg' 
            ]
        ],
        'review_comment' => "Please ensure the font sizing on line 2 matches the reference vector alignment carefully."
    ]
];

// 2. DETAILED ORDER CAPTURE: Processes data sent from checkout.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['is_new_order_submission'])) {
    
    // Clean, unescape, and extract the payload securely
    $rawPayload = isset($_POST['raw_cart_payload']) ? $_POST['raw_cart_payload'] : '';
    
    if (empty($rawPayload) && isset($_POST['custom_specs'])) {
        $rawPayload = $_POST['custom_specs'];
    }

    $parsedItems = [];
    if (!empty($rawPayload)) {
        // Unescape standard HTML Special Characters out before translating JSON parameters 
        $sanitizedJson = htmlspecialchars_decode($rawPayload, ENT_QUOTES);
        $parsedItems = json_decode($sanitizedJson, true);
    }
    
    if (!empty($parsedItems)) {
        $newItemsFormatted = [];
        foreach ($parsedItems as $item) {
            
            // Extract image path cleanly across possible variant names ('image_path', 'image', or 'img')
            $extractedImagePath = '';
            if (!empty($item['image_path'])) {
                $extractedImagePath = $item['image_path'];
            } elseif (!empty($item['image'])) {
                $extractedImagePath = $item['image'];
            } elseif (!empty($item['img'])) {
                $extractedImagePath = $item['img'];
            }

            $newItemsFormatted[] = [
                'name' => $item['name'] ?? 'Custom Tailored Blueprint Asset',
                'price' => floatval($item['price'] ?? 0),
                'specs' => $item['specs'] ?? 'Standard Specifications Parameter Vector',
                'quantity' => intval($item['quantity'] ?? 1),
                'image_path' => trim($extractedImagePath) 
            ];
        }
        
        // Prepend the order structure straight to the rendering profile stack
        array_unshift($ordersHistory, [
            'order_ref' => 'ATH-' . strtoupper(substr(md5(time()), 0, 8)),
            'date' => date('Y-m-d'),
            'total_price' => floatval($_POST['order_total'] ?? 0),
            'payment_method' => $_POST['selected_payment_method'] ?? 'cod',
            'status' => 'Processing Node',
            'items' => $newItemsFormatted,
            'review_comment' => ''
        ]);
    }
}

// Simple adjustment comments handler logic loop
$feedbackSuccessNotice = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_update_review'])) {
    $targetRef = $_POST['target_order_ref'];
    $newComment = $_POST['user_review_comment'];
    $feedbackSuccessNotice = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artisan Workspace Dashboard | Athar Studio</title>
    <style>
        :root {
            --text-main: #f1f5f9;      
            --text-muted: #94a3b8;     
            --bg-base: #0f172a;        
            --bg-card: #1e293b;        
            --bg-input: #334155;       
            --border: #334155;         
            --purple-accent: #7c3aed;  
            --success: #10b981;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-base);
            color: var(--text-main);
            margin: 0;
            padding: 0;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 5%;
            background-color: var(--bg-card);
            border-bottom: 1px solid var(--border);
        }

        .logo-container .logo-img {
            height: 50px;
            object-fit: contain;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 25px;
            margin: 0;
            padding: 0;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-main);
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .nav-links a:hover, .nav-links a.active {
            color: var(--purple-accent);
        }

        .logout-btn {
            background: #ef4444;
            color: #fff !important;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.88rem;
            font-weight: 600;
        }

        .workspace-container {
            padding: 40px 5%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .welcome-hero {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            background: linear-gradient(135deg, var(--bg-card) 0%, #1e1b4b 100%);
            padding: 30px;
            border-radius: 16px;
            border: 1px solid var(--border);
        }

        .welcome-hero h1 {
            margin: 0 0 5px 0;
            font-size: 2rem;
        }

        .order-history-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .order-meta-summary-bar {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
        }

        .meta-segment-node .label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .meta-segment-node .value {
            font-weight: 700;
        }

        .payment-method-badge {
            background: var(--bg-input);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .item-row-box {
            display: flex;
            gap: 20px;
            align-items: center;
            background: var(--bg-base);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid var(--border);
        }

        /* High-Contrast Responsive Dynamic Thumbnail Image Component */
        .thumbnail-wrapper {
            width: 80px;
            height: 80px;
            flex-shrink: 0;
        }

        .item-thumbnail-preview {
            width: 100%;
            height: 100%;
            border-radius: 8px;
            object-fit: cover;
            background: var(--bg-input);
            border: 1px solid var(--border);
            display: block;
        }

        .item-details-block {
            flex: 1;
            min-width: 0;
        }

        .item-name-heading {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .item-specs-subtext {
            font-size: 0.88rem;
            color: var(--text-muted);
        }

        .item-pricing-matrix-node {
            text-align: right;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .modification-review-box {
            margin-top: 20px;
            background: #1e1b4b;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #312e81;
        }

        .review-textarea {
            width: 100%;
            background: var(--bg-base);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px;
            color: var(--text-main);
            font-family: inherit;
            resize: vertical;
            box-sizing: border-box;
            margin-bottom: 12px;
        }

        .save-feedback-btn {
            background: var(--purple-accent);
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
        }

        .alert-toast-success {
            background: var(--success);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        footer {
            text-align: center;
            padding: 40px 0;
            color: var(--text-muted);
            font-size: 0.9rem;
            border-top: 1px solid var(--border);
            margin-top: 60px;
        }
    </style>
</head>
<body>

<header>
    <div class="logo-container">
        <img src="image/athar logo-09.png" alt="Athar Logo" class="logo-img">
    </div>
    <div class="nav-container">
        <ul class="nav-links">
            <li><a href="index.html">Home</a></li>
            <li><a href="products.html">Products</a></li>
            <li><a href="customize.php">Custom Lab</a></li>
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="dashboard.php?action=logout" class="logout-btn">Log Out</a></li>
        </ul>
    </div>
</header>

<div class="workspace-container">
    
    <div class="welcome-hero">
        <div>
            <h1>Welcome Back, <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
            <p>Monitor historical configuration telemetry vectors and order modifications from this console.</p>
        </div>
        <div style="font-size: 2.5rem;">🛠️</div>
    </div>

    <?php if ($feedbackSuccessNotice): ?>
        <div class="alert-toast-success">
            ✓ Production alteration requests log successfully updated for engineering validation checks.
        </div>
    <?php endif; ?>

    <div style="font-size: 1.4rem; font-weight: 700; margin-bottom: 20px;">
        <span>Active & Historic Queue Specifications</span>
    </div>

    <?php foreach ($ordersHistory as $order): ?>
        <div class="order-history-card">
            
            <div class="order-meta-summary-bar">
                <div class="meta-segment-node">
                    <div class="label">Reference Hash</div>
                    <div class="value" style="font-family: monospace; color: var(--purple-accent);"><?= $order['order_ref'] ?></div>
                </div>
                <div class="meta-segment-node">
                    <div class="label">Placement Date</div>
                    <div class="value"><?= $order['date'] ?></div>
                </div>
                <div class="meta-segment-node">
                    <div class="label">Settle Channel</div>
                    <div class="value">
                        <span class="payment-method-badge"><?= $order['payment_method'] === 'cod' ? 'Cash (COD)' : ($order['payment_method'] === 'wish' ? 'Wish Money' : 'Card Node') ?></span>
                    </div>
                </div>
                <div class="meta-segment-node">
                    <div class="label">Queue Status</div>
                    <div class="value" style="color: #f59e0b;"><?= $order['status'] ?></div>
                </div>
                <div class="meta-segment-node" style="text-align: right;">
                    <div class="label">Total Ledger Cost</div>
                    <div class="value" style="color: var(--text-main); font-size: 1.15rem;">$<?= number_format($order['total_price'], 2) ?></div>
                </div>
            </div>

            <div class="order-items-substack">
                <?php foreach ($order['items'] as $item): ?>
                    <div class="item-row-box">
                        
                        <?php if (!empty($item['image_path']) && strlen(trim($item['image_path'])) > 3): ?>
                            <div class="thumbnail-wrapper">
                                <img src="<?= $item['image_path'] ?>" 
                                     class="item-thumbnail-preview" 
                                     alt="User Uploaded Specification Vector"
                                     onerror="this.parentNode.style.display='none';">
                            </div>
                        <?php endif; ?>

                        <div class="item-details-block">
                            <div class="item-name-heading"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="item-specs-subtext"><?= htmlspecialchars($item['specs']) ?></div>
                            <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 2px;">Quantity: <?= intval($item['quantity']) ?></div>
                        </div>

                        <div class="item-pricing-matrix-node">
                            $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="modification-review-box">
                <div style="font-size: 0.95rem; font-weight: 700; margin-bottom: 10px; color: #c084fc;">
                    <span>📝 Request Production Alterations / Review Feedback</span>
                </div>
                <p style="margin: 0 0 12px 0; font-size: 0.85rem; color: var(--text-muted); line-height: 1.4;">
                    Need changes made to structural scaling, layout dimensions, or custom design properties? Write your adjustments below for our warehouse review engineers.
                </p>
                
                <form action="dashboard.php" method="POST">
                    <input type="hidden" name="action_update_review" value="1">
                    <input type="hidden" name="target_order_ref" value="<?= $order['order_ref'] ?>">
                    
                    <textarea 
                        name="user_review_comment" 
                        class="review-textarea" 
                        rows="3" 
                        placeholder="Specify any blueprint or design parameter variations required..."
                    ><?= htmlspecialchars($order['review_comment'] ?? '') ?></textarea>
                    
                    <button type="submit" class="save-feedback-btn">Log Adjustment Requests</button>
                </form>
            </div>

        </div>
    <?php endforeach; ?>

</div>

<footer>
    <p>&copy; <?= date('Y') ?> Athar Studio. All Rights Reserved.</p>
</footer>

</body>
</html>