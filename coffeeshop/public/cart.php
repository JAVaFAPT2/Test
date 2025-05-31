<?php
// Set page title
$page_title = "Your Cart - Brew Haven Coffee Shop";
// Include header
include_once "includes/header.php";
// Include database and product class
include_once "config/database.php";
include_once "classes/Product.php";

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize product object
$product = new Product($db);

// Process cart item removal
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Remove item from cart
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['product_id'] == $product_id) {
                unset($_SESSION['cart'][$key]);
                // Reindex array
                $_SESSION['cart'] = array_values($_SESSION['cart']);
                $_SESSION['success_msg'] = "Item removed from cart.";
                break;
            }
        }
    }

    // Redirect back to cart page
    header("Location: cart.php");
    exit;
}

// Process quantity update
if (isset($_POST['update_cart'])) {
    if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {
        foreach ($_POST['quantity'] as $product_id => $quantity) {
            // Ensure quantity is positive integer
            $quantity = max(1, (int)$quantity);

            // Update cart
            if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $key => $item) {
                    if ($item['product_id'] == $product_id) {
                        $_SESSION['cart'][$key]['quantity'] = $quantity;
                        break;
                    }
                }
            }
        }

        $_SESSION['success_msg'] = "Cart updated successfully.";
        // Redirect to refresh page
        header("Location: cart.php");
        exit;
    }
}

// Calculate cart total
$cart_total = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_total += $item['price'] * $item['quantity'];
    }
}
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="mb-4">Your Shopping Cart</h1>

        <?php if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])): ?>
            <div class="alert alert-info">
                <p>Your cart is empty. <a href="menu.php">Continue shopping</a> to add items to your cart.</p>
            </div>
        <?php else: ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['cart'] as $item): ?>
                                <tr>
                                    <td>
                                        <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                    </td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <input type="number" name="quantity[<?php echo $item['product_id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" max="10" class="form-control" style="width: 80px;">
                                    </td>
                                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    <td>
                                        <a href="cart.php?action=remove&id=<?php echo $item['product_id']; ?>" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Remove
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th>$<?php echo number_format($cart_total, 2); ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="menu.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                    </a>
                    <div>
                        <button type="submit" name="update_cart" class="btn btn-secondary me-2">
                            <i class="fas fa-sync-alt me-2"></i>Update Cart
                        </button>
                        <a href="checkout.php" class="btn btn-success">
                            <i class="fas fa-shopping-cart me-2"></i>Proceed to Checkout
                        </a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
include_once "includes/footer.php";
?>
