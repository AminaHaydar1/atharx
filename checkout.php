<?php
// checkout.php

// 1. Capture and parse the incoming cart payload securely
$customSpecsRaw = isset($_POST['custom_specs']) ? $_POST['custom_specs'] : '';
$cartItems = [];

if (!empty($customSpecsRaw)) {
    $cartItems = json_decode($customSpecsRaw, true);
}

// 2. Base Configuration Constants
$deliveryFee = 4.00; // Standard studio cargo delivery fee rate
$runningSubtotal = 0.00;

// Admin Payment references configuration details
$adminCardName    = "ATHAR STUDIO ENTERPRISE";
$adminCardNumber  = "4000 1234 5678 9010";
$adminCardExpiry  = "12/28";
$adminWishNumber  = "+961 70 123 456"; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Desk | Athar Studio</title>
    <style>
        /* Exact Synchronization with Custom Lab Dark/Deep Color Palette Tokens */
        :root {
            --text-main: #f1f5f9;      /* Light typography text */
            --text-muted: #94a3b8;     /* Soft secondary text */
            --bg-base: #0f172a;        /* Deep canvas background */
            --bg-card: #1e293b;        /* Dark panel container card background */
            --bg-input: #334155;       /* Elevated accent dark fields */
            --border: #334155;         /* Muted layout border lines */
            --purple-accent: #7c3aed;  /* Core signature accent branding color */
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-base);
            color: var(--text-main);
            margin: 0;
            padding: 0;
        }

        /* Header Style Synchronization */
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

        .nav-container {
            display: flex;
            align-items: center;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 25px;
            margin: 0;
            padding: 0;
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

        /* Catalog Layout Headers Synchronization */
        .catalog-container {
            padding: 40px 5%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .catalog-header {
            margin-bottom: 40px;
        }

        .catalog-header p {
            color: var(--purple-accent);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 2px;
            font-weight: 700;
            margin: 0 0 5px 0;
        }

        .catalog-header h1 {
            font-size: 2.5rem;
            margin: 0;
            color: var(--text-main);
        }

        /* Two Column Splitting Dashboard Grid */
        .checkout-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 30px;
            align-items: start;
        }

        @media (max-width: 992px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Form Card Element Blocks Styling */
        .checkout-card-block {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.2);
        }

        .block-title {
            font-size: 1.35rem;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 25px;
            color: var(--text-main);
            border-bottom: 1px solid var(--border);
            padding-bottom: 15px;
        }

        /* Cart Structural Rows (Matches Custom Lab Style Accents) */
        .cart-checkout-item {
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            background: var(--bg-base);
            border-left: 5px solid var(--purple-accent);
        }

        .item-meta-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
        }

        .item-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 4px;
        }

        .item-spec-note {
            font-size: 0.88rem;
            color: var(--text-muted);
        }

        .item-reference-tag {
            font-size: 0.78rem;
            color: var(--text-muted);
            font-family: monospace;
            margin-top: 4px;
        }

        .item-price-display {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
            text-align: right;
        }

        /* Custom Staged Image Row Thumbnail Module */
        .cart-preview-images-row {
            display: flex;
            gap: 8px;
            margin-top: 15px;
            padding-top: 12px;
            border-top: 1px dashed var(--border);
        }

        .cart-preview-thumbnail {
            width: 65px;
            height: 65px;
            border-radius: 6px;
            object-fit: cover;
            border: 1px solid var(--border);
            background: var(--bg-input);
        }

        /* Calculated Ledger Summary Section Block */
        .summary-ledger-block {
            margin-top: 25px;
            background: var(--bg-base);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--border);
        }

        .ledger-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 0.95rem;
            color: var(--text-main);
        }

        .ledger-row.total-line {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--text-main);
            border-top: 2px dashed var(--border);
            padding-top: 15px;
            margin-top: 12px;
            margin-bottom: 0;
        }

        /* Selection Options Configuration Pills Buttons & Dynamic Payment States */
        .option-pill-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 25px;
        }

        .option-pill-btn {
            background: var(--bg-base);
            border: 1px solid var(--border);
            padding: 12px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s ease;
            color: var(--text-main);
        }

        /* Uniform Custom Lab Purple Accent States across all controls */
        .option-pill-btn:hover { 
            border-color: var(--purple-accent); 
            background: var(--bg-input);
        }
        
        .option-pill-btn.active { 
            background: var(--purple-accent); 
            color: #fff; 
            border-color: var(--purple-accent); 
        }

        /* Dynamic Payment Method View Panels */
        .payment-info-panel {
            display: none;
            background: var(--bg-base);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .payment-info-panel.visible {
            display: block;
        }

        .blueprint-data-card {
            background: var(--bg-card);
            padding: 15px;
            border-radius: 0 8px 8px 0;
            margin-top: 10px;
            border-top: 1px solid var(--border);
            border-right: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            border-left: 4px solid var(--purple-accent);
        }

        .data-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .data-value {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-main);
            margin-bottom: 10px;
            font-family: monospace;
        }
        
        .data-value:last-child {
            margin-bottom: 0;
        }

        /* Action Footer Order Button Trigger */
        .authorize-queue-btn {
            width: 100%;
            background: var(--purple-accent);
            color: white;
            border: none;
            padding: 16px;
            font-size: 1.1rem;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            transition: opacity 0.2s ease;
        }

        .authorize-queue-btn:hover {
            opacity: 0.9;
        }

        .empty-basket-notice {
            text-align: center;
            color: var(--text-muted);
            padding: 40px 20px;
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
            <li><a href="customize.php" class="active">Custom Lab</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="contact.html">Contact Us</a></li>
        </ul>
    </div>
</header>

<div class="catalog-container">
    <div class="catalog-header">
        <p>Bespoke Production Engine</p>
        <h1>Order Checkout Desk</h1>
    </div>

    <div class="checkout-grid">
        
        <div class="checkout-card-block">
            <div class="block-title">Production Rack Summary</div>
            
            <?php if (empty($cartItems)): ?>
                <div class="empty-basket-notice">
                    <p>Your configuration rack is empty. No custom items detected.</p>
                    <a href="customize.php" style="color: var(--purple-accent); text-decoration: none; font-weight: 600;">← Return to Custom Lab</a>
                </div>
            <?php else: ?>
                <div class="cart-items-stack">
                    <?php foreach ($cartItems as $index => $item): 
                        $itemRowSubtotal = floatval($item['price']) * intval($item['quantity']);
                        $runningSubtotal += $itemRowSubtotal;
                        
                        // Generate dynamic tracking reference hash
                        $referenceHash = "ATH-" . strtoupper(substr(md5($item['name'] . $index), 0, 8));
                    ?>
                        <div class="cart-checkout-item">
                            <div class="item-meta-header">
                                <div>
                                    <div class="item-title"><?= htmlspecialchars($item['name']) ?></div>
                                    <div class="item-spec-note"><?= htmlspecialchars($item['specs']) ?></div>
                                    <div class="item-reference-tag">Ref Code: <?= $referenceHash ?></div>
                                    <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">Quantity Vector: <?= intval($item['quantity']) ?></div>
                                </div>
                                <div class="item-price-display">
                                    <div>$<?= number_format($itemRowSubtotal, 2) ?></div>
                                    <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal;">$<?= number_format($item['price'], 2) ?> / unit</span>
                                </div>
                            </div>

                            <?php if (!empty($item['image_path'])): ?>
                                <div class="cart-preview-images-row">
                                    <img src="<?= htmlspecialchars($item['image_path']) ?>" class="cart-preview-thumbnail" alt="Uploaded blueprint design vector">
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-ledger-block">
                    <div class="ledger-row">
                        <span>Active Configurations Base Sum</span>
                        <span>$<?= number_format($runningSubtotal, 2) ?></span>
                    </div>
                    <div class="ledger-row">
                        <span>Studio Logistic Cargo Delivery</span>
                        <span>$<?= number_format($deliveryFee, 2) ?></span>
                    </div>
                    <div class="ledger-row total-line">
                        <span>Total Assets Balance Summary</span>
                        <span>$<?= number_format($runningSubtotal + $deliveryFee, 2) ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="checkout-card-block">
            <div class="block-title">Secure Routing Settlement</div>
            
            <form action="dashboard.php" method="POST" id="checkoutSettlementForm">
                <input type="hidden" name="order_subtotal" value="<?= $runningSubtotal ?>">
                <input type="hidden" name="delivery_fee" value="<?= $deliveryFee ?>">
                <input type="hidden" name="order_total" value="<?= $runningSubtotal + $deliveryFee ?>">
                <input type="hidden" name="raw_cart_payload" value="<?= htmlspecialchars($customSpecsRaw) ?>">
                <input type="hidden" name="selected_payment_method" id="selectedPaymentMethodBridge" value="cod">
                <input type="hidden" name="is_new_order_submission" value="1">

                <div style="font-size: 0.9rem; font-weight: 700; color: var(--text-main); margin-bottom: 12px;">Select Payment Profile Channel</div>
                
                <div class="option-pill-grid">
                    <button type="button" class="option-pill-btn" id="tab-card" onclick="togglePaymentChannel('card')">Online Card</button>
                    <button type="button" class="option-pill-btn" id="tab-wish" onclick="togglePaymentChannel('wish')">Wish Money</button>
                    <button type="button" class="option-pill-btn active" id="tab-cod" onclick="togglePaymentChannel('cod')">Cash (COD)</button>
                </div>

                <div class="payment-info-panel" id="panel-card">
                    <p style="margin-top:0; font-size:0.88rem; color: var(--text-muted); line-height:1.4;">Transfer the ledger summary amount to the administrator's processing master account bank routing network shown below:</p>
                    <div class="blueprint-data-card">
                        <div class="data-label">Admin Corporate Registrant</div>
                        <div class="data-value"><?= $adminCardName ?></div>
                        <div class="data-label">Card Matrix Account Number</div>
                        <div class="data-value" style="letter-spacing: 0.5px;"><?= $adminCardNumber ?></div>
                        <div class="data-label">Valid Expiration Period</div>
                        <div class="data-value"><?= $adminCardExpiry ?></div>
                    </div>
                </div>

                <div class="payment-info-panel" id="panel-wish">
                    <p style="margin-top:0; font-size:0.88rem; color: var(--text-muted); line-height:1.4;">Send the verified ledger sum directly to the following registry phone index endpoint:</p>
                    <div class="blueprint-data-card">
                        <div class="data-label">Wish Transfer Identity Target</div>
                        <div class="data-value" style="color: var(--purple-accent); font-size: 1.2rem;"><?= $adminWishNumber ?></div>
                        <div class="data-label">Account Destination Desk</div>
                        <div class="data-value">Athar Studio Central Processing Node</div>
                    </div>
                </div>

                <div class="payment-info-panel visible" id="panel-cod">
                    <div style="display:flex; align-items:center; gap:8px; color: var(--purple-accent); font-weight:700; font-size:0.95rem;">
                        <span style="font-size: 1.1rem;">✓</span>
                        <span>Cash On Delivery Clearance Enabled</span>
                    </div>
                    <p style="margin: 8px 0 0 0; font-size:0.88rem; color: var(--text-muted); line-height:1.4;">Settle balance parameters in full directly with logistical transit courier field operators upon physically accepting delivery verification protocols.</p>
                </div>

                <hr style="border:none; border-top:1px solid var(--border); margin:25px 0;">

                <button type="submit" class="authorize-queue-btn" <?php if(empty($cartItems)) echo 'disabled style="opacity:0.4; cursor:not-allowed;"'; ?>>
                    Authorize Production Queue
                </button>
            </form>
        </div>

    </div>
</div>

<footer>
    <p>&copy; <?= date('Y') ?> Athar Studio. All Rights Reserved.</p>
</footer>

<script>
    // Tab Controller System Routing Engine
    function togglePaymentChannel(selectedKey) {
        // Remove active state classes configurations from tabs matrix
        document.querySelectorAll('.option-pill-btn').forEach(btn => btn.classList.remove('active'));
        
        // Collapse alternative hidden workflow layout views panels
        document.querySelectorAll('.payment-info-panel').forEach(panel => panel.classList.remove('visible'));

        // Assign active highlights states layout structures matching target keys
        document.getElementById(`tab-${selectedKey}`).classList.add('active');
        document.getElementById(`panel-${selectedKey}`).classList.add('visible');

        // Forward the key variable string payload down through structural forms bridges inputs
        document.getElementById('selectedPaymentMethodBridge').value = selectedKey;
    }
</script>

</body>
</html>