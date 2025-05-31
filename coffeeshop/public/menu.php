<?php
$page_title = "VelVet Coffee - Menu";
include 'includes/header.php';

// Include database and product/category classes
include_once "config/database.php";
include_once "classes/Product.php";
include_once "classes/Category.php";

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize product and category objects
$product = new Product($db);
$category = new Category($db);

// Get categories
$cat_stmt = $category->readAll();

// Handle category filter
$current_category = null;
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $product->category_id = $_GET['category'];
    $stmt = $product->readByCategory();

    // Get category name for display
    $category->id = $_GET['category'];
    if ($category->readOne()) {
        $current_category = $category->name;
    }
} else {
    // Get all products if no category selected
    $stmt = $product->readAll();
}

// Handle search
$search_term = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $_GET['search'];
    $stmt = $product->search($search_term);
}
?>

<div class="row">
    <!-- Categories Sidebar -->
    <div class="col-lg-3 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Categories</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="menu.php" class="list-group-item list-group-item-action <?php echo !isset($_GET['category']) ? 'active' : ''; ?>">
                    All Products
                </a>
                <?php while ($cat_row = $cat_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <a href="menu.php?category=<?php echo $cat_row['id']; ?>" class="list-group-item list-group-item-action <?php echo (isset($_GET['category']) && $_GET['category'] == $cat_row['id']) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat_row['name']); ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Search Form -->
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Search</h5>
            </div>
            <div class="card-body">
                <form action="menu.php" method="get">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search_term); ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="col-lg-9">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <?php
                if ($search_term) {
                    echo "Search Results for: " . htmlspecialchars($search_term);
                } elseif ($current_category) {
                    echo htmlspecialchars($current_category);
                } else {
                    echo "All Products";
                }
                ?>
            </h2>
        </div>

        <!-- Products -->
        <div class="row">
            <?php
            $count = 0;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                $count++;
            ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 product-card">
                        <img src="<?php echo htmlspecialchars($row['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($row['category_name']); ?></p>
                            <p class="card-text"><?php echo substr(htmlspecialchars($row['description']), 0, 100) . '...'; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 mb-0">$<?php echo number_format($row['price'], 2); ?></span>
                                <button class="btn btn-primary add-to-cart" data-id="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>" data-price="<?php echo $row['price']; ?>">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>

            <?php if ($count === 0): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <p>No products found. Please try a different category or search term.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Shopping Cart JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add to Cart Functionality
    const addToCartButtons = document.querySelectorAll('.add-to-cart');

    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const productName = this.getAttribute('data-name');
            const productPrice = this.getAttribute('data-price');

            // Call function to add to cart
            addToCart(productId, productName, productPrice);
        });
    });

    // Function to add item to cart
    function addToCart(id, name, price) {
        // Use AJAX to add to cart without page refresh
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'add_to_cart.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function() {
            if (this.status === 200) {
                const response = JSON.parse(this.responseText);
                if (response.success) {
                    // Update cart count in navbar
                    document.getElementById('cart-count').textContent = response.cart_count;

                    // Show success message
                    alert(`${name} added to your cart!`);
                } else {
                    alert(response.message);
                }
            }
        };

        xhr.send(`product_id=${id}&quantity=1&ajax=1`);
    }
});
</script>

<?php include 'includes/footer.php'; ?>
