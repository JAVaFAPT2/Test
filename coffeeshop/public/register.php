<?php
// Set page title
$page_title = "Register - Brew Haven Coffee Shop";
// Include header
include_once "includes/header.php";
// Include database and user class
include_once "config/database.php";
include_once "classes/User.php";

// Initialize variables
$username = $email = $name = "";
$error = "";
$success = false;

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();

    // Initialize user object
    $user = new User($db);

    // Set user properties
    $user->username = trim($_POST['username']);
    $user->email = trim($_POST['email']);
    $user->password = trim($_POST['password']);
    $user->name = trim($_POST['name']);

    // Validate password confirmation
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $_SESSION['error_msg'] = "Passwords do not match.";
    } else {
        // Create the user
        if ($user->register()) {
            $_SESSION['success_msg'] = "Registration successful! You can now login.";
            header("Location: login.php");
            exit;
        }
    }

    // Set variables for form re-population
    $username = htmlspecialchars($user->username);
    $email = htmlspecialchars($user->email);
    $name = htmlspecialchars($user->name);
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow mt-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Create an Account</h4>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="register-form">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo $username; ?>" required>
                        <div class="form-text">Username must be 4-20 alphanumeric characters.</div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>" required>
                        <div class="form-text">Your name will be used for your orders and receipts.</div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">Password must be at least 8 characters and include at least one letter and one number.</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="agree_terms" name="agree_terms" required>
                        <label class="form-check-label" for="agree_terms">
                            I agree to the <a href="terms.php">Terms and Conditions</a> and <a href="privacy.php">Privacy Policy</a>
                        </label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Register</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>
</div>

<script>
// Client-side validation using JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('register-form');

    form.addEventListener('submit', function(event) {
        let isValid = true;

        // Validate username
        const username = document.getElementById('username').value.trim();
        if (!username.match(/^[a-zA-Z0-9]{4,20}$/)) {
            isValid = false;
            document.getElementById('username').classList.add('is-invalid');
            alert('Username must be 4-20 alphanumeric characters.');
        } else {
            document.getElementById('username').classList.remove('is-invalid');
        }

        // Validate email
        const email = document.getElementById('email').value.trim();
        if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            isValid = false;
            document.getElementById('email').classList.add('is-invalid');
            alert('Please enter a valid email address.');
        } else {
            document.getElementById('email').classList.remove('is-invalid');
        }

        // Validate name
        const name = document.getElementById('name').value.trim();
        if (!name.match(/^[a-zA-Z ]{2,50}$/)) {
            isValid = false;
            document.getElementById('name').classList.add('is-invalid');
            alert('Name must be 2-50 characters and contain only letters and spaces.');
        } else {
            document.getElementById('name').classList.remove('is-invalid');
        }

        // Validate password
        const password = document.getElementById('password').value;
        if (!password.match(/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/)) {
            isValid = false;
            document.getElementById('password').classList.add('is-invalid');
            alert('Password must be at least 8 characters and include at least one letter and one number.');
        } else {
            document.getElementById('password').classList.remove('is-invalid');
        }

        // Validate password confirmation
        const confirmPassword = document.getElementById('confirm_password').value;
        if (password !== confirmPassword) {
            isValid = false;
            document.getElementById('confirm_password').classList.add('is-invalid');
            alert('Passwords do not match.');
        } else {
            document.getElementById('confirm_password').classList.remove('is-invalid');
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
