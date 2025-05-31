<?php
// Set page title
$page_title = "Checkout - Brew Haven Coffee Shop";
// Include header
include_once "includes/header.php";
// Include database and authentication check
include_once "config/database.php";
include_once "includes/auth_check.php";
include_once "classes/Order.php";

// Require login for checkout
require_login();

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['error_msg'] = "Your cart is empty. Please add items before checking out.";
    header("Location: cart.php");
    exit;
}

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Calculate cart total
$cart_total = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_total += $item['price'] * $item['quantity'];
    }
}

// Process checkout form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form inputs
    $valid = true;
    $errors = array();

    // Validate name
    if (empty($_POST['name'])) {
        $valid = false;
        $errors[] = "Name is required";
    }

    // Validate email
    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $valid = false;
        $errors[] = "Valid email is required";
    }

    // Validate address
    if (empty($_POST['address'])) {
        $valid = false;
        $errors[] = "Address is required";
    }

    // Validate payment information (simplified for demo)
    if (empty($_POST['card_number']) || !preg_match('/^\d{16}$/', $_POST['card_number'])) {
        $valid = false;
        $errors[] = "Valid 16-digit card number is required";
    }

    if (empty($_POST['card_expiry']) || !preg_match('/^\d{2}\/\d{2}$/', $_POST['card_expiry'])) {
        $valid = false;
        $errors[] = "Valid expiry date (MM/YY) is required";
    }

    if (empty($_POST['card_cvv']) || !preg_match('/^\d{3,4}$/', $_POST['card_cvv'])) {
        $valid = false;
        $errors[] = "Valid CVV is required";
    }

    // If form is valid, process order
    if ($valid) {
        // Create new order
        $order = new Order($db);
        $order->user_id = $_SESSION['user_id'];
        $order->total_amount = $cart_total;
        $order->items = $_SESSION['cart'];

        // Create the order
        if ($order->create()) {
            // Clear the cart
            unset($_SESSION['cart']);

            // Set success message
            $_SESSION['success_msg'] = "Order placed successfully! Your order ID is " . $order->id;

            // Redirect to order confirmation
            header("Location: order_confirmation.php?id=" . $order->id);
            exit;
        } else {
            $_SESSION['error_msg'] = "Failed to place order. Please try again.";
        }
    } else {
        // Set error messages
        $_SESSION['error_msg'] = "Please correct the following errors:<br>" . implode("<br>", $errors);
    }
}
?>

<div class="row">
    <div class="col-lg-8">
        <h2 class="mb-4">Checkout</h2>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="checkout-form">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Contact Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Shipping Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" name="address" value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control" id="state" name="state" value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="zip" class="form-label">ZIP Code</label>
                            <input type="text" class="form-control" id="zip" name="zip" value="<?php echo isset($_POST['zip']) ? htmlspecialchars($_POST['zip']) : ''; ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Payment Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="card_number" class="form-label">Card Number</label>
                        <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="card_expiry" class="form-label">Expiry Date</label>
                            <input type="text" class="form-control" id="card_expiry" name="card_expiry" placeholder="MM/YY" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="card_cvv" class="form-label">CVV</label>
                            <input type="text" class="form-control" id="card_cvv" name="card_cvv" placeholder="123" required>
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="save_card" name="save_card">
                        <label class="form-check-label" for="save_card">
                            Save card information for future purchases
                        </label>
                    </div>
                    <p class="text-muted small">
                        <i class="fas fa-lock me-1"></i> Your payment information is encrypted and secure.
                    </p>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success btn-lg">Place Order</button>
                <a href="cart.php" class="btn btn-outline-secondary">Return to Cart</a>
            </div>
        </form>
    </div>

    <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Order Summary</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th class="text-end">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['cart'] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td class="text-end">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span>$<?php echo number_format($cart_total, 2); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Tax (7%):</span>
                    <span>$<?php echo number_format($cart_total * 0.07, 2); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Shipping:</span>
                    <span>$<?php echo number_format(5.00, 2); ?></span>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-2 fw-bold">
                    <span>Total:</span>
                    <span>$<?php echo number_format($cart_total + ($cart_total * 0.07) + 5.00, 2); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Client-side validation using JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkout-form');

    form.addEventListener('submit', function(event) {
        let isValid = true;

        // Validate card number (16 digits)
        const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
        if (!cardNumber.match(/^\d{16}$/)) {
            isValid = false;
            document.getElementById('card_number').classList.add('is-invalid');
            alert('Please enter a valid 16-digit card number.');
        } else {
            document.getElementById('card_number').classList.remove('is-invalid');
        }

        // Validate expiry date (MM/YY format)
        const cardExpiry = document.getElementById('card_expiry').value;
        if (!cardExpiry.match(/^\d{2}\/\d{2}$/)) {
            isValid = false;
            document.getElementById('card_expiry').classList.add('is-invalid');
            alert('Please enter a valid expiry date in MM/YY format.');
        } else {
            document.getElementById('card_expiry').classList.remove('is-invalid');
        }

        // Validate CVV (3-4 digits)
        const cardCvv = document.getElementById('card_cvv').value;
        if (!cardCvv.match(/^\d{3,4}$/)) {
            isValid = false;
            document.getElementById('card_cvv').classList.add('is-invalid');
            alert('Please enter a valid 3 or 4-digit CVV code.');
        } else {
            document.getElementById('card_cvv').classList.remove('is-invalid');
        }

        if (!isValid) {
            event.preventDefault();
        }
    });

    // Format card number as user types
    const cardNumberInput = document.getElementById('card_number');
    cardNumberInput.addEventListener('input', function(e) {
        // Remove non-digit characters
        let value = this.value.replace(/\D/g, '');

        // Add spaces every 4 digits
        if (value.length > 0) {
            value = value.match(new RegExp('.{1,4}', 'g')).join(' ');
        }

        // Update input value
        this.value = value;
    });

    // Format expiry date as user types
    const expiryInput = document.getElementById('card_expiry');
    expiryInput.addEventListener('input', function(e) {
        // Remove non-digit characters
        let value = this.value.replace(/\D/g, '');

        // Add slash after 2 digits
        if (value.length > 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }

        // Update input value
        this.value = value;
    });
});
</script>

<?php
// Include footer
include_once "includes/footer.php";
?>
