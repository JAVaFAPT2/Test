<?php
// Check if user is logged in
function is_logged_in() {
    // Check if user_id session variable exists
    return isset($_SESSION['user_id']);
}

// Redirect to login page if not logged in
function require_login() {
    if (!is_logged_in()) {
        // Store current URL for redirect after login
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

        // Set error message
        $_SESSION['error_msg'] = "Please log in to access this page.";

        // Redirect to login page
        header("Location: login.php");
        exit;
    }
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Redirect to home page if not admin
function require_admin() {
    if (!is_logged_in() || !is_admin()) {
        // Set error message
        $_SESSION['error_msg'] = "You don't have permission to access that page.";

        // Redirect to home page
        header("Location: index.php");
        exit;
    }
}
?>
