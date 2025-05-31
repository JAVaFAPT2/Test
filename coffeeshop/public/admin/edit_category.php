<?php
// Set page title
$page_title = "Edit Category - Admin Dashboard";
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

// Check if category ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_msg'] = "Missing category ID.";
    header("Location: categories.php");
    exit;
}

// Set category ID to be edited
$category->id = $_GET['id'];

// Read the existing category details
if (!$category->readOne()) {
    $_SESSION['error_msg'] = "Category not found.";
    header("Location: categories.php");
    exit;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Set category properties
    $category->name = trim($_POST['name']);
    $category->description = trim($_POST['description']);

    // Update category
    if ($category->update()) {
        $_SESSION['success_msg'] = "Category updated successfully.";
        header("Location: categories.php");
        exit;
    } else {
        $_SESSION['error_msg'] = "Unable to update category.";
    }
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
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Edit Category</h5>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $category->id); ?>" method="post" id="editCategoryForm">
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($category->name); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($category->description); ?></textarea>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <a href="categories.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Categories
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit category form validation
    const editCategoryForm = document.getElementById('editCategoryForm');

    if (editCategoryForm) {
        editCategoryForm.addEventListener('submit', function(event) {
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
