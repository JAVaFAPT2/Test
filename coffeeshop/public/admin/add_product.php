<?php
// Include necessary files
include_once "../config/database.php";
include_once "../classes/Product.php";
include_once "../includes/auth_check.php";

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_msg'] = "You do not have permission to access the admin dashboard.";
    header("Location: ../index.php");
    exit;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();

    // Initialize product object
    $product = new Product($db);

    // Set product properties
    $product->name = trim($_POST['name']);
    $product->description = trim($_POST['description']);
    $product->price = trim($_POST['price']);
    $product->image = trim($_POST['image']);
    $product->category_id = trim($_POST['category_id']);

    // Create product
    if ($product->create()) {
        $_SESSION['success_msg'] = "Product was created successfully.";
    } else {
        $_SESSION['error_msg'] = "Unable to create product.";
    }

    // Redirect to products page
    header("Location: products.php");
    exit;
} else {
    // If not a POST request, redirect to products page
    header("Location: products.php");
    exit;
}
?>
