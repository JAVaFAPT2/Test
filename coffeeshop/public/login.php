<?php
// Set page title
$page_title = "Login - Brew Haven Coffee Shop";
// Include header
include_once "includes/header.php";
// Include database and user class
include_once "config/database.php";
include_once "classes/User.php";

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Initialize variables
$email = "";
$error = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();

    // Initialize user object
    $user = new User($db);

    // Set user properties
    $user->email = trim($_POST['email']);
    $user->password = trim($_POST['password']);

    // Attempt login
    if ($user->login()) {
        // Redirect based on user role
        if ($_SESSION['role'] === 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            // If there is a redirect URL in the session (from requiring login)
            if (isset($_SESSION['redirect_url'])) {
                $redirect = $_SESSION['redirect_url'];
                unset($_SESSION['redirect_url']);
                header("Location: $redirect");
            } else {
                header("Location: index.php");
            }
        }
        exit;
    }

    // Store email for form re-population
    $email = htmlspecialchars($user->email);
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow mt-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Login to Your Account</h4>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="login-form">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
                        <label class="form-check-label" for="remember_me">
                            Remember me
                        </label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Login</button>
                    </div>
                    <div class="text-center mt-3">
                        <a href="forgot-password.php">Forgot your password?</a>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>

        <!-- Demo account info -->
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title text-center">Demo Admin Account</h5>
                <p class="card-text text-center">Email: admin@coffeeshop.com<br>Password: Admin123</p>
            </div>
        </div>
    </div>
</div>

<script>
// Client-side validation using JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('login-form');

    form.addEventListener('submit', function(event) {
        let isValid = true;

        // Validate email
        const email = document.getElementById('email').value.trim();
        if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            isValid = false;
            document.getElementById('email').classList.add('is-invalid');
            alert('Please enter a valid email address.');
        } else {
            document.getElementById('email').classList.remove('is-invalid');
        }

        // Validate password (not empty)
        const password = document.getElementById('password').value;
        if (password.length === 0) {
            isValid = false;
            document.getElementById('password').classList.add('is-invalid');
            alert('Please enter your password.');
        } else {
            document.getElementById('password').classList.remove('is-invalid');
        }

        if (!isValid) {
            event.preventDefault();
        }
    });
});
</script>

<?php
// Include footer
include_once "includes/footer.php";
?>
