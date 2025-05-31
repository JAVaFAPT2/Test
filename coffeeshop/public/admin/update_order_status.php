<?php
// Include necessary files
include_once "../config/database.php";
include_once "../includes/auth_check.php";
include_once "../classes/Order.php";

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

    // Initialize order object
    $order = new Order($db);

    // Set order properties
    $order->id = trim($_POST['order_id']);
    $order->status = trim($_POST['status']);

    // Update order status
    if ($order->updateStatus()) {
        $_SESSION['success_msg'] = "Order status updated successfully.";
    } else {
        $_SESSION['error_msg'] = "Unable to update order status.";
    }

    // Redirect back to orders page
    header("Location: orders.php");
    exit;
} else {
    // If not a POST request, redirect to orders page
    header("Location: orders.php");
    exit;
}
?>
