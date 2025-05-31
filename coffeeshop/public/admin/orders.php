<?php
// Set page title
$page_title = "Manage Orders - Admin Dashboard";
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

// Get all orders
$stmt = $order->readAll();

// Status filter
$status_filter = '';
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status_filter = $_GET['status'];
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

        <!-- Status Filter -->
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Filter by Status</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="orders.php" class="list-group-item list-group-item-action <?php echo !$status_filter ? 'active' : ''; ?>">
                        All Orders
                    </a>
                    <a href="orders.php?status=pending" class="list-group-item list-group-item-action <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">
                        Pending
                    </a>
                    <a href="orders.php?status=processing" class="list-group-item list-group-item-action <?php echo $status_filter === 'processing' ? 'active' : ''; ?>">
                        Processing
                    </a>
                    <a href="orders.php?status=completed" class="list-group-item list-group-item-action <?php echo $status_filter === 'completed' ? 'active' : ''; ?>">
                        Completed
                    </a>
                    <a href="orders.php?status=cancelled" class="list-group-item list-group-item-action <?php echo $status_filter === 'cancelled' ? 'active' : ''; ?>">
                        Cancelled
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Content -->
    <div class="col-lg-9">
        <h2 class="mb-4">
            <?php
            if ($status_filter) {
                echo ucfirst($status_filter) . " Orders";
            } else {
                echo "All Orders";
            }
            ?>
        </h2>

        <!-- Orders Table -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $count = 0;
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                                // Apply status filter if set
                                if ($status_filter && $row['status'] !== $status_filter) {
                                    continue;
                                }
                                $count++;
                            ?>
                                <tr>
                                    <td>#<?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['created'])); ?></td>
                                    <td>$<?php echo number_format($row['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php
                                            switch($row['status']) {
                                                case 'pending': echo 'warning'; break;
                                                case 'processing': echo 'primary'; break;
                                                case 'completed': echo 'success'; break;
                                                case 'cancelled': echo 'danger'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="order_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#updateStatusModal" data-order-id="<?php echo $row['id']; ?>" data-order-status="<?php echo $row['status']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>

                            <?php if ($count === 0): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No orders found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
                    <input type="hidden" id="order_id" name="order_id">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
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
