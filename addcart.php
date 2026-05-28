<?php
session_start();

// Disable HTML rendering since this script only communicates back via clean JSON responses
header('Content-Type: application/json');

// Guardrail: Force user session confirmation before checking out
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'authentication_required']);
    exit();
}

// Read the raw JSON input stream sent from the fetch request
$raw_input = file_get_contents('php://input');
$decoded_data = json_decode($raw_input, true);

if (isset($decoded_data['cart_items']) && !empty($decoded_data['cart_items'])) {
    
    $compiled_product_names = [];
    $compiled_specs = [];
    $total_quantity = 0;
    $grand_total_price = 0.00;
    $all_images = [];

    // Loop through each product currently resting inside the cart array stack
    foreach ($decoded_data['cart_items'] as $item) {
        $compiled_product_names[] = isset($item['name']) ? $item['name'] : 'Studio Asset';
        $compiled_specs[]         = isset($item['specifications']) ? $item['specifications'] : 'Standard';
        
        $qty   = isset($item['quantity']) ? intval($item['quantity']) : 1;
        $price = isset($item['price']) ? floatval($item['price']) : 0.00;

        $total_quantity    += $qty;
        $grand_total_price += ($price * $qty);

        if (isset($item['image']) && !empty($item['image'])) {
            $all_images[] = $item['image'];
        }
    }

    // Bind all individual item metrics neatly into one single global order summary payload
    $_SESSION['pending_order'] = [
        'product_name'   => implode(", ", array_unique($compiled_product_names)),
        'specifications' => implode(" | ", $compiled_specs),
        'quantity'       => $total_quantity,
        'total_price'    => $grand_total_price,
        'images'         => $all_images
    ];

    echo json_encode(['success' => true]);
    exit();
} else {
    echo json_encode(['success' => false, 'error' => 'empty_payload', 'message' => 'No active items found in transmission.']);
    exit();
}
?>