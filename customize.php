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
        * {margin: 0;
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
        padding: 8px 16px;
        border-radius: 20px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 0 12px rgba(168, 85, 247, 0.3);
        transition: all 0.3s ease;
        user-select: none;
    }
    .cart-trigger:hover {
        background: rgba(168, 85, 247, 0.2);
        box-shadow: 0 0 18px rgba(168, 85, 247, 0.5);
    }

    /* --- GEOMETRIC CSS-DRAWN SHOPPING CART ARTWORK --- */
    .cart-icon-container {
        position: relative;
        width: 22px;
        height: 20px;
        display: inline-block;
    }
    
    .cart-basket-mesh {
        position: absolute;
        top: 2px;
        left: 0;
        width: 18px;
        height: 11px;
        border: 2px solid #cbd5e1;
        border-top: none;
        border-radius: 0 0 4px 4px;
    }
    /* Handlebar extension */
    .cart-basket-mesh::before {
        content: '';
        position: absolute;
        top: -4px;
        right: -4px;
        width: 5px;
        height: 2px;
        background-color: #cbd5e1;
        border-radius: 1px;
    }
    /* Slanted cart nose frame line */
    .cart-basket-mesh::after {
        content: '';
        position: absolute;
        top: -3px;
        left: -2px;
        width: 2px;
        height: 4px;
        background-color: #cbd5e1;
    }

    .cart-icon-wheel {
        position: absolute;
        bottom: 2px;
        width: 4px;
        height: 4px;
        background-color: #cbd5e1;
        border-radius: 50%;
    }
    .wheel-left { left: 2px; }
    .wheel-right { left: 12px; }

    /* Color shifts matching parent trigger state */
    .cart-trigger:hover .cart-basket-mesh,
    .cart-trigger:hover .cart-basket-mesh::before,
    .cart-trigger:hover .cart-basket-mesh::after,
    .cart-trigger:hover .cart-icon-wheel {
        background-color: #a855f7;
        border-color: #a855f7;
    }
    .cart-trigger:hover .cart-basket-mesh {
        border-top: none;
    }

    .cart-count { background: #ffffff; color: #0b0813; font-size: 11px; font-weight: 800; padding: 2px 7px; border-radius: 10px; transition: transform 0.2s ease; }
    .cart-trigger:hover .cart-count { transform: scale(1.05); }

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

    /* --- NEW PRODUCT ACCENT MUTATIONS --- */
    .product-card.metal-accent  { border-color: rgba(148, 163, 184, 0.2); }
    .product-card.office-accent { border-color: rgba(234, 179, 8, 0.2); }
    .product-card.decor-accent  { border-color: rgba(236, 72, 153, 0.2); }
    .product-card.apparel-accent{ border-color: rgba(249, 115, 22, 0.2); }
    .product-card.tech-accent   { border-color: rgba(16, 185, 129, 0.2); }
    .product-card.print-accent  { border-color: rgba(6, 182, 212, 0.2); }
    .product-card.wood-accent   { border-color: rgba(139, 92, 26, 0.2); }
    .product-card.gift-accent   { border-color: rgba(239, 68, 68, 0.2); }
    .product-card.craft-accent  { border-color: rgba(139, 92, 246, 0.2); }

    .product-card.metal-accent:hover   { border-color: #94a3b8; box-shadow: 0 0 25px rgba(148, 163, 184, 0.25); }
    .product-card.office-accent:hover  { border-color: #eab308; box-shadow: 0 0 25px rgba(234, 179, 8, 0.25); }
    .product-card.decor-accent:hover   { border-color: #ec4899; box-shadow: 0 0 25px rgba(236, 72, 153, 0.25); }
    .product-card.apparel-accent:hover { border-color: #f97316; box-shadow: 0 0 25px rgba(249, 115, 22, 0.25); }
    .product-card.tech-accent:hover    { border-color: #10b981; box-shadow: 0 0 25px rgba(16, 185, 129, 0.25); }
    .product-card.print-accent:hover   { border-color: #06b6d4; box-shadow: 0 0 25px rgba(6, 182, 212, 0.25); }
    .product-card.wood-accent:hover    { border-color: #8b5a1a; box-shadow: 0 0 25px rgba(139, 92, 26, 0.25); }
    .product-card.gift-accent:hover    { border-color: #ef4444; box-shadow: 0 0 25px rgba(239, 68, 68, 0.25); }
    .product-card.craft-accent:hover   { border-color: #8b55f6; box-shadow: 0 0 25px rgba(139, 92, 246, 0.25); }

    /* --- SWIPEABLE CATALOG INTERACTIVE PRODUCT STAGE --- */
    .image-banner-stage {
        position: relative;
        width: 100%;
        height: 220px;
        margin-bottom: 18px;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.1);
        user-select: none;
        touch-action: pan-y;
    }
    
    .product-images-rail {
        display: flex;
        width: 100%;
        height: 100%;
        transition: transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    
    .product-images-rail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        flex-shrink: 0;
        pointer-events: none;
    }

    /* --- SLIDER UI ARROWS --- */
    .slider-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 36px;
        height: 36px;
        background: rgba(11, 8, 19, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #ffffff;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        z-index: 30;
        font-size: 14px;
        font-weight: bold;
        backdrop-filter: blur(4px);
        transition: all 0.2s ease;
        opacity: 0;
    }
    .product-card:hover .slider-arrow { opacity: 1; }
    .slider-arrow:hover { background: #ffffff; color: #0b0813; border-color: #ffffff; }
    .product-card.purple-accent .slider-arrow:hover { background: #a855f7; color: #fff; border-color: #a855f7; }
    .product-card.blue-accent .slider-arrow:hover { background: #3b82f6; color: #fff; border-color: #3b82f6; }
    
    /* New Slider Arrow Hover Extensions */
    .product-card.metal-accent .slider-arrow:hover   { background: #94a3b8; color: #fff; border-color: #94a3b8; }
    .product-card.office-accent .slider-arrow:hover  { background: #eab308; color: #fff; border-color: #eab308; }
    .product-card.decor-accent .slider-arrow:hover   { background: #ec4899; color: #fff; border-color: #ec4899; }
    .product-card.apparel-accent .slider-arrow:hover { background: #f97316; color: #fff; border-color: #f97316; }
    .product-card.tech-accent .slider-arrow:hover    { background: #10b981; color: #fff; border-color: #10b981; }
    .product-card.print-accent .slider-arrow:hover   { background: #06b6d4; color: #fff; border-color: #06b6d4; }
    .product-card.wood-accent .slider-arrow:hover    { background: #8b5a1a; color: #fff; border-color: #8b5a1a; }
    .product-card.gift-accent .slider-arrow:hover    { background: #ef4444; color: #fff; border-color: #ef4444; }
    .product-card.craft-accent .slider-arrow:hover   { background: #8b55f6; color: #fff; border-color: #8b55f6; }
    
    .arrow-left { left: 10px; }
    .arrow-right { right: 10px; }

    /* Slide Indicator Dots */
    .slider-dots-indicator {
        position: absolute;
        bottom: 12px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 6px;
        z-index: 20;
    }
    .dot {
        width: 7px;
        height: 7px;
        background: rgba(255, 255, 255, 0.4);
        border-radius: 50%;
        transition: all 0.2s;
    }
    .dot.active {
        width: 18px;
        border-radius: 4px;
    }
    .product-card.purple-accent .dot.active { background: #a855f7; }
    .product-card.blue-accent .dot.active { background: #3b82f6; }
    
    /* New Dot Extensions */
    .product-card.metal-accent .dot.active   { background: #94a3b8; }
    .product-card.office-accent .dot.active  { background: #eab308; }
    .product-card.decor-accent .dot.active   { background: #ec4899; }
    .product-card.apparel-accent .dot.active { background: #f97316; }
    .product-card.tech-accent .dot.active    { background: #10b981; }
    .product-card.print-accent .dot.active   { background: #06b6d4; }
    .product-card.wood-accent .dot.active    { background: #8b5a1a; }
    .product-card.gift-accent .dot.active    { background: #ef4444; }
    .product-card.craft-accent .dot.active   { background: #8b55f6; }

    .product-info h3 { font-size: 20px; font-weight: 700; margin-bottom: 6px; }
    .product-price-display { font-size: 16px; font-weight: 700; color: #a855f7; margin-bottom: 20px; display: block; }
    .product-card.blue-accent .product-price-display { color: #3b82f6; }
    
    /* New Price Extensions */
    .product-card.metal-accent .product-price-display   { color: #94a3b8; }
    .product-card.office-accent .product-price-display  { color: #eab308; }
    .product-card.decor-accent .product-price-display   { color: #ec4899; }
    .product-card.apparel-accent .product-price-display { color: #f97316; }
    .product-card.tech-accent .product-price-display    { color: #10b981; }
    .product-card.print-accent .product-price-display   { color: #06b6d4; }
    .product-card.wood-accent .product-price-display    { color: #8b5a1a; }
    .product-card.gift-accent .product-price-display    { color: #ef4444; }
    .product-card.craft-accent .product-price-display   { color: #8b55f6; }

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
    
    /* New Pill Extensions */
    .product-card.metal-accent .option-pill-btn.active   { background: rgba(148, 163, 184, 0.15); border-color: #94a3b8; color: #fff; box-shadow: 0 0 10px rgba(148, 163, 184, 0.3); }
    .product-card.office-accent .option-pill-btn.active  { background: rgba(234, 179, 8, 0.15); border-color: #eab308; color: #fff; box-shadow: 0 0 10px rgba(234, 179, 8, 0.3); }
    .product-card.decor-accent .option-pill-btn.active   { background: rgba(236, 72, 153, 0.15); border-color: #ec4899; color: #fff; box-shadow: 0 0 10px rgba(236, 72, 153, 0.3); }
    .product-card.apparel-accent .option-pill-btn.active { background: rgba(249, 115, 22, 0.15); border-color: #f97316; color: #fff; box-shadow: 0 0 10px rgba(249, 115, 22, 0.3); }
    .product-card.tech-accent .option-pill-btn.active    { background: rgba(16, 185, 129, 0.15); border-color: #10b981; color: #fff; box-shadow: 0 0 10px rgba(16, 185, 129, 0.3); }
    .product-card.print-accent .option-pill-btn.active   { background: rgba(6, 182, 212, 0.15); border-color: #06b6d4; color: #fff; box-shadow: 0 0 10px rgba(6, 182, 212, 0.3); }
    .product-card.wood-accent .option-pill-btn.active    { background: rgba(139, 92, 26, 0.15); border-color: #8b5a1a; color: #fff; box-shadow: 0 0 10px rgba(139, 92, 26, 0.3); }
    .product-card.gift-accent .option-pill-btn.active    { background: rgba(239, 68, 68, 0.15); border-color: #ef4444; color: #fff; box-shadow: 0 0 10px rgba(239, 68, 68, 0.3); }
    .product-card.craft-accent .option-pill-btn.active   { background: rgba(139, 92, 246, 0.15); border-color: #8b55f6; color: #fff; box-shadow: 0 0 10px rgba(139, 92, 246, 0.3); }

    /* File Upload */
    .blueprint-upload-field { position: relative; background: rgba(0, 0, 0, 0.2); border: 1px dashed rgba(255, 255, 255, 0.15); border-radius: 8px; padding: 14px; text-align: center; min-height: 85px; display: flex; flex-direction: column; justify-content: center; align-items: center; }
    .blueprint-upload-field input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10; }
    .upload-status-txt { font-size: 12px; color: #64748b; font-weight: 500; }

    /* Swatch picker */
    .color-picker-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(95px, 1fr)); gap: 8px; max-height: 200px; overflow-y: auto; padding-right: 4px; }
    .color-picker-grid::-webkit-scrollbar { width: 4px; }
    .color-picker-grid::-webkit-scrollbar-thumb { background: rgba(59, 130, 246, 0.3); border-radius: 2px; }

    .color-swatch-option { background: rgba(0, 0, 0, 0.25); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 8px; padding: 8px 10px; display: flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.2s; user-select: none; }
    .color-swatch-option:hover { border-color: rgba(255, 255, 255, 0.2); background: rgba(255, 255, 255, 0.02); }
    .color-swatch-option.active { border-color: #3b82f6; background: rgba(59, 130, 246, 0.15); box-shadow: 0 0 8px rgba(59, 130, 246, 0.2); }
    .color-preview-circle { width: 14px; height: 14px; border-radius: 50%; border: 1px solid rgba(255, 255, 255, 0.15); flex-shrink: 0; }
    .color-name-text { font-size: 11px; color: #cbd5e1; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .color-swatch-option.active .color-name-text { color: #ffffff; }

    .product-footer { display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.05); }
    .tag-pill { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #a855f7; }
    .product-card.blue-accent .tag-pill { color: #3b82f6; }
    
    /* New Tag Pill Extensions */
    .product-card.metal-accent .tag-pill   { color: #94a3b8; }
    .product-card.office-accent .tag-pill  { color: #eab308; }
    .product-card.decor-accent .tag-pill   { color: #ec4899; }
    .product-card.apparel-accent .tag-pill { color: #f97316; }
    .product-card.tech-accent .tag-pill    { color: #10b981; }
    .product-card.print-accent .tag-pill   { color: #06b6d4; }
    .product-card.wood-accent .tag-pill    { color: #8b5a1a; }
    .product-card.gift-accent .tag-pill    { color: #ef4444; }
    .product-card.craft-accent .tag-pill   { color: #8b55f6; }

    .add-cart-btn { background: transparent; color: #fff; border: 1px solid rgba(168, 85, 247, 0.6); padding: 10px 22px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; }
    .product-card:hover .add-cart-btn { background: #a855f7; border-color: #a855f7; }
    .product-card.blue-accent:hover .add-cart-btn { background: #3b82f6; border-color: #3b82f6; }
    
    /* New Add to Cart Hover Actions */
    .product-card.metal-accent:hover .add-cart-btn   { background: #94a3b8; border-color: #94a3b8; }
    .product-card.office-accent:hover .add-cart-btn  { background: #eab308; border-color: #eab308; }
    .product-card.decor-accent:hover .add-cart-btn   { background: #ec4899; border-color: #ec4899; }
    .product-card.apparel-accent:hover .add-cart-btn { background: #f97316; border-color: #f97316; }
    .product-card.tech-accent:hover .add-cart-btn    { background: #10b981; border-color: #10b981; }
    .product-card.print-accent:hover .add-cart-btn   { background: #06b6d4; border-color: #06b6d4; }
    .product-card.wood-accent:hover .add-cart-btn    { background: #8b5a1a; border-color: #8b5a1a; }
    .product-card.gift-accent:hover .add-cart-btn    { background: #ef4444; border-color: #ef4444; }
    .product-card.craft-accent:hover .add-cart-btn   { background: #8b55f6; border-color: #8b55f6; }

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
    .close-cart { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #94a3b8; font-size: 20px; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; justify-content: center; align-items: center; }

    .cart-items-wrapper { flex-grow: 1; overflow-y: auto; margin-bottom: 20px; }
    .cart-item-card { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 15px; margin-bottom: 15px; display: flex; flex-direction: column; gap: 12px; }
    .cart-item-meta-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
    .cart-item-title { font-weight: 600; font-size: 14px; }
    .cart-item-spec-note { font-size: 11px; color: #94a3b8; }
    .cart-item-price { font-size: 13px; color: #a855f7; font-weight: 600; }
    .cart-item-card.blue-accent .cart-item-price { color: #3b82f6; }
    
    /* New Sidebar Price Extensions */
    .cart-item-card.metal-accent .cart-item-price   { color: #94a3b8; }
    .cart-item-card.office-accent .cart-item-price  { color: #eab308; }
    .cart-item-card.decor-accent .cart-item-price   { color: #ec4899; }
    .cart-item-card.apparel-accent .cart-item-price { color: #f97316; }
    .cart-item-card.tech-accent .cart-item-price    { color: #10b981; }
    .cart-item-card.print-accent .cart-item-price   { color: #06b6d4; }
    .cart-item-card.wood-accent .cart-item-price    { color: #8b5a1a; }
    .cart-item-card.gift-accent .cart-item-price    { color: #ef4444; }
    .cart-item-card.craft-accent .cart-item-price   { color: #8b55f6; }

    .cart-preview-images-row { display: flex; flex-wrap: wrap; gap: 6px; }
    .cart-preview-thumbnail { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid rgba(168, 85, 247, 0.4); }
    .cart-item-card.blue-accent .cart-preview-thumbnail { border-color: rgba(59, 130, 246, 0.4); }
    
    /* New Sidebar Thumbnail Extensions */
    .cart-item-card.metal-accent .cart-preview-thumbnail   { border-color: rgba(148, 163, 184, 0.4); }
    .cart-item-card.office-accent .cart-preview-thumbnail  { border-color: rgba(234, 179, 8, 0.4); }
    .cart-item-card.decor-accent .cart-preview-thumbnail   { border-color: rgba(236, 72, 153, 0.4); }
    .cart-item-card.apparel-accent .cart-preview-thumbnail { border-color: rgba(249, 115, 22, 0.4); }
    .cart-item-card.tech-accent .cart-preview-thumbnail    { border-color: rgba(16, 185, 129, 0.4); }
    .cart-item-card.print-accent .cart-preview-thumbnail   { border-color: rgba(6, 182, 212, 0.4); }
    .cart-item-card.wood-accent .cart-preview-thumbnail    { border-color: rgba(139, 92, 26, 0.4); }
    .cart-item-card.gift-accent .cart-preview-thumbnail    { border-color: rgba(239, 68, 68, 0.4); }
    .cart-item-card.craft-accent .cart-preview-thumbnail   { border-color: rgba(139, 92, 246, 0.4); }
    
    .cart-item-qty-row { display: flex; justify-content: space-between; align-items: center; }
    
    .cart-qty-inline-picker { display: flex; align-items: center; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(168, 85, 247, 0.4); border-radius: 6px; overflow: hidden; width: 100px; height: 30px; }
    .cart-item-card.blue-accent .cart-qty-inline-picker { border-color: rgba(59, 130, 246, 0.4); }
    
    /* New Sidebar Picker Extensions */
    .cart-item-card.metal-accent .cart-qty-inline-picker   { border-color: rgba(148, 163, 184, 0.4); }
    .cart-item-card.office-accent .cart-qty-inline-picker  { border-color: rgba(234, 179, 8, 0.4); }
    .cart-item-card.decor-accent .cart-qty-inline-picker   { border-color: rgba(236, 72, 153, 0.4); }
    .cart-item-card.apparel-accent .cart-qty-inline-picker { border-color: rgba(249, 115, 22, 0.4); }
    .cart-item-card.tech-accent .cart-qty-inline-picker    { border-color: rgba(16, 185, 129, 0.4); }
    .cart-item-card.print-accent .cart-qty-inline-picker   { border-color: rgba(6, 182, 212, 0.4); }
    .cart-item-card.wood-accent .cart-qty-inline-picker    { border-color: rgba(139, 92, 26, 0.4); }
    .cart-item-card.gift-accent .cart-qty-inline-picker    { border-color: rgba(239, 68, 68, 0.4); }
    .cart-item-card.craft-accent .cart-qty-inline-picker   { border-color: rgba(139, 92, 246, 0.4); }
    
    .cart-qty-inline-btn { background: transparent; border: none; color: #a855f7; width: 30px; height: 100%; font-size: 14px; cursor: pointer; font-weight: 700; }
    .cart-item-card.blue-accent .cart-qty-inline-btn { color: #3b82f6; }
    
    /* New Sidebar Qty Button Extensions */
    .cart-item-card.metal-accent .cart-qty-inline-btn   { color: #94a3b8; }
    .cart-item-card.office-accent .cart-qty-inline-btn  { color: #eab308; }
    .cart-item-card.decor-accent .cart-qty-inline-btn   { color: #ec4899; }
    .cart-item-card.apparel-accent .cart-qty-inline-btn { color: #f97316; }
    .cart-item-card.tech-accent .cart-qty-inline-btn    { color: #10b981; }
    .cart-item-card.print-accent .cart-qty-inline-btn   { color: #06b6d4; }
    .cart-item-card.wood-accent .cart-qty-inline-btn    { color: #8b5a1a; }
    .cart-item-card.gift-accent .cart-qty-inline-btn    { color: #ef4444; }
    .cart-item-card.craft-accent .cart-qty-inline-btn   { color: #8b55f6; }
    
    .cart-qty-inline-display { background: transparent; border: none; color: #fff; text-align: center; font-size: 13px; font-weight: 700; width: 40px; }
    .remove-item-btn { background: transparent; border: none; color: #64748b; font-size: 12px; cursor: pointer; }

    .cart-summary-block { background: rgba(15, 12, 28, 0.8); border: 1px solid rgba(168, 85, 247, 0.2); border-radius: 12px; padding: 20px; margin-bottom: 15px; }
    .summary-row { display: flex; justify-content: space-between; font-size: 13px; color: #94a3b8; margin-bottom: 8px; }
    .summary-row.total-line { border-top: 1px dashed rgba(255, 255, 255, 0.1); padding-top: 10px; margin-top: 10px; font-size: 16px; font-weight: 700; color: #fff; }
    
    .cart-checkout-btn { background: linear-gradient(to right, #a855f7, #3b82f6); color: #fff; border: none; width: 100%; padding: 15px; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; transition: opacity 0.2s; }
    .cart-checkout-btn:hover { opacity: 0.9; }
    .empty-cart-msg { color: #475569; text-align: center; margin-top: 80px; font-size: 14px; }
    footer { border-top: 1px solid rgba(168, 85, 247, 0.2); background: #06040a; padding: 40px 8%; text-align: center; margin-top: 100px; }}
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
            <div class="image-banner-stage" data-product="wood">
                <button class="slider-arrow arrow-left" onclick="shiftGalleryImage('wood', -1)">‹</button>
                <button class="slider-arrow arrow-right" onclick="shiftGalleryImage('wood', 1)">›</button>
                <div class="product-images-rail" id="rail-prod-wood">
                    <img src="image/wood.jpg" alt="Wood Plate View 1">
                    <img src="image/wood2.jpg" alt="Wood Plate View 2">
                </div>
                <div class="slider-dots-indicator">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                </div>
            </div>
            <div class="product-info-wrap">
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
                    <input type="file" accept="image/*" id="file-wood" onchange="refreshUploadStatus(this, 'wood')">
                    <span class="upload-status-txt" id="status-wood">Upload design blueprint vectors</span>
                </div>
            </div>
            <div class="product-footer">
                <span class="tag-pill">Woodwork</span>
                <button class="add-cart-btn" onclick="addAssetToCart('wood', 'Wooden Engraved Plate')">Configure Base</button>
            </div>
        </div>

        <div class="product-card blue-accent" id="card-keychain">
            <div class="image-banner-stage" data-product="keychain">
                <button class="slider-arrow arrow-left" onclick="shiftGalleryImage('keychain', -1)">‹</button>
                <button class="slider-arrow arrow-right" onclick="shiftGalleryImage('keychain', 1)">›</button>
                <div class="product-images-rail" id="rail-prod-keychain">
                    <img src="image/keychain.jpg" alt="Keychain View 1">
                    <img src="image/keychain2.jpg" alt="Keychain View 2">
                </div>
                <div class="slider-dots-indicator">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                </div>
            </div>
            <div class="product-info-wrap">
                <h3>Custom Acrylic Keychain</h3>
                <span class="product-price-display" id="price-keychain">$2.00</span>
            </div>
            <div class="config-group">
                <span class="config-label">Insert Media Graphics</span>
                <div class="blueprint-upload-field">
                    <input type="file" accept="image/*" id="file-keychain" onchange="refreshUploadStatus(this, 'keychain')">
                    <span class="upload-status-txt" id="status-keychain">Upload display artwork frames</span>
                </div>
            </div>
            <div class="product-footer">
                <span class="tag-pill">Accessories</span>
                <button class="add-cart-btn" onclick="addAssetToCart('keychain', 'Custom Acrylic Keychain')">Configure Base</button>
            </div>
        </div>

        <div class="product-card purple-accent" id="card-pins">
            <div class="image-banner-stage" data-product="pins">
                <button class="slider-arrow arrow-left" onclick="shiftGalleryImage('pins', -1)">‹</button>
                <button class="slider-arrow arrow-right" onclick="shiftGalleryImage('pins', 1)">›</button>
                <div class="product-images-rail" id="rail-prod-pins">
                    <img src="image/pin.jpg" alt="Lapel Pin View 1">
                    <img src="image/pin2.jpg" alt="Lapel Pin View 2">
                </div>
                <div class="slider-dots-indicator">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                </div>
            </div>
            <div class="product-info-wrap">
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
                    <input type="file" accept="image/*" id="file-pins" onchange="refreshUploadStatus(this, 'pins')">
                    <span class="upload-status-txt" id="status-pins">Upload face graphic prints</span>
                </div>
            </div>
            <div class="product-footer">
                <span class="tag-pill">Badges</span>
                <button class="add-cart-btn" onclick="addAssetToCart('pins', 'Custom Lapel Pin')">Configure Base</button>
            </div>
        </div>

        <div class="product-card blue-accent" id="card-flowers">
            <div class="image-banner-stage" data-product="flowers">
                <button class="slider-arrow arrow-left" onclick="shiftGalleryImage('flowers', -1)">‹</button>
                <button class="slider-arrow arrow-right" onclick="shiftGalleryImage('flowers', 1)">›</button>
                <div class="product-images-rail" id="rail-prod-flowers">
                    <img src="image/bouquet.jpg" alt="Bouquet View 1">
                    <img src="image/bouquet2.jpg" alt="Bouquet View 2">
                </div>
                <div class="slider-dots-indicator">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                </div>
            </div>
            <div class="product-info-wrap">
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
                <span class="config-label">Ribbon Base Color Profiles (Select Multiple)</span>
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
                        <span class="color-preview-circle" style="background-color: #7dd3fc;"></span>
                        <span class="color-name-text">Light Blue</span>
                    </div>
                    <div class="color-swatch-option" data-color="Dark Blue" onclick="toggleRibbonColor(this)">
                        <span class="color-preview-circle" style="background-color: #1d4ed8;"></span>
                        <span class="color-name-text">Dark Blue</span>
                    </div>
                    <div class="color-swatch-option" data-color="Purple" onclick="toggleRibbonColor(this)">
                        <span class="color-preview-circle" style="background-color: #7c3aed;"></span>
                        <span class="color-name-text">Purple</span>
                    </div>
                    <div class="color-swatch-option" data-color="Lavender" onclick="toggleRibbonColor(this)">
                        <span class="color-preview-circle" style="background-color: #ddd6fe;"></span>
                        <span class="color-name-text">Lavender</span>
                    </div>
                    <div class="color-swatch-option" data-color="Yellow" onclick="toggleRibbonColor(this)">
                        <span class="color-preview-circle" style="background-color: #eab308;"></span>
                        <span class="color-name-text">Yellow</span>
                    </div>
                    <div class="color-swatch-option" data-color="White" onclick="toggleRibbonColor(this)">
                        <span class="color-preview-circle" style="background-color: #ffffff;"></span>
                        <span class="color-name-text">White</span>
                    </div>
                    <div class="color-swatch-option" data-color="Black" onclick="toggleRibbonColor(this)">
                        <span class="color-preview-circle" style="background-color: #000000;"></span>
                        <span class="color-name-text">Black</span>
                    </div>
                    <div class="color-swatch-option" data-color="Off-white" onclick="toggleRibbonColor(this)">
                        <span class="color-preview-circle" style="background-color: #fafaf9;"></span>
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

        <div class="product-card metal-accent" id="card-bottle">
            <div class="image-banner-stage" data-product="bottle">
                <button class="slider-arrow arrow-left" onclick="shiftGalleryImage('bottle', -1)">‹</button>
                <button class="slider-arrow arrow-right" onclick="shiftGalleryImage('bottle', 1)">›</button>
                <div class="product-images-rail" id="rail-prod-bottle">
                    <img src="image/bottle.jpg" alt="Water Bottle View 1">
                    <img src="image/bottle2.jpg" alt="Water Bottle View 2">
                </div>
                <div class="slider-dots-indicator">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                </div>
            </div>
            <div class="product-info-wrap">
                <h3>Stainless Steel Water Bottle</h3>
                <span class="product-price-display" id="price-bottle">$18.00</span>
            </div>
            <div class="config-group">
                <span class="config-label">Engraving Graphics Vector</span>
                <div class="blueprint-upload-field">
                    <input type="file" accept="image/*" id="file-bottle" onchange="refreshUploadStatus(this, 'bottle')">
                    <span class="upload-status-txt" id="status-bottle">Upload design blueprint vectors</span>
                </div>
            </div>
            <div class="product-footer">
                <span class="tag-pill">Drinkware</span>
                <button class="add-cart-btn" onclick="addAssetToCart('bottle', 'Stainless Steel Water Bottle')">Configure Base</button>
            </div>
        </div>

        <div class="product-card office-accent" id="card-mousepad">
            <div class="image-banner-stage" data-product="mousepad">
                <button class="slider-arrow arrow-left" onclick="shiftGalleryImage('mousepad', -1)">‹</button>
                <button class="slider-arrow arrow-right" onclick="shiftGalleryImage('mousepad', 1)">›</button>
                <div class="product-images-rail" id="rail-prod-mousepad">
                    <img src="image/mousepad.jpg" alt="Mouse Pad View 1">
                    <img src="image/mousepad2.jpg" alt="Mouse Pad View 2">
                </div>
                <div class="slider-dots-indicator">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                </div>
            </div>
            <div class="product-info-wrap">
                <h3>Mouse Pad (A5 Size)</h3>
                <span class="product-price-display" id="price-mousepad">$8.00</span>
            </div>
            <div class="config-group">
                <span class="config-label">Custom Print Image</span>
                <div class="blueprint-upload-field">
                    <input type="file" accept="image/*" id="file-mousepad" onchange="refreshUploadStatus(this, 'mousepad')">
                    <span class="upload-status-txt" id="status-mousepad">Upload print-ready graphic</span>
                </div>
            </div>
            <div class="product-footer">
                <span class="tag-pill">Office Accessories</span>
                <button class="add-cart-btn" onclick="addAssetToCart('mousepad', 'Mouse Pad A5 Size')">Configure Base</button>
            </div>
        </div>

        <div class="product-card decor-accent" id="card-magnet">
            <div class="image-banner-stage" data-product="magnet">
                <button class="slider-arrow arrow-left" onclick="shiftGalleryImage('magnet', -1)">‹</button>
                <button class="slider-arrow arrow-right" onclick="shiftGalleryImage('magnet', 1)">›</button>
                <div class="product-images-rail" id="rail-prod-magnet">
                    <img src="image/magnet.jpg" alt="Magnet Photo View 1">
                    <img src="image/magnet2.jpg" alt="Magnet Photo View 2">
                </div>
                <div class="slider-dots-indicator">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                </div>
            </div>
            <div class="product-info-wrap">
                <h3>Magnet Photo</h3>
                <span class="product-price-display" id="price-magnet">$4.00</span>
            </div>
            <div class="config-group">
                <span class="config-label">Photo Image</span>
                <div class="blueprint-upload-field">
                    <input type="file" accept="image/*" id="file-magnet" onchange="refreshUploadStatus(this, 'magnet')">
                    <span class="upload-status-txt" id="status-magnet">Upload your photo</span>
                </div>
            </div>
            <div class="product-footer">
                <span class="tag-pill">Home Decor</span>
                <button class="add-cart-btn" onclick="addAssetToCart('magnet', 'Magnet Photo')">Configure Base</button>
            </div>
        </div>

        <div class="product-card apparel-accent" id="card-tshirt">
            <div class="image-banner-stage" data-product="tshirt">
                <button class="slider-arrow arrow-left" onclick="shiftGalleryImage('tshirt', -1)">‹</button>
                <button class="slider-arrow arrow-right" onclick="shiftGalleryImage('tshirt', 1)">›</button>
                <div class="product-images-rail" id="rail-prod-tshirt">
                    <img src="image/tshirt.jpg" alt="Printed T-Shirt View 1">
                    <img src="image/tshirt2.jpg" alt="Printed T-Shirt View 2">
                </div>
                <div class="slider-dots-indicator">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                </div>
            </div>
            <div class="product-info-wrap">
                <h3>Printed T-Shirt</h3>
                <span class="product-price-display" id="price-tshirt">$20.00</span>
            </div>
            <div class="config-group">
                <span class="config-label">Size Specs</span>
                <div class="custom-select-wrapper">
                    <button class="option-pill-btn" onclick="updateItemPrice('tshirt', 20.00, this, 'Small')">Small ($20)</button>
                    <button class="option-pill-btn active" onclick="updateItemPrice('tshirt', 20.00, this, 'Medium')">Medium ($20)</button>
                    <button class="option-pill-btn" onclick="updateItemPrice('tshirt', 20.00, this, 'Large')">Large ($20)</button>
                    <button class="option-pill-btn" onclick="updateItemPrice('tshirt', 20.00, this, 'XL')">XL ($20)</button>
                </div>
            </div>
            <div class="config-group">
                <span class="config-label">T-Shirt Print Graphic</span>
                <div class="blueprint-upload-field">
                    <input type="file" accept="image/*" id="file-tshirt" onchange="refreshUploadStatus(this, 'tshirt')">
                    <span class="upload-status-txt" id="status-tshirt">Upload design blueprint vectors</span>
                </div>
            </div>
            <div class="product-footer">
                <span class="tag-pill">Apparel</span>
                <button class="add-cart-btn" onclick="addAssetToCart('tshirt', 'Printed T-Shirt')">Configure Base</button>
            </div>
        </div>

        <div class="product-card tech-accent" id="card-phoneholder">
            <div class="image-banner-stage" data-product="phoneholder">
                <button class="slider-arrow arrow-left" onclick="shiftGalleryImage('phoneholder', -1)">‹</button>
                <button class="slider-arrow arrow-right" onclick="shiftGalleryImage('phoneholder', 1)">›</button>
                <div class="product-images-rail" id="rail-prod-phoneholder">
                    <img src="image/phoneholder.jpg" alt="Phone Holder View 1">
                    <img src="image/phoneholder2.jpg" alt="Phone Holder View 2">
                </div>
                <div class="slider-dots-indicator">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                </div>
            </div>
            <div class="product-info-wrap">
                <h3>Phone Holder</h3>
                <span class="product-price-display" id="price-phoneholder">$12.00</span>
            </div>
            <div class="config-group">
                <span class="config-label">Customization Graphics</span>
                <div class="blueprint-upload-field">
                    <input type="file" accept="image/*" id="file-phoneholder" onchange="refreshUploadStatus(this, 'phoneholder')">
                    <span class="upload-status-txt" id="status-phoneholder">Upload engraving artwork</span>
                </div>
            </div>
            <div class="product-footer">
                <span class="tag-pill">Accessories</span>
                <button class="add-cart-btn" onclick="addAssetToCart('phoneholder', 'Phone Holder')">Configure Base</button>
            </div>
        </div>

        <div class="product-card print-accent" id="card-bookmark">
            <div class="image-banner-stage" data-product="bookmark">
                <button class="slider-arrow arrow-left" onclick="shiftGalleryImage('bookmark', -1)">‹</button>
                <button class="slider-arrow arrow-right" onclick="shiftGalleryImage('bookmark', 1)">›</button>
                <div class="product-images-rail" id="rail-prod-bookmark">
                    <img src="image/bookmark.jpg" alt="Custom Bookmark View 1">
                    <img src="image/bookmark2.jpg" alt="Custom Bookmark View 2">
                </div>
                <div class="slider-dots-indicator">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                </div>
            </div>
            <div class="product-info-wrap">
                <h3>Custom Bookmark</h3>
                <span class="product-price-display" id="price-bookmark">$3.50</span>
            </div>
            <div class="config-group">
                <span class="config-label">Bookmark Graphics</span>
                <div class="blueprint-upload-field">
                    <input type="file" accept="image/*" id="file-bookmark" onchange="refreshUploadStatus(this, 'bookmark')">
                    <span class="upload-status-txt" id="status-bookmark">Upload design artwork</span>
                </div>
            </div>
            <div class="product-footer">
                <span class="tag-pill">Stationery</span>
                <button class="add-cart-btn" onclick="addAssetToCart('bookmark', 'Custom Bookmark')">Configure Base</button>
            </div>
        </div>

        <div class="product-card wood-accent" id="card-wood-engrave">
            <div class="image-banner-stage" data-product="wood-engrave">
                <button class="slider-arrow arrow-left" onclick="shiftGalleryImage('wood-engrave', -1)">‹</button>
                <button class="slider-arrow arrow-right" onclick="shiftGalleryImage('wood-engrave', 1)">›</button>
                <div class="product-images-rail" id="rail-prod-wood-engrave">
                    <img src="image/wood_engrave.jpg" alt="Engrave Photo View 1">
                    <img src="image/wood_engrave2.jpg" alt="Engrave Photo View 2">
                </div>
                <div class="slider-dots-indicator">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                </div>
            </div>
            <div class="product-info-wrap">
                <h3>Engrave Photo on Wood</h3>
                <span class="product-price-display" id="price-wood-engrave">$10.00</span>
            </div>
            <div class="config-group">
                <span class="config-label">Dimension Specs</span>
                <div class="custom-select-wrapper">
                    <button class="option-pill-btn" onclick="updateItemPrice('wood-engrave', 5.00, this, 'A6')">A6 ($5)</button>
                    <button class="option-pill-btn active" onclick="updateItemPrice('wood-engrave', 10.00, this, 'A5')">A5 ($10)</button>
                    <button class="option-pill-btn" onclick="updateItemPrice('wood-engrave', 20.00, this, 'A4')">A4 ($20)</button>
                </div>
            </div>
            <div class="config-group">
                <span class="config-label">Engraving Graphics Vector</span>
                <div class="blueprint-upload-field">
                    <input type="file" accept="image/*" id="file-wood-engrave" onchange="refreshUploadStatus(this, 'wood-engrave')">
                    <span class="upload-status-txt" id="status-wood-engrave">Upload design blueprint vectors</span>
                </div>
            </div>
            <div class="product-footer">
                <span class="tag-pill">Woodwork</span>
                <button class="add-cart-btn" onclick="addAssetToCart('wood-engrave', 'Engrave Photo on Wood')">Configure Base</button>
            </div>
        </div>

        <div class="product-card gift-accent" id="card-pack-birthday">
            <div class="image-banner-stage" data-product="pack-birthday">
                <button class="slider-arrow arrow-left" onclick="shiftGalleryImage('pack-birthday', -1)">‹</button>
                <button class="slider-arrow arrow-right" onclick="shiftGalleryImage('pack-birthday', 1)">›</button>
                <div class="product-images-rail" id="rail-prod-pack-birthday">
                    <img src="image/birthday_pack.jpg" alt="Birthday Gift Pack View 1">
                    <img src="image/birthday_pack2.jpg" alt="Birthday Gift Pack View 2">
                </div>
                <div class="slider-dots-indicator">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                </div>
            </div>
            <div class="product-info-wrap">
                <h3>Birthday Gift Pack</h3>
                <span class="product-price-display" id="price-pack-birthday">$35.00</span>
            </div>
            <div class="config-group">
                <span class="config-label">Custom Lid Image Upload</span>
                <div class="blueprint-upload-field">
                    <input type="file" accept="image/*" id="file-pack-birthday" onchange="refreshUploadStatus(this, 'pack-birthday')">
                    <span class="upload-status-txt" id="status-pack-birthday">Upload card or item graphic</span>
                </div>
            </div>
            <div class="product-footer">
                <span class="tag-pill">Wooden Themed Pack</span>
                <button class="add-cart-btn" onclick="addAssetToCart('pack-birthday', 'Birthday Gift Pack')">Configure Base</button>
            </div>
        </div>

        <div class="product-card gift-accent" id="card-pack-anime">
            <div class="image-banner-stage" data-product="pack-anime">
                <button class="slider-arrow arrow-left" onclick="shiftGalleryImage('pack-anime', -1)">‹</button>
                <button class="slider-arrow arrow-right" onclick="shiftGalleryImage('pack-anime', 1)">›</button>
                <div class="product-images-rail" id="rail-prod-pack-anime">
                    <img src="image/anime_pack.jpg" alt="Anime Gift Pack View 1">
                    <img src="image/anime_pack2.jpg" alt="Anime Gift Pack View 2">
                </div>
                <div class="slider-dots-indicator">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                </div>
            </div>
            <div class="product-info-wrap">
                <h3>Anime Pack</h3>
                <span class="product-price-display" id="price-pack-anime">$40.00</span>
            </div>
            <div class="config-group">
                <span class="config-label">Custom Anime Image Art</span>
                <div class="blueprint-upload-field">
                    <input type="file" accept="image/*" id="file-pack-anime" onchange="refreshUploadStatus(this, 'pack-anime')">
                    <span class="upload-status-txt" id="status-pack-anime">Upload your illustration</span>
                </div>
            </div>
            <div class="product-footer">
                <span class="tag-pill">Wooden Themed Pack</span>
                <button class="add-cart-btn" onclick="addAssetToCart('pack-anime', 'Anime Pack')">Configure Base</button>
            </div>
        </div>

        <div class="product-card gift-accent" id="card-pack-name">
            <div class="image-banner-stage" data-product="pack-name">
                <button class="slider-arrow arrow-left" onclick="shiftGalleryImage('pack-name', -1)">‹</button>
                <button class="slider-arrow arrow-right" onclick="shiftGalleryImage('pack-name', 1)">›</button>
                <div class="product-images-rail" id="rail-prod-pack-name">
                    <img src="image/name_pack.jpg" alt="Wooden Name Pack View 1">
                    <img src="image/name_pack2.jpg" alt="Wooden Name Pack View 2">
                </div>
                <div class="slider-dots-indicator">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                </div>
            </div>
            <div class="product-info-wrap">
                <h3>Wooden Name Pack</h3>
                <span class="product-price-display" id="price-pack-name">$38.00</span>
            </div>
            <div class="config-group">
                <span class="config-label">Name Artwork Design Vector</span>
                <div class="blueprint-upload-field">
                    <input type="file" accept="image/*" id="file-pack-name" onchange="refreshUploadStatus(this, 'pack-name')">
                    <span class="upload-status-txt" id="status-pack-name">Upload typography files</span>
                </div>
            </div>
            <div class="product-footer">
                <span class="tag-pill">Wooden Themed Pack</span>
                <button class="add-cart-btn" onclick="addAssetToCart('pack-name', 'Wooden Name Pack')">Configure Base</button>
            </div>
        </div>

        <div class="product-card craft-accent" id="card-paintkit">
            <div class="image-banner-stage" data-product="paintkit">
                <button class="slider-arrow arrow-left" onclick="shiftGalleryImage('paintkit', -1)">‹</button>
                <button class="slider-arrow arrow-right" onclick="shiftGalleryImage('paintkit', 1)">›</button>
                <div class="product-images-rail" id="rail-prod-paintkit">
                    <img src="image/paintkit.jpg" alt="Gypsum Paint Kit View 1">
                    <img src="image/paintkit2.jpg" alt="Gypsum Paint Kit View 2">
                </div>
                <div class="slider-dots-indicator">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                </div>
            </div>
            <div class="product-info-wrap">
                <h3>Gypsum Paint Kit</h3>
                <span class="product-price-display" id="price-paintkit">$15.00</span>
            </div>
            <div class="config-group">
                <span class="config-label">Custom Instruction Sheet Photo</span>
                <div class="blueprint-upload-field">
                    <input type="file" accept="image/*" id="file-paintkit" onchange="refreshUploadStatus(this, 'paintkit')">
                    <span class="upload-status-txt" id="status-paintkit">Upload design blueprint vectors</span>
                </div>
            </div>
            <div class="product-footer">
                <span class="tag-pill">DIY Craft Kits</span>
                <button class="add-cart-btn" onclick="addAssetToCart('paintkit', 'Gypsum Paint Kit')">Configure Base</button>
            </div>
        </div>

    </div> </div> <div class="cart-sidebar" id="cartSidebarPanel">
    <div class="cart-side-header">
        <h2>Production Rack</h2>
        <button class="close-cart" onclick="toggleCart(false)">×</button>
    </div>
    <div class="cart-items-wrapper" id="cartDrawerList">
        <div class="empty-cart-msg">Your configuration rack is empty.</div>
    </div>
    <div class="cart-summary-block">
        <div class="summary-row"><span>Active Quantities</span><span id="summaryItemsCount">0 items</span></div>
        <div class="summary-row"><span>Cargo Shipping</span><span style="color:#22c55e; font-weight:600;">FREE STUDIO DELIVERY</span></div>
        <div class="summary-row total-line"><span>Total Assets Summary</span><span id="summaryTotalQty">$0.00</span></div>
    </div>

    <form id="checkoutProcessingForm" action="checkout.php" method="POST">
        <input type="hidden" name="custom_specs" id="formCustomSpecsBridge">
        <button type="button" class="cart-checkout-btn" onclick="submitToCheckoutDesk()">Proceed to Order Checkout</button>
    </form>
</div>

<footer>
    <p>&copy; <?= date('Y') ?> Athar Studio. All Rights Reserved.</p>
</footer>
    <script>
      // Track running memory configurations globally
// Track running memory configurations globally
// Track running memory configurations globally
const globalCartEngine = [];

// 1. EXTENDED DYNAMIC PRICE MATRIX (Includes all new base prices)
const dynamicPriceMatrix = { 
    wood: 5.00, 
    keychain: 2.00, 
    pins: 2.00, 
    flowers: 10.00,
    bottle: 18.00,
    mousepad: 8.00,
    magnet: 4.00,
    tshirt: 20.00,
    phoneholder: 12.00,
    bookmark: 3.50,
    'wood-engrave': 10.00,
    'pack-birthday': 35.00,
    'pack-anime': 40.00,
    'pack-name': 38.00,
    paintkit: 15.00
};

// 2. EXTENDED DEFAULT SPECIFICATION LABELS
const dynamicSpecLabels = { 
    wood: 'Size: A6', 
    keychain: 'Acrylic Base', 
    pins: '5.5cm Pin Frame', 
    flowers: 'Scale: 10 Flowers | Colors: Red',
    bottle: 'Standard Setup',
    mousepad: 'Size: A5',
    magnet: 'Standard Photo Unit',
    tshirt: 'Standard Custom Print',
    phoneholder: 'Standard Accent Mount',
    bookmark: 'Standard Custom Cut',
    'wood-engrave': 'Size: A5',
    'pack-birthday': 'Wooden Themed Box Configuration',
    'pack-anime': 'Wooden Themed Anime Pack',
    'pack-name': 'Wooden Themed Name Pack',
    paintkit: 'Gypsum Paint Set Pack'
};

// 3. EXTENDED SLIDER TRACKER MATRIX
const sliderTracker = { 
    wood: 0, 
    keychain: 0, 
    pins: 0, 
    flowers: 0,
    bottle: 0,
    mousepad: 0,
    magnet: 0,
    tshirt: 0,
    phoneholder: 0,
    bookmark: 0,
    'wood-engrave': 0,
    'pack-birthday': 0,
    'pack-anime': 0,
    'pack-name': 0,
    paintkit: 0
};

// Image Slider Navigation Control Loops
function shiftGalleryImage(productId, step) {
    const rail = document.getElementById(`rail-prod-${productId}`);
    if (!rail) return; // Guard clause if slider element doesn't exist
    
    const images = rail.querySelectorAll('img');
    const totalImages = images.length;
    if (totalImages === 0) return;
    
    sliderTracker[productId] = (sliderTracker[productId] + step + totalImages) % totalImages;
    rail.style.transform = `translateX(-${sliderTracker[productId] * 100}%)`;
    
    // Sync indicators
    const dots = rail.parentElement.querySelectorAll('.slider-dots-indicator .dot');
    dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === sliderTracker[productId]);
    });
}

// Dynamic Price & Selection Metric Swaps
function updateItemPrice(productId, pricingUnit, engineElement, specLabel) {
    dynamicPriceMatrix[productId] = parseFloat(pricingUnit);
    const priceDisplay = document.getElementById(`price-${productId}`);
    if (priceDisplay) {
        priceDisplay.innerText = `$${dynamicPriceMatrix[productId].toFixed(2)}`;
    }
    
    // Toggle element active states
    const configurationSiblings = engineElement.parentElement.querySelectorAll('.option-pill-btn');
    configurationSiblings.forEach(btn => btn.classList.remove('active'));
    engineElement.classList.add('active');

    if (productId === 'flowers') {
        syncFlowerSpecText(specLabel);
    } else {
        dynamicSpecLabels[productId] = `Size: ${specLabel}`;
    }
}

// Flower Ribbon Array Processor 
function toggleRibbonColor(swatchElement) {
    swatchElement.classList.toggle('active');
    const parentContainer = swatchElement.parentElement;
    const activeSwatches = parentContainer.querySelectorAll('.color-swatch-option.active');
    
    const colorArray = [];
    activeSwatches.forEach(swatch => {
        colorArray.push(swatch.getAttribute('data-color'));
    });

    if (colorArray.length === 0) {
        swatchElement.classList.add('active');
        colorArray.push(swatchElement.getAttribute('data-color'));
    }

    const ribbonInput = document.getElementById('selected_ribbon_colors');
    if (ribbonInput) {
        ribbonInput.value = colorArray.join(', ');
    }
    
    const activeScaleBtn = parentContainer.parentElement.parentElement.querySelector('.custom-select-wrapper .option-pill-btn.active');
    const activeScaleText = activeScaleBtn ? activeScaleBtn.innerText.split(' (')[0] : '10 Flowers';
    syncFlowerSpecText(activeScaleText);
}

function syncFlowerSpecText(scaleText) {
    const ribbonInput = document.getElementById('selected_ribbon_colors');
    const colorSelection = ribbonInput ? ribbonInput.value : 'Red';
    dynamicSpecLabels['flowers'] = `Scale: ${scaleText} | Colors: ${colorSelection}`;
}

// File Drag / Upload Parsing Feedback Loops
function refreshUploadStatus(fileInputElement, productId) {
    const labelNode = document.getElementById(`status-${productId}`);
    if (!labelNode) return;

    if (fileInputElement.files.length > 0) {
        labelNode.innerText = fileInputElement.files.length === 1 
            ? `Selected: ${fileInputElement.files[0].name}`
            : `Staged: ${fileInputElement.files.length} design files`;
        labelNode.style.color = "#3b82f6";
    } else {
        labelNode.innerText = "Upload design blueprint vectors";
        labelNode.style.color = "#64748b";
    }
}

// Production Rack Operations (Sidebar System Control)
function toggleCart(visibilityFlag) {
    const panel = document.getElementById('cartSidebarPanel');
    const overlay = document.getElementById('sidebarDimmer');
    if (!panel) return;

    if (visibilityFlag) {
        panel.classList.add('open');
        if (overlay) overlay.classList.add('active');
    } else {
        panel.classList.remove('open');
        if (overlay) overlay.classList.remove('active');
    }
}

function addAssetToCart(productId, productDisplayName) {
    const checkSpecProfile = dynamicSpecLabels[productId] || 'Standard Setup';
    const activePrice = dynamicPriceMatrix[productId] || 0.00;
    
    const localFileNode = document.getElementById(`file-${productId}`);
    let imagePathString = "";
    if (localFileNode && localFileNode.files && localFileNode.files[0]) {
        imagePathString = URL.createObjectURL(localFileNode.files[0]);
    }

    const standardMatchIndex = globalCartEngine.findIndex(item => 
        item.id === productId && item.specs === checkSpecProfile && item.price === activePrice
    );
    
    if (standardMatchIndex > -1) {
        globalCartEngine[standardMatchIndex].quantity += 1;
        if (imagePathString !== "") {
            globalCartEngine[standardMatchIndex].image_paths.push(imagePathString);
        }
    } else {
        const productCard = document.getElementById(`card-${productId}`);
        let targetAccent = 'purple-accent'; // Safe structural fallback
        
        if (productCard) {
            if (productCard.classList.contains('blue-accent')) targetAccent = 'blue-accent';
            else if (productCard.classList.contains('metal-accent')) targetAccent = 'metal-accent';
            else if (productCard.classList.contains('office-accent')) targetAccent = 'office-accent';
            else if (productCard.classList.contains('decor-accent')) targetAccent = 'decor-accent';
            else if (productCard.classList.contains('apparel-accent')) targetAccent = 'apparel-accent';
            else if (productCard.classList.contains('tech-accent')) targetAccent = 'tech-accent';
            else if (productCard.classList.contains('print-accent')) targetAccent = 'print-accent';
            else if (productCard.classList.contains('wood-accent')) targetAccent = 'wood-accent';
            else if (productCard.classList.contains('gift-accent')) targetAccent = 'gift-accent';
            else if (productCard.classList.contains('craft-accent')) targetAccent = 'craft-accent';
        }

        globalCartEngine.push({
            id: productId,
            name: productDisplayName,
            price: activePrice,
            specs: checkSpecProfile,
            quantity: 1,
            image_paths: imagePathString !== "" ? [imagePathString] : [],
            accent_theme: targetAccent
        });
    }

    refreshCartSidebarLayout();
    toggleCart(true);
}

function updateQuantityLoop(cartElementIndex, arithmeticModifier) {
    globalCartEngine[cartElementIndex].quantity += arithmeticModifier;
    if (globalCartEngine[cartElementIndex].quantity <= 0) {
        globalCartEngine.splice(cartElementIndex, 1);
    }
    refreshCartSidebarLayout();
}

function purgeCartLineItem(cartElementIndex) {
    globalCartEngine.splice(cartElementIndex, 1);
    refreshCartSidebarLayout();
}

function refreshCartSidebarLayout() {
    const listContainer = document.getElementById('cartDrawerList');
    const totalCountBadge = document.getElementById('cartCountGlobal');
    const sideCountSummary = document.getElementById('summaryItemsCount');
    const totalLedgerPrice = document.getElementById('summaryTotalQty');

    if (!listContainer) return;
    listContainer.innerHTML = '';
    
    let incrementalItemCount = 0;
    let netAggregateValue = 0.00;

    if (globalCartEngine.length === 0) {
        listContainer.innerHTML = '<div class="empty-cart-msg">Your configuration rack is empty.</div>';
    } else {
        globalCartEngine.forEach((item, index) => {
            incrementalItemCount += item.quantity;
            const itemRowSubtotal = item.price * item.quantity;
            netAggregateValue += itemRowSubtotal;

            const cartItemMarkup = `
                <div class="cart-item-card ${item.accent_theme}">
                    <div class="cart-item-meta-header">
                        <div style="flex: 1; min-width: 0; padding-right: 8px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <button type="button" class="cart-item-purge-x" onclick="purgeCartLineItem(${index})" title="Remove Item" style="background: none; border: none; color: #ef4444; font-size: 1.2rem; cursor: pointer; padding: 0; line-height: 1;">&times;</button>
                                <div class="cart-item-title" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.name}</div>
                            </div>
                            <div class="cart-item-spec-note">${item.specs}</div>
                        </div>
                        <div class="cart-item-price">$${itemRowSubtotal.toFixed(2)}</div>
                    </div>
                    ${item.image_paths.length > 0 ? `
                    <div class="cart-preview-images-row">
                        ${item.image_paths.map(path => `<img src="${path}" class="cart-preview-thumbnail" alt="Blueprint Preview">`).join('')}
                    </div>` : ''}
                    <div class="cart-item-qty-row" style="justify-content: flex-end; margin-top: 8px;">
                        <div class="cart-qty-inline-picker">
                            <button type="button" class="cart-qty-inline-btn" onclick="updateQuantityLoop(${index}, -1)">-</button>
                            <input type="text" class="cart-qty-inline-display" value="${item.quantity}" readonly>
                            <button type="button" class="cart-qty-inline-btn" onclick="updateQuantityLoop(${index}, 1)">+</button>
                        </div>
                    </div>
                </div>
            `;
            listContainer.insertAdjacentHTML('beforeend', cartItemMarkup);
        });
    }

    if (totalCountBadge) totalCountBadge.innerText = incrementalItemCount;
    if (sideCountSummary) sideCountSummary.innerText = `${incrementalItemCount} item${incrementalItemCount !== 1 ? 's' : ''}`;
    if (totalLedgerPrice) totalLedgerPrice.innerText = `$${netAggregateValue.toFixed(2)}`;
}

// Final Checkout Post Packaging Form Bridge Dispatcher
function submitToCheckoutDesk() {
    if (globalCartEngine.length === 0) {
        alert("Your production rack is empty. Please configure at least one base item.");
        return;
    }
    
    const dynamicProcessingPayload = globalCartEngine.map(item => ({
        name: item.name,
        specs: item.specs,
        price: item.price,
        quantity: item.quantity,
        image_path: item.image_paths.length > 0 ? item.image_paths[0] : ''
    }));

    const bridgeInput = document.getElementById('formCustomSpecsBridge');
    const checkoutForm = document.getElementById('checkoutProcessingForm');
    
    if (bridgeInput && checkoutForm) {
        bridgeInput.value = JSON.stringify(dynamicProcessingPayload);
        checkoutForm.submit();
    }
}
    </script>
</body>
</html>