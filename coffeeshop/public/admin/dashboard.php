<?php
// Set page title
$page_title = "Admin Dashboard - Brew Haven Coffee Shop";
// Include necessary files
include_once "../includes/header.php";
include_once "../config/database.php";
include_once "../includes/auth_check.php";
include_once "../classes/Product.php";
include_once "../classes/Category.php";
include_once "../classes/Order.php";
include_once "../classes/User.php";

// Require admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_msg'] = "You do not have permission to access the admin dashboard.";
    header("Location: ../index.php");
    exit;
}

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$product = new Product($db);
$category = new Category($db);
$order = new Order($db);

// Get counts
$product_stmt = $product->readAll();
$product_count = $product_stmt->rowCount();

$category_stmt = $category->readAll();
$category_count = $category_stmt->rowCount();

$order_stmt = $order->readAll();
$order_count = $order_stmt->rowCount();

// Get recent orders (latest 5)
$recent_orders = array();
$counter = 0;
while ($row = $order_stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($counter < 5) {
        $recent_orders[] = $row;
    }
    $counter++;
}
?>

<div class="row">
    <!-- Admin Sidebar -->
    <div class="col-lg-3 mb-4">
        <div class="list-group">
            <a href="dashboard.php" class="list-group-item list-group-item-action active">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
            <a href="products.php" class="list-group-item list-group-item-action">
                <i class="fas fa-coffee me-2"></i>Products
            </a>
            <a href="categories.php" class="list-group-item list-group-item-action">
                <i class="fas fa-tags me-2"></i>Categories
            </a>
            <a href="orders.php" class="list-group-item list-group-item-action">
                <i class="fas fa-shopping-cart me-2"></i>Orders
            </a>
            <a href="users.php" class="list-group-item list-group-item-action">
                <i class="fas fa-users me-2"></i>Users
            </a>
            <a href="../index.php" class="list-group-item list-group-item-action">
                <i class="fas fa-store me-2"></i>View Shop
            </a>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Admin Info</h5>
            </div>
            <div class="card-body">
                <h5><?php echo htmlspecialchars($_SESSION['name']); ?></h5>
                <p class="mb-2"><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <p class="mb-0"><strong>Role:</strong> Administrator</p>
            </div>
        </div>
    </div>

    <!-- Admin Content -->
    <div class="col-lg-9">
        <h2 class="mb-4">Admin Dashboard</h2>

        <!-- Stats Cards -->
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <h5 class="card-title">Products</h5>
                        <p class="card-text display-4"><?php echo $product_count; ?></p>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="products.php" class="text-white text-decoration-none">View Details</a>
                        <i class="fas fa-angle-right"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <h5 class="card-title">Categories</h5>
                        <p class="card-text display-4"><?php echo $category_count; ?></p>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="categories.php" class="text-white text-decoration-none">View Details</a>
                        <i class="fas fa-angle-right"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body">
                        <h5 class="card-title">Orders</h5>
                        <p class="card-text display-4"><?php echo $order_count; ?></p>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="orders.php" class="text-white text-decoration-none">View Details</a>
                        <i class="fas fa-angle-right"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Recent Orders</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_orders)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No orders found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_orders as $order_item): ?>
                                    <tr>
                                        <td>#<?php echo $order_item['id']; ?></td>
                                        <td><?php echo htmlspecialchars($order_item['username']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($order_item['created'])); ?></td>
                                        <td>$<?php echo number_format($order_item['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php
                                                switch($order_item['status']) {
                                                    case 'pending': echo 'warning'; break;
                                                    case 'processing': echo 'primary'; break;
                                                    case 'completed': echo 'success'; break;
                                                    case 'cancelled': echo 'danger'; break;
                                                    default: echo 'secondary';
                                                }
                                            ?>">
                                                <?php echo ucfirst($order_item['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="order_detail.php?id=<?php echo $order_item['id']; ?>" class="btn btn-sm btn-info">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="orders.php" class="btn btn-outline-primary">View All Orders</a>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="add_product.php" class="btn btn-outline-primary">
                                <i class="fas fa-plus me-2"></i>Add New Product
                            </a>
                            <a href="add_category.php" class="btn btn-outline-success">
                                <i class="fas fa-plus me-2"></i>Add New Category
                            </a>
                            <a href="orders.php?status=pending" class="btn btn-outline-warning">
                                <i class="fas fa-clock me-2"></i>View Pending Orders
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Shop Overview</h5>
                    </div>
                    <div class="card-body">
                        <p>Welcome to the admin dashboard. Here you can manage your products, categories, and orders.</p>
                        <p>Use the sidebar to navigate between different sections.</p>
                        <p><strong>Last Login:</strong> <?php echo date('F j, Y, g:i a'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once "../includes/footer.php";
?>
