<?php
// Set page title
$page_title = "Order Confirmation - Brew Haven Coffee Shop";
// Include header
include_once "includes/header.php";
// Include database and authentication check
include_once "config/database.php";
include_once "includes/auth_check.php";
include_once "classes/Order.php";

// Require login
require_login();

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_msg'] = "Invalid order ID.";
    header("Location: index.php");
    exit;
}

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize order object
$order = new Order($db);
$order->id = $_GET['id'];

// Get order details
if (!$order->readOne()) {
    $_SESSION['error_msg'] = "Order not found.";
    header("Location: index.php");
    exit;
}

// Check if order belongs to current user
if ($order->user_id != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
    $_SESSION['error_msg'] = "You do not have permission to view this order.";
    header("Location: index.php");
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                    <h2>Thank You for Your Order!</h2>
                    <p class="lead">Your order has been received and is being processed.</p>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Order Information</h5>
                        <p>
                            <strong>Order ID:</strong> #<?php echo $order->id; ?><br>
                            <strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order->created)); ?><br>
                            <strong>Status:</strong> <span class="badge bg-primary"><?php echo ucfirst($order->status); ?></span><br>
                            <strong>Total:</strong> $<?php echo number_format($order->total_amount, 2); ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h5>Customer Information</h5>
                        <p>
                            <strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['name']); ?><br>
                            <strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email'] ?? 'Not available'); ?><br>
                        </p>
                    </div>
                </div>

                <h5>Order Details</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order->items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td class="text-end">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th class="text-end">$<?php echo number_format($order->total_amount, 2); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="alert alert-info mt-4">
                    <p class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        An email confirmation has been sent to your email address with your order details.
                    </p>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="orders.php" class="btn btn-outline-primary">
                        <i class="fas fa-list me-2"></i>View All Orders
                    </a>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once "includes/footer.php";
?>
