<?php
// Set page title
$page_title = "Manage Categories - Admin Dashboard";
// Include necessary files
include_once "../includes/header.php";
include_once "../config/database.php";
include_once "../includes/auth_check.php";
include_once "../classes/Category.php";

// Require admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_msg'] = "You do not have permission to access the admin dashboard.";
    header("Location: ../index.php");
    exit;
}

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize category object
$category = new Category($db);

// Process delete request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $category->id = $_GET['id'];
    if ($category->delete()) {
        $_SESSION['success_msg'] = "Category deleted successfully.";
    } else {
        $_SESSION['error_msg'] = "Unable to delete category. Make sure it has no associated products.";
    }
    header("Location: categories.php");
    exit;
}

// Process category creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_category'])) {
    // Set category properties
    $category->name = trim($_POST['name']);
    $category->description = trim($_POST['description']);

    // Create category
    if ($category->create()) {
        $_SESSION['success_msg'] = "Category created successfully.";
    } else {
        $_SESSION['error_msg'] = "Unable to create category.";
    }
    header("Location: categories.php");
    exit;
}

// Get all categories
$stmt = $category->readAll();
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
            <a href="categories.php" class="list-group-item list-group-item-action active">
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
    </div>

    <!-- Admin Content -->
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Categories</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fas fa-plus me-2"></i>Add New Category
            </button>
        </div>

        <!-- Categories Table -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $count = 0;
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                                $count++;
                            ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td>
                                        <a href="edit_category.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="categories.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this category? All associated products will be affected.');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>

                            <?php if ($count === 0): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No categories found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="addCategoryForm">
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="d-grid">
                        <input type="hidden" name="add_category" value="1">
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add category form validation
    const addCategoryForm = document.getElementById('addCategoryForm');

    if (addCategoryForm) {
        addCategoryForm.addEventListener('submit', function(event) {
            let isValid = true;

            // Validate category name
            const nameInput = document.getElementById('name');
            if (nameInput.value.trim() === '' || nameInput.value.length < 2 || nameInput.value.length > 100) {
                nameInput.classList.add('is-invalid');
                isValid = false;
                alert('Category name must be between 2-100 characters.');
            } else {
                nameInput.classList.remove('is-invalid');
            }

            if (!isValid) {
                event.preventDefault();
            }
        });
    }
});
</script>

<?php
// Include footer
include_once "../includes/footer.php";
?>
