<?php
// Set page title
$page_title = "Order Details - Admin Dashboard";
// Include necessary files
include_once "../includes/header.php";
include_once "../config/database.php";
include_once "../includes/auth_check.php";
include_once "../classes/Order.php";

// Require admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_msg'] = "You do not have permission to access the admin dashboard.";
    header("Location: ../index.php");
    exit;
}

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize order object
$order = new Order($db);

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_msg'] = "Missing order ID.";
    header("Location: orders.php");
    exit;
}

// Set order ID
$order->id = $_GET['id'];

// Get order details
if (!$order->readOne()) {
    $_SESSION['error_msg'] = "Order not found.";
    header("Location: orders.php");
    exit;
}
?>

<div class="row">
    <!-- Admin Sidebar -->
    <div class="col-lg-3 mb-4">
        <div class="list-group">
            <a href="dashboard.php" class="list-group-item list-group-item-action">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
            <a href="products.php" class="list-group-item list-group-item-action">
                <i class="fas fa-coffee me-2"></i>Products
            </a>
            <a href="categories.php" class="list-group-item list-group-item-action">
                <i class="fas fa-tags me-2"></i>Categories
            </a>
            <a href="orders.php" class="list-group-item list-group-item-action active">
                <i class="fas fa-shopping-cart me-2"></i>Orders
            </a>
            <a href="users.php" class="list-group-item list-group-item-action">
                <i class="fas fa-users me-2"></i>Users
            </a>
            <a href="../index.php" class="list-group-item list-group-item-action">
                <i class="fas fa-store me-2"></i>View Shop
            </a>
        </div>
    </div>

    <!-- Admin Content -->
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Order #<?php echo $order->id; ?></h2>
            <div>
                <a href="orders.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left me-2"></i>Back to Orders
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateStatusModal" data-order-id="<?php echo $order->id; ?>" data-order-status="<?php echo $order->status; ?>">
                    <i class="fas fa-edit me-2"></i>Update Status
                </button>
            </div>
        </div>

        <!-- Order Details -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Order Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Order ID:</strong> #<?php echo $order->id; ?></p>
                        <p><strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order->created)); ?></p>
                        <p>
                            <strong>Status:</strong>
                            <span class="badge bg-<?php
                                switch($order->status) {
                                    case 'pending': echo 'warning'; break;
                                    case 'processing': echo 'primary'; break;
                                    case 'completed': echo 'success'; break;
                                    case 'cancelled': echo 'danger'; break;
                                    default: echo 'secondary';
                                }
                            ?>">
                                <?php echo ucfirst($order->status); ?>
                            </span>
                        </p>
                        <p><strong>Total Amount:</strong> $<?php echo number_format($order->total_amount, 2); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Customer Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Customer ID:</strong> <?php echo $order->user_id; ?></p>
                        <p><strong>Email:</strong> <?php echo isset($order->email) ? htmlspecialchars($order->email) : 'Not available'; ?></p>

                        <hr>

                        <a href="view_user.php?id=<?php echo $order->user_id; ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user me-2"></i>View Customer Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Order Items</h5>
            </div>
            <div class="card-body">
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
            </div>
        </div>

        <!-- Order Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Order Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" onclick="printOrder()">
                        <i class="fas fa-print me-2"></i>Print Order
                    </button>
                    <a href="mailto:<?php echo isset($order->email) ? htmlspecialchars($order->email) : ''; ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-envelope me-2"></i>Email Customer
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="updateStatusModalLabel">Update Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="update_order_status.php" method="post" id="updateStatusForm">
                    <input type="hidden" id="order_id" name="order_id" value="<?php echo $order->id; ?>">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending" <?php echo $order->status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $order->status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="completed" <?php echo $order->status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $order->status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function printOrder() {
    window.print();
}

document.addEventListener('DOMContentLoaded', function() {
    // Set order ID and current status in modal when opened
    const updateStatusModal = document.getElementById('updateStatusModal');
    if (updateStatusModal) {
        updateStatusModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const orderId = button.getAttribute('data-order-id');
            const orderStatus = button.getAttribute('data-order-status');

            const orderIdInput = document.getElementById('order_id');
            const statusSelect = document.getElementById('status');

            orderIdInput.value = orderId;
            statusSelect.value = orderStatus;
        });
    }
});
</script>

<?php
// Include footer
include_once "../includes/footer.php";
?>
