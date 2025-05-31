<?php
// Set page title
$page_title = "Edit Product - Admin Dashboard";
// Include necessary files
include_once "../includes/header.php";
include_once "../config/database.php";
include_once "../includes/auth_check.php";
include_once "../classes/Product.php";
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

// Initialize product and category objects
$product = new Product($db);
$category = new Category($db);

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_msg'] = "Missing product ID.";
    header("Location: products.php");
    exit;
}

// Set product ID to be edited
$product->id = $_GET['id'];

// Read the existing product details
if (!$product->readOne()) {
    $_SESSION['error_msg'] = "Product not found.";
    header("Location: products.php");
    exit;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Set product properties
    $product->name = trim($_POST['name']);
    $product->description = trim($_POST['description']);
    $product->price = trim($_POST['price']);
    $product->image = trim($_POST['image']);
    $product->category_id = trim($_POST['category_id']);

    // Update product
    if ($product->update()) {
        $_SESSION['success_msg'] = "Product updated successfully.";
        header("Location: products.php");
        exit;
    } else {
        $_SESSION['error_msg'] = "Unable to update product.";
    }
}

// Get all categories for the dropdown
$category_stmt = $category->readAll();
$categories = array();
while ($row = $category_stmt->fetch(PDO::FETCH_ASSOC)) {
    $categories[] = $row;
}
?>

<div class="row">
    <!-- Admin Sidebar -->
    <div class="col-lg-3 mb-4">
        <div class="list-group">
            <a href="dashboard.php" class="list-group-item list-group-item-action">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
            <a href="products.php" class="list-group-item list-group-item-action active">
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
    </div>

    <!-- Admin Content -->
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Edit Product</h5>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $product->id); ?>" method="post" id="editProductForm">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="name" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product->name); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select a category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $product->category_id) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label">Price ($)</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0.01" value="<?php echo htmlspecialchars($product->price); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($product->description); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="image" class="form-label">Image URL</label>
                                <input type="url" class="form-control" id="image" name="image" value="<?php echo htmlspecialchars($product->image); ?>" required>
                                <div class="form-text">Enter a valid URL for the product image.</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Product Image Preview</label>
                                <div class="card">
                                    <img src="<?php echo htmlspecialchars($product->image); ?>" alt="<?php echo htmlspecialchars($product->name); ?>" class="img-fluid" id="image-preview">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <a href="products.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Products
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit product form validation
    const editProductForm = document.getElementById('editProductForm');

    if (editProductForm) {
        editProductForm.addEventListener('submit', function(event) {
            let isValid = true;

            // Validate product name
            const nameInput = document.getElementById('name');
            if (nameInput.value.trim() === '' || nameInput.value.length < 2 || nameInput.value.length > 100) {
                nameInput.classList.add('is-invalid');
                isValid = false;
                alert('Product name must be between 2-100 characters.');
            } else {
                nameInput.classList.remove('is-invalid');
            }

            // Validate price
            const priceInput = document.getElementById('price');
            if (priceInput.value <= 0) {
                priceInput.classList.add('is-invalid');
                isValid = false;
                alert('Price must be a positive number.');
            } else {
                priceInput.classList.remove('is-invalid');
            }

            // Validate image URL
            const imageInput = document.getElementById('image');
            if (!isValidUrl(imageInput.value)) {
                imageInput.classList.add('is-invalid');
                isValid = false;
                alert('Please enter a valid image URL.');
            } else {
                imageInput.classList.remove('is-invalid');
            }

            if (!isValid) {
                event.preventDefault();
            }
        });
    }

    // Update image preview when URL changes
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('image-preview');

    if (imageInput && imagePreview) {
        imageInput.addEventListener('input', function() {
            if (isValidUrl(this.value)) {
                imagePreview.src = this.value;
            }
        });
    }

    // Function to validate URL
    function isValidUrl(url) {
        try {
            new URL(url);
            return true;
        } catch (e) {
            return false;
        }
    }
});
</script>

<?php
// Include footer
include_once "../includes/footer.php";
?>
