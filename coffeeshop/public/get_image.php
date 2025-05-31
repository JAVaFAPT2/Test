<?php

// Include database and product object files
include_once 'config/database.php';
include_once 'classes/Product.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Prepare product object
$product = new Product($db);

// Get product id from URL
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: No product ID provided.');

// Get image data
$imageData = $product->getImage($id);

// Check if image data was retrieved
if ($imageData) {
    // Set the content type header - assuming JPEG images. Adjust if needed.
    header('Content-Type: image/jpeg');

    // Output the image data
    echo $imageData;
} else {
    // Image not found or an error occurred
    http_response_code(404);
    echo 'Image not found.';
}

?> 