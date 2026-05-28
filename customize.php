<?php
// 1. Initialize secure session management
session_start();

// 2. Access Control Gatekeeper: If not logged in, redirect them to sign-in portal
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html?error=authentication_required");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Lab | Athar Studio</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #0b0813;
            color: #ffffff;
            min-height: 100vh;
            overflow-x: hidden;
            background-image: radial-gradient(circle at 10% 20%, rgba(168, 85, 247, 0.15) 0%, transparent 40%),
                              radial-gradient(circle at 90% 80%, rgba(59, 130, 246, 0.15) 0%, transparent 40%);
        }

        /* --- NAVIGATION HEADER ARCHITECTURE --- */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 8%;
            background: rgba(15, 12, 28, 0.75);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(168, 85, 247, 0.25);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo-container { display: flex; align-items: center; }
        .logo-img { height: 55px; width: auto; filter: drop-shadow(0 0 8px rgba(168, 85, 247, 0.5)); }
        .nav-container { display: flex; align-items: center; gap: 40px; }
        .nav-links { list-style: none; display: flex; gap: 35px; }
        
        .nav-links a {
            color: #cbd5e1;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        .nav-links a:hover, .nav-links a.active {
            color: #a855f7;
            text-shadow: 0 0 8px rgba(168, 85, 247, 0.6);
        }

        .cart-trigger {
            background: rgba(168, 85, 247, 0.1);
            border: 1px solid #a855f7;
            padding: 10px 18px;
            border-radius: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 0 12px rgba(168, 85, 247, 0.3);
            transition: all 0.3s ease;
        }

        .cart-icon-container { position: relative; width: 22px; height: 18px; display: flex; align-items: center; justify-content: center; }
        .cart-basket-mesh { width: 18px; height: 12px; border: 2px solid #ffffff; border-top: none; border-radius: 0 0 4px 3px; position: relative; top: -2px; left: 2px; }
        .cart-icon-wheel { position: absolute; bottom: 0; width: 4px; height: 4px; background: #ffffff; border-radius: 50%; }
        .wheel-left { left: 5px; } .wheel-right { right: 1px; }
        .cart-count { background: #ffffff; color: #0b0813; font-size: 11px; font-weight: 800; padding: 2px 7px; border-radius: 10px; }

        /* --- MAIN PRODUCTION ENGINE GRID --- */
        .catalog-container { max-width: 1300px; margin: 50px auto; padding: 0 4%; }
        .catalog-header { text-align: center; margin-bottom: 50px; }
        .catalog-header h1 {
            font-size: 42px; font-weight: 800;
            background: linear-gradient(to right, #ffffff, #cbd5e1);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 10px;
        }
        .catalog-header p { color: #a855f7; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 3px; }

        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 30px; }
        .product-card {
            background: rgba(15, 12, 28, 0.5); backdrop-filter: blur(8px); border: 1px solid rgba(168, 85, 247, 0.2);
            border-radius: 16px; padding: 30px; display: flex; flex-direction: column; transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.4);
        }
        .product-card.blue-accent { border-color: rgba(59, 130, 246, 0.2); }
        .product-card:hover { transform: translateY(-6px); background: rgba(20, 16, 38, 0.8); border-color: #a855f7; box-shadow: 0 0 25px rgba(168, 85, 247, 0.25); }
        .product-card.blue-accent:hover { border-color: #3b82f6; box-shadow: 0 0 25px rgba(59, 130, 246, 0.25); }

        .product-info h3 { font-size: 20px; font-weight: 700; margin-bottom: 6px; }
        .product-price-display { font-size: 16px; font-weight: 700; color: #a855f7; margin-bottom: 20px; display: block; }
        .product-card.blue-accent .product-price-display { color: #3b82f6; }

        /* Configuration Elements */
        .config-group { margin-bottom: 18px; }
        .config-label { font-size: 11px; text-transform: uppercase; color: #94a3b8; letter-spacing: 1px; font-weight: 700; margin-bottom: 8px; display: block; }
        .custom-select-wrapper { display: flex; flex-wrap: wrap; gap: 8px; }

        .option-pill-btn {
            flex: 1; min-width: 80px; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1);
            color: #cbd5e1; padding: 10px 8px; font-size: 12px; font-weight: 600; border-radius: 8px; cursor: pointer; transition: all 0.2s; text-align: center;
        }
        .option-pill-btn:hover { border-color: rgba(255,255,255,0.3); color: #fff; }
        .product-card.purple-accent .option-pill-btn.active { background: rgba(168, 85, 247, 0.15); border-color: #a855f7; color: #fff; box-shadow: 0 0 10px rgba(168, 85, 247, 0.3); }
        .product-card.blue-accent .option-pill-btn.active { background: rgba(59, 130, 246, 0.15); border-color: #3b82f6; color: #fff; box-shadow: 0 0 10px rgba(59, 130, 246, 0.3); }

        /* --- SWATCH PICKER MATRIX MATRIX STYLE SYSTEM --- */
        .color-picker-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(105px, 1fr)); gap: 8px; }
        .color-swatch-option {
            background: rgba(0, 0, 0, 0.25); border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px; padding: 8px 10px; display: flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.2s; user-select: none;
        }
        .color-swatch-option:hover { border-color: rgba(59, 130, 246, 0.4); background: rgba(59, 130, 246, 0.02); }
        .color-swatch-option.active { border-color: #3b82f6; background: rgba(59, 130, 246, 0.15); box-shadow: 0 0 10px rgba(59, 130, 246, 0.25); }
        .color-preview-circle { width: 14px; height: 14px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
        .color-name-text { font-size: 11px; color: #ffffff; font-weight: 500; }

        .blueprint-upload-field { position: relative; background: rgba(0, 0, 0, 0.2); border: 1px dashed rgba(255, 255, 255, 0.15); border-radius: 8px; padding: 14px; text-align: center; cursor: pointer; transition: all 0.2s; }
        .blueprint-upload-field:hover { border-color: #a855f7; background: rgba(168, 85, 247, 0.02); }
        .product-card.blue-accent .blueprint-upload-field:hover { border-color: #3b82f6; background: rgba(59, 130, 246, 0.02); }
        .blueprint-upload-field input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        .upload-status-txt { font-size: 12px; color: #64748b; font-weight: 500; word-break: break-word; }

        .product-footer { display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.05); }
        .tag-pill { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #a855f7; }
        .product-card.blue-accent .tag-pill { color: #3b82f6; }

        .add-cart-btn { background: transparent; color: #fff; border: 1px solid rgba(168, 85, 247, 0.6); padding: 10px 22px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; }
        .product-card.blue-accent .add-cart-btn { border-color: rgba(59, 130, 246, 0.6); }
        .product-card:hover .add-cart-btn { background: #a855f7; border-color: #a855f7; box-shadow: 0 0 12px rgba(168, 85, 247, 0.6); }
        .product-card.blue-accent:hover .add-cart-btn { background: #3b82f6; border-color: #3b82f6; box-shadow: 0 0 12px rgba(59, 130, 246, 0.6); }

        /* --- SIDEBAR DRAWER PANEL --- */
        .cart-sidebar {
            position: fixed; top: 0; right: -100%; width: 420px; max-width: 100%; height: 100vh;
            background: rgba(10, 7, 18, 0.96); backdrop-filter: blur(20px); border-left: 1px solid rgba(168, 85, 247, 0.3);
            box-shadow: -10px 0 40px rgba(0, 0, 0, 0.8); z-index: 2000; transition: right 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            padding: 30px; display: flex; flex-direction: column;
        }
        .cart-sidebar.open { right: 0; }
        .sidebar-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px); z-index: 1500; display: none; }
        .sidebar-overlay.active { display: block; }

        .cart-side-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid rgba(168, 85, 247, 0.2); padding-bottom: 15px; }
        .cart-side-header h2 { font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 12px; }
        .close-cart { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #94a3b8; font-size: 20px; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; justify-content: center; align-items: center; }
        .close-cart:hover { color: #fff; background: rgba(239, 68, 68, 0.3); border-color: #ef4444; }

        .cart-items-wrapper { flex-grow: 1; overflow-y: auto; margin-bottom: 20px; }
        .cart-item-card { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 15px; margin-bottom: 15px; display: flex; flex-direction: column; gap: 12px; }
        .cart-item-meta-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
        .cart-item-title { font-weight: 600; font-size: 14px; color: #ffffff; }
        .cart-item-spec-note { font-size: 11px; color: #94a3b8; margin-top: 2px; }
        .cart-item-price { font-size: 13px; color: #a855f7; font-weight: 600; white-space: nowrap; }

        .cart-preview-images-row { display: flex; flex-wrap: wrap; gap: 8px; margin: 8px 0; }
        .cart-preview-thumbnail { width: 55px; height: 55px; object-fit: cover; border-radius: 6px; border: 1px solid rgba(168, 85, 247, 0.4); background: #120e24; }
        .cart-item-qty-row { display: flex; justify-content: space-between; align-items: center; margin-top: 5px; }
        
        .cart-qty-inline-picker { display: flex; align-items: center; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(168, 85, 247, 0.4); border-radius: 6px; overflow: hidden; width: 100px; height: 30px; }
        .cart-qty-inline-btn { background: transparent; border: none; color: #a855f7; width: 30px; height: 100%; font-size: 14px; cursor: pointer; font-weight: 700; }
        .cart-qty-inline-btn:hover { background: rgba(168, 85, 247, 0.2); }
        .cart-qty-inline-display { background: transparent; border: none; color: #fff; text-align: center; font-size: 13px; font-weight: 700; width: 40px; }
        .remove-item-btn { background: transparent; border: none; color: #64748b; font-size: 12px; cursor: pointer; }
        .remove-item-btn:hover { color: #ef4444; }

        .cart-summary-block { background: rgba(15, 12, 28, 0.8); border: 1px solid rgba(168, 85, 247, 0.2); border-radius: 12px; padding: 20px; margin-bottom: 15px; }
        .summary-row { display: flex; justify-content: space-between; font-size: 13px; color: #94a3b8; margin-bottom: 8px; }
        .summary-row.total-line { border-top: 1px dashed rgba(255, 255, 255, 0.1); padding-top: 10px; margin-top: 10px; font-size: 16px; font-weight: 700; color: #fff; }
        
        .cart-checkout-btn { background: linear-gradient(to right, #a855f7, #3b82f6); color: #fff; border: none; width: 100%; padding: 15px; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; }
        .empty-cart-msg { color: #475569; text-align: center; margin-top: 80px; font-size: 14px; }
        footer { border-top: 1px solid rgba(168, 85, 247, 0.2); background: #06040a; padding: 40px 8%; text-align: center; margin-top: 100px; }
    </style>
</head>
<body>

    <div class="sidebar-overlay" id="sidebarDimmer" onclick="toggleCart(false)"></div>

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
            <div class="cart-trigger" onclick="toggleCart(true)">
                <div class="cart-icon-container">
                    <div class="cart-basket-mesh"></div>
                    <div class="cart-icon-wheel wheel-left"></div>
                    <div class="cart-icon-wheel wheel-right"></div>
                </div>
                <span class="cart-count" id="cartCountGlobal">0</span>
            </div>
        </div>
    </header>

    <div class="catalog-container">
        <div class="catalog-header">
            <p>Bespoke Production Engine</p>
            <h1>Bespoke Custom Lab</h1>
        </div>

        <div class="products-grid">
            
            <div class="product-card purple-accent" id="card-wood">
                <div class="product-info">
                    <h3>Wooden Engraved Plate</h3>
                    <span class="product-price-display" id="price-wood">$5.00</span>
                </div>
                <div class="config-group">
                    <span class="config-label">Dimension Specs</span>
                    <div class="custom-select-wrapper">
                        <button class="option-pill-btn active" onclick="updateItemPrice('wood', 5.00, this, 'A6')">A6 ($5)</button>
                        <button class="option-pill-btn" onclick="updateItemPrice('wood', 10.00, this, 'A5')">A5 ($10)</button>
                        <button class="option-pill-btn" onclick="updateItemPrice('wood', 15.00, this, 'A4')">A4 ($15)</button>
                    </div>
                </div>
                <div class="config-group">
                    <span class="config-label">Engraving Graphics Vector</span>
                    <div class="blueprint-upload-field">
                        <input type="file" accept="image/*" onchange="handleSingleFileUpload(this, 'wood')">
                        <span class="upload-status-txt" id="status-wood">Upload image illustration</span>
                    </div>
                </div>
                <div class="product-footer">
                    <span class="tag-pill">Woodwork</span>
                    <button class="add-cart-btn" onclick="addAssetToCart('wood', 'Wooden Engraved Plate')">Configure Base</button>
                </div>
            </div>

            <div class="product-card blue-accent" id="card-keychain">
                <div class="product-info">
                    <h3>Custom Acrylic Keychain</h3>
                    <span class="product-price-display" id="price-keychain">$2.00</span>
                </div>
                <div class="config-group">
                    <span class="config-label">Insert Media Graphics (Multiple Allowed)</span>
                    <div class="blueprint-upload-field">
                        <input type="file" accept="image/*" multiple onchange="handleMultipleFileUploads(this, 'keychain')">
                        <span class="upload-status-txt" id="status-keychain">Upload display artworks (Hold Ctrl to choose multiple)</span>
                    </div>
                </div>
                <div class="product-footer">
                    <span class="tag-pill">Accessories</span>
                    <button class="add-cart-btn" onclick="addAssetToCart('keychain', 'Custom Acrylic Keychain')">Configure Base</button>
                </div>
            </div>

            <div class="product-card purple-accent" id="card-pins">
                <div class="product-info">
                    <h3>Custom Lapel Pin</h3>
                    <span class="product-price-display" id="price-pins">$2.00</span>
                </div>
                <div class="config-group">
                    <span class="config-label">Pin Dimension Matrix</span>
                    <div class="custom-select-wrapper">
                        <button class="option-pill-btn active" style="cursor: default; flex: none; width: 100%;">5.5cm Pin ($2)</button>
                    </div>
                </div>
                <div class="config-group">
                    <span class="config-label">Pin Image Insertion</span>
                    <div class="blueprint-upload-field">
                        <input type="file" accept="image/*" onchange="handleSingleFileUpload(this, 'pins')">
                        <span class="upload-status-txt" id="status-pins">Upload face image graphic</span>
                    </div>
                </div>
                <div class="product-footer">
                    <span class="tag-pill">Badges</span>
                    <button class="add-cart-btn" onclick="addAssetToCart('pins', 'Custom Lapel Pin')">Configure Base</button>
                </div>
            </div>

            <div class="product-card blue-accent" id="card-flowers">
                <div class="product-info">
                    <h3>Custom Ribbon Flower Bouquet</h3>
                    <span class="product-price-display" id="price-flowers">$10.00</span>
                </div>
                <div class="config-group">
                    <span class="config-label">Bouquet Layout Scale</span>
                    <div class="custom-select-wrapper">
                        <button class="option-pill-btn active" onclick="updateItemPrice('flowers', 10.00, this, '10 Flowers')">10 Flowers ($10)</button>
                        <button class="option-pill-btn" onclick="updateItemPrice('flowers', 20.00, this, '20 Flowers')">20 Flowers ($20)</button>
                        <button class="option-pill-btn" onclick="updateItemPrice('flowers', 30.00, this, '30 Flowers')">30 Flowers ($30)</button>
                    </div>
                </div>
                <div class="config-group">
                    <span class="config-label">Ribbon Base Color Profiles (Select Multiple Allowed)</span>
                    
                    <div class="color-picker-grid">
                        <div class="color-swatch-option active" data-color="Red" onclick="toggleRibbonColor(this)">
                            <span class="color-preview-circle" style="background-color: #ef4444;"></span>
                            <span class="color-name-text">Red</span>
                        </div>
                        <div class="color-swatch-option" data-color="Green" onclick="toggleRibbonColor(this)">
                            <span class="color-preview-circle" style="background-color: #22c55e;"></span>
                            <span class="color-name-text">Green</span>
                        </div>
                        <div class="color-swatch-option" data-color="Light Pink" onclick="toggleRibbonColor(this)">
                            <span class="color-preview-circle" style="background-color: #fbcfe8;"></span>
                            <span class="color-name-text">Light Pink</span>
                        </div>
                        <div class="color-swatch-option" data-color="Dark Pink" onclick="toggleRibbonColor(this)">
                            <span class="color-preview-circle" style="background-color: #ec4899;"></span>
                            <span class="color-name-text">Dark Pink</span>
                        </div>
                        <div class="color-swatch-option" data-color="Light Blue" onclick="toggleRibbonColor(this)">
                            <span class="color-preview-circle" style="background-color: #93c5fd;"></span>
                            <span class="color-name-text">Light Blue</span>
                        </div>
                        <div class="color-swatch-option" data-color="Dark Blue" onclick="toggleRibbonColor(this)">
                            <span class="color-preview-circle" style="background-color: #1e3a8a;"></span>
                            <span class="color-name-text">Dark Blue</span>
                        </div>
                        <div class="color-swatch-option" data-color="Purple" onclick="toggleRibbonColor(this)">
                            <span class="color-preview-circle" style="background-color: #a855f7;"></span>
                            <span class="color-name-text">Purple</span>
                        </div>
                        <div class="color-swatch-option" data-color="Lavender" onclick="toggleRibbonColor(this)">
                            <span class="color-preview-circle" style="background-color: #e9d5ff;"></span>
                            <span class="color-name-text">Lavender</span>
                        </div>
                        <div class="color-swatch-option" data-color="Yellow" onclick="toggleRibbonColor(this)">
                            <span class="color-preview-circle" style="background-color: #eab308;"></span>
                            <span class="color-name-text">Yellow</span>
                        </div>
                        <div class="color-swatch-option" data-color="White" onclick="toggleRibbonColor(this)">
                            <span class="color-preview-circle" style="background-color: #ffffff; border: 1px solid rgba(255,255,255,0.3);"></span>
                            <span class="color-name-text">White</span>
                        </div>
                        <div class="color-swatch-option" data-color="Black" onclick="toggleRibbonColor(this)">
                            <span class="color-preview-circle" style="background-color: #000000; border: 1px solid rgba(255,255,255,0.1);"></span>
                            <span class="color-name-text">Black</span>
                        </div>
                        <div class="color-swatch-option" data-color="Off-white" onclick="toggleRibbonColor(this)">
                            <span class="color-preview-circle" style="background-color: #f7f7f2; border: 1px solid rgba(255,255,255,0.3);"></span>
                            <span class="color-name-text">Off-white</span>
                        </div>
                    </div>
                    <input type="hidden" id="selected_ribbon_colors" value="Red">
                </div>
                <div class="product-footer">
                    <span class="tag-pill">Luxury Craft</span>
                    <button class="add-cart-btn" onclick="addAssetToCart('flowers', 'Custom Ribbon Flower Bouquet')">Configure Base</button>
                </div>
            </div>

            <div class="product-card blue-accent" id="card-print">
                <div class="product-info">
                    <h3>Printed Illustrated Graphics</h3>
                    <span class="product-price-display" id="price-print">$3.00</span>
                </div>
                <div class="config-group">
                    <span class="config-label">Finishing Matrix Selection</span>
                    <div class="custom-select-wrapper">
                        <button class="option-pill-btn active" onclick="updateItemPrice('print', 3.00, this, 'A5 Laminated')">A5 Laminated ($3)</button>
                        <button class="option-pill-btn" onclick="updateItemPrice('print', 4.00, this, 'A4 Laminated')">A4 Laminated ($4)</button>
                        <button class="option-pill-btn" onclick="updateItemPrice('print', 5.00, this, 'A4 Framed')">A4 Framed ($5)</button>
                    </div>
                </div>
                <div class="config-group">
                    <span class="config-label">Art Blueprint Upload</span>
                    <div class="blueprint-upload-field">
                        <input type="file" accept="image/*" onchange="handleSingleFileUpload(this, 'print')">
                        <span class="upload-status-txt" id="status-print">Upload layout art source</span>
                    </div>
                </div>
                <div class="product-footer">
                    <span class="tag-pill">Print Studio</span>
                    <button class="add-cart-btn" onclick="addAssetToCart('print', 'Printed Illustrated Graphics')">Configure Base</button>
                </div>
            </div>

        </div>
    </div>

    <div class="cart-sidebar" id="cartSidebarPanel">
        <div class="cart-side-header">
            <h2>Production Rack</h2>
            <button class="close-cart" onclick="toggleCart(false)">×</button>
        </div>

        <div class="cart-items-wrapper" id="cartDrawerList">
            <div class="empty-cart-msg">Your configuration rack is empty.</div>
        </div>

        <div class="cart-summary-block">
            <div class="summary-row">
                <span>Active Quantities</span>
                <span id="summaryItemsCount">0 items</span>
            </div>
            <div class="summary-row">
                <span>Cargo Shipping</span>
                <span style="color:#22c55e; font-weight:600;">FREE STUDIO DELIVERY</span>
            </div>
            <div class="summary-row total-line">
                <span>Total Assets Summary</span>
                <span id="summaryTotalQty">$0.00</span>
            </div>
        </div>

        <form id="checkoutProcessingForm" action="checkout.php" method="POST" enctype="multipart/form-data" style="display:none;">
            <input type="text" name="product_type" id="formProductType">
            <textarea name="custom_specs" id="formCustomSpecs"></textarea>
            <input type="text" name="price" id="formPrice">
            <input type="text" name="quantity" id="formQty">
            <input type="file" name="design_image" id="formFileTransferBridge">
            <div id="multiFileInputsContainer"></div>
        </form>

        <button class="cart-checkout-btn" onclick="processCheckoutPipeline()">Proceed Checkout</button>
    </div>

    <footer>
        <p style="font-size: 12px; color: #64748b;">© 2026 ATHAR Studio. Beyond Your Best Idea. All rights reserved.</p>
    </footer>

    <script>
        let productConfigurations = {
            wood: { basePrice: 5.00, activeSpec: 'A6', uploadedFiles: [] },
            keychain: { basePrice: 2.00, activeSpec: 'Standard Keychain Base', uploadedFiles: [] },
            pins: { basePrice: 2.00, activeSpec: '5.5cm Pin', uploadedFiles: [] },
            flowers: { basePrice: 10.00, activeSpec: '10 Flowers', colorHex: 'RED', uploadedFiles: [] },
            print: { basePrice: 3.00, activeSpec: 'A5 Laminated', uploadedFiles: [] }
        };

        let currentActiveRackItem = null;

        function toggleCart(open) {
            document.getElementById('cartSidebarPanel').style.right = open ? '0' : '-100%';
            document.getElementById('sidebarDimmer').style.display = open ? 'block' : 'none';
        }

        function toggleRibbonColor(element) {
            element.classList.toggle('active');
            
            const cardGrid = element.parentElement;
            const activeSwatches = cardGrid.querySelectorAll('.color-swatch-option.active');
            
            let colorChoices = [];
            activeSwatches.forEach(swatch => {
                colorChoices.push(swatch.getAttribute('data-color'));
            });

            if (colorChoices.length === 0) {
                element.classList.add('active');
                colorChoices.push(element.getAttribute('data-color'));
            }

            const combinedHexLabel = colorChoices.join(' + ');
            document.getElementById('selected_ribbon_colors').value = combinedHexLabel;
            productConfigurations.flowers.colorHex = combinedHexLabel.toUpperCase();
        }

        function updateItemPrice(key, price, buttonElement, specLabel) {
            productConfigurations[key].basePrice = price;
            productConfigurations[key].activeSpec = specLabel;

            const priceDisplay = document.getElementById(`price-${key}`);
            if (priceDisplay) priceDisplay.innerText = `$${price.toFixed(2)}`;

            const pills = buttonElement.parentElement.querySelectorAll('.option-pill-btn');
            pills.forEach(pill => pill.classList.remove('active'));
            buttonElement.classList.add('active');
        }

        function handleSingleFileUpload(input, key) {
            const statusLabel = document.getElementById(`status-${key}`);
            if (input.files && input.files[0]) {
                productConfigurations[key].uploadedFiles = [input.files[0]];
                statusLabel.innerText = `Attached: ${input.files[0].name}`;
                statusLabel.style.color = "#22c55e";
            } else {
                productConfigurations[key].uploadedFiles = [];
                statusLabel.innerText = "Upload image illustration";
                statusLabel.style.color = "#64748b";
            }
        }

        function handleMultipleFileUploads(input, key) {
            const statusLabel = document.getElementById(`status-${key}`);
            if (input.files && input.files.length > 0) {
                productConfigurations[key].uploadedFiles = Array.from(input.files);
                statusLabel.innerText = `${input.files.length} design files loaded successfully.`;
                statusLabel.style.color = "#3b82f6";
            } else {
                productConfigurations[key].uploadedFiles = [];
                statusLabel.innerText = "Upload display artworks (Hold Ctrl to choose multiple)";
                statusLabel.style.color = "#64748b";
            }
        }

        function addAssetToCart(key, fallbackName) {
            const configuredItem = productConfigurations[key];
            let specSummaryString = `Config Rule: ${configuredItem.activeSpec}`;
            
            if (key === 'flowers') {
                specSummaryString += ` | Shade Profile: ${configuredItem.colorHex}`;
            }

            let basePreviewsHTML = '';
            if (configuredItem.uploadedFiles && configuredItem.uploadedFiles.length > 0) {
                configuredItem.uploadedFiles.forEach(file => {
                    const inlineBlobUrl = URL.createObjectURL(file);
                    basePreviewsHTML += `<img src="${inlineBlobUrl}" class="cart-preview-thumbnail" alt="Preview Mini">`;
                });
            }

            currentActiveRackItem = {
                key: key,
                name: fallbackName,
                price: configuredItem.basePrice,
                specs: specSummaryString,
                quantity: 1,
                previewsHTML: basePreviewsHTML
            };

            renderRackUI();
            toggleCart(true);
        }

        function changeQuantity(changeValue) {
            if (!currentActiveRackItem) return;
            
            let targetQuantity = currentActiveRackItem.quantity + changeValue;
            if (targetQuantity < 1) return;
            
            currentActiveRackItem.quantity = targetQuantity;
            renderRackUI();
        }

        function renderRackUI() {
            if (!currentActiveRackItem) {
                clearProductionRack();
                return;
            }

            const collectiveTotalCost = currentActiveRackItem.price * currentActiveRackItem.quantity;

            document.getElementById('cartCountGlobal').innerText = currentActiveRackItem.quantity;
            document.getElementById('summaryItemsCount').innerText = `${currentActiveRackItem.quantity} items`;
            document.getElementById('summaryTotalQty').innerText = `$${collectiveTotalCost.toFixed(2)}`;

            document.getElementById('cartDrawerList').innerHTML = `
                <div class="cart-item-card">
                    <div class="cart-item-meta-header">
                        <div>
                            <div class="cart-item-title">${currentActiveRackItem.name}</div>
                            <div class="cart-item-spec-note">${currentActiveRackItem.specs}</div>
                        </div>
                        <div class="cart-item-price">$${(currentActiveRackItem.price * currentActiveRackItem.quantity).toFixed(2)}</div>
                    </div>
                    
                    ${currentActiveRackItem.previewsHTML ? `<div class="cart-preview-images-row">${currentActiveRackItem.previewsHTML}</div>` : ''}

                    <div class="cart-item-qty-row">
                        <div class="cart-qty-inline-picker">
                            <button class="cart-qty-inline-btn" onclick="changeQuantity(-1)">-</button>
                            <input type="text" class="cart-qty-inline-display" readonly value="${currentActiveRackItem.quantity}">
                            <button class="cart-qty-inline-btn" onclick="changeQuantity(1)">+</button>
                        </div>
                        <button class="remove-item-btn" onclick="clearProductionRack()">Remove Design</button>
                    </div>
                </div>
            `;
        }

        function clearProductionRack() {
            currentActiveRackItem = null;
            document.getElementById('cartCountGlobal').innerText = "0";
            document.getElementById('summaryItemsCount').innerText = "0 items";
            document.getElementById('summaryTotalQty').innerText = "$0.00";
            document.getElementById('cartDrawerList').innerHTML = `<div class="empty-cart-msg">Your configuration rack is empty.</div>`;
        }

        function processCheckoutPipeline() {
            if (!currentActiveRackItem) {
                alert('Your custom engineering asset configuration rack is empty!');
                return;
            }

            const hiddenForm = document.getElementById('checkoutProcessingForm');
            const fileBridge = document.getElementById('formFileTransferBridge');
            const fileContainer = document.getElementById('multiFileInputsContainer');

            // CRITICAL SYSTEM RESET
            fileContainer.innerHTML = '';
            fileBridge.value = '';
            hiddenForm.reset();

            const activeStorageObj = productConfigurations[currentActiveRackItem.key];

            document.getElementById('formProductType').value = currentActiveRackItem.name;
            document.getElementById('formCustomSpecs').value = currentActiveRackItem.specs;
            document.getElementById('formPrice').value = currentActiveRackItem.price.toFixed(2);
            document.getElementById('formQty').value = currentActiveRackItem.quantity;

            if (activeStorageObj.uploadedFiles && activeStorageObj.uploadedFiles.length > 0) {
                if (currentActiveRackItem.key === 'keychain') {
                    const multiBridge = new DataTransfer();
                    activeStorageObj.uploadedFiles.forEach(file => {
                        multiBridge.items.add(file);
                    });
                    
                    let fileInputNode = document.createElement('input');
                    fileInputNode.type = 'file';
                    fileInputNode.name = 'design_images[]';
                    fileInputNode.multiple = true;
                    fileInputNode.files = multiBridge.files;
                    fileContainer.appendChild(fileInputNode);
                } else {
                    const singleBridge = new DataTransfer();
                    singleBridge.items.add(activeStorageObj.uploadedFiles[0]);
                    fileBridge.files = singleBridge.files;
                }
            }

            hiddenForm.submit();
        }
    </script>
</body>
</html>