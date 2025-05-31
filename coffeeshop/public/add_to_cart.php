<?php
// Start the session
session_start();

// Include database and product class
include_once "config/database.php";
include_once "classes/Product.php";

// Set default response
$response = array(
    'success' => false,
    'message' => 'Unknown error occurred',
    'cart_count' => 0
);

// Check if request contains product_id and quantity
if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();

    // Initialize product object
    $product = new Product($db);
    $product->id = $_POST['product_id'];

    // Verify product exists
    if ($product->readOne()) {
        // Initialize cart if it doesn't exist
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }

        // Get product ID and quantity
        $product_id = $_POST['product_id'];
        $quantity = (int)$_POST['quantity'];

        // Ensure quantity is positive
        if ($quantity <= 0) {
            $quantity = 1;
        }

        // Check if product already exists in cart
        $product_exists = false;
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['product_id'] == $product_id) {
                // Update quantity
                $_SESSION['cart'][$key]['quantity'] += $quantity;
                $product_exists = true;
                break;
            }
        }

        // If product does not exist in cart, add it
        if (!$product_exists) {
            // Add product to cart
            $_SESSION['cart'][] = array(
                'product_id' => $product_id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity
            );
        }

        // Set success response
        $response['success'] = true;
        $response['message'] = 'Product added to cart';
        $response['cart_count'] = count($_SESSION['cart']);
    } else {
        $response['message'] = 'Product not found';
    }
} else {
    $response['message'] = 'Invalid request';
}

// Check if it's an AJAX request
if (isset($_POST['ajax']) && $_POST['ajax'] == 1) {
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    // Set message in session
    if ($response['success']) {
        $_SESSION['success_msg'] = 'Product added to cart!';
    } else {
        $_SESSION['error_msg'] = $response['message'];
    }

    // Redirect back to previous page
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
?>
