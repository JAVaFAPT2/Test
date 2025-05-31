/**
 * Brew Haven Coffee Shop - Main JavaScript File
 */

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize Bootstrap popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    const popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Shopping Cart Functionality
    setupShoppingCart();

    // Form Validation
    setupFormValidation();
});

/**
 * Setup Shopping Cart Functionality
 */
function setupShoppingCart() {
    // Add to Cart Button Click Event
    const addToCartButtons = document.querySelectorAll('.add-to-cart');

    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const productName = this.getAttribute('data-name');
            const productPrice = this.getAttribute('data-price');

            // Add item to cart
            addToCart(productId, productName, productPrice);
        });
    });

    // Function to add item to cart
    function addToCart(id, name, price) {
        // Create AJAX request
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'add_to_cart.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function() {
            if (this.status === 200) {
                try {
                    const response = JSON.parse(this.responseText);
                    if (response.success) {
                        // Update cart count in navbar
                        const cartCountElement = document.getElementById('cart-count');
                        if (cartCountElement) {
                            cartCountElement.textContent = response.cart_count;
                        }

                        // Show success message
                        showNotification(`${name} added to your cart!`, 'success');
                    } else {
                        showNotification(response.message, 'danger');
                    }
                } catch (e) {
                    console.error('Error parsing JSON response:', e);
                    showNotification('An error occurred while adding to cart', 'danger');
                }
            }
        };

        xhr.onerror = function() {
            showNotification('Request failed', 'danger');
        };

        xhr.send(`product_id=${id}&quantity=1&ajax=1`);
    }

    // Update Cart Quantity Event
    const quantityInputs = document.querySelectorAll('input[name^="quantity"]');

    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (parseInt(this.value) < 1) {
                this.value = 1;
            }
            if (parseInt(this.value) > 10) {
                this.value = 10;
            }
        });
    });
}

/**
 * Setup Form Validation
 */
function setupFormValidation() {
    // Login Form Validation
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            let isValid = true;

            // Validate email
            const emailInput = document.getElementById('email');
            if (emailInput && !validateEmail(emailInput.value)) {
                markInvalid(emailInput, 'Please enter a valid email address');
                isValid = false;
            } else if (emailInput) {
                markValid(emailInput);
            }

            // Validate password (not empty)
            const passwordInput = document.getElementById('password');
            if (passwordInput && passwordInput.value.trim() === '') {
                markInvalid(passwordInput, 'Please enter your password');
                isValid = false;
            } else if (passwordInput) {
                markValid(passwordInput);
            }

            if (!isValid) {
                event.preventDefault();
            }
        });
    }

    // Registration Form Validation
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            let isValid = true;

            // Validate username (alphanumeric, 4-20 chars)
            const usernameInput = document.getElementById('username');
            if (usernameInput && !validateUsername(usernameInput.value)) {
                markInvalid(usernameInput, 'Username must be 4-20 alphanumeric characters');
                isValid = false;
            } else if (usernameInput) {
                markValid(usernameInput);
            }

            // Validate email
            const emailInput = document.getElementById('email');
            if (emailInput && !validateEmail(emailInput.value)) {
                markInvalid(emailInput, 'Please enter a valid email address');
                isValid = false;
            } else if (emailInput) {
                markValid(emailInput);
            }

            // Validate name (letters and spaces, 2-50 chars)
            const nameInput = document.getElementById('name');
            if (nameInput && !validateName(nameInput.value)) {
                markInvalid(nameInput, 'Name must be 2-50 characters and contain only letters and spaces');
                isValid = false;
            } else if (nameInput) {
                markValid(nameInput);
            }

            // Validate password
            const passwordInput = document.getElementById('password');
            if (passwordInput && !validatePassword(passwordInput.value)) {
                markInvalid(passwordInput, 'Password must be at least 8 characters and include at least one letter and one number');
                isValid = false;
            } else if (passwordInput) {
                markValid(passwordInput);
            }

            // Validate password confirmation
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordMatchesConfirm = passwordInput && confirmPasswordInput &&
                                          passwordInput.value === confirmPasswordInput.value;

            if (confirmPasswordInput && !passwordMatchesConfirm) {
                markInvalid(confirmPasswordInput, 'Passwords do not match');
                isValid = false;
            } else if (confirmPasswordInput) {
                markValid(confirmPasswordInput);
            }

            if (!isValid) {
                event.preventDefault();
            }
        });
    }

    // Checkout Form Validation
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(event) {
            let isValid = true;

            // Validate card number (16 digits)
            const cardNumberInput = document.getElementById('card_number');
            if (cardNumberInput && !validateCardNumber(cardNumberInput.value)) {
                markInvalid(cardNumberInput, 'Please enter a valid 16-digit card number');
                isValid = false;
            } else if (cardNumberInput) {
                markValid(cardNumberInput);
            }

            // Validate expiry date (MM/YY format)
            const expiryInput = document.getElementById('card_expiry');
            if (expiryInput && !validateExpiry(expiryInput.value)) {
                markInvalid(expiryInput, 'Please enter a valid expiry date in MM/YY format');
                isValid = false;
            } else if (expiryInput) {
                markValid(expiryInput);
            }

            // Validate CVV (3-4 digits)
            const cvvInput = document.getElementById('card_cvv');
            if (cvvInput && !validateCVV(cvvInput.value)) {
                markInvalid(cvvInput, 'Please enter a valid 3-4 digit CVV code');
                isValid = false;
            } else if (cvvInput) {
                markValid(cvvInput);
            }

            if (!isValid) {
                event.preventDefault();
            }
        });
    }
}

/**
 * Validation Functions
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validateUsername(username) {
    const re = /^[a-zA-Z0-9]{4,20}$/;
    return re.test(username);
}

function validateName(name) {
    const re = /^[a-zA-Z ]{2,50}$/;
    return re.test(name);
}

function validatePassword(password) {
    const re = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/;
    return re.test(password);
}

function validateCardNumber(cardNumber) {
    const digitsOnly = cardNumber.replace(/\s+/g, '');
    const re = /^\d{16}$/;
    return re.test(digitsOnly);
}

function validateExpiry(expiry) {
    const re = /^\d{2}\/\d{2}$/;
    return re.test(expiry);
}

function validateCVV(cvv) {
    const re = /^\d{3,4}$/;
    return re.test(cvv);
}

/**
 * Helper Functions
 */
function markInvalid(input, message) {
    input.classList.add('is-invalid');

    // Check if error message element already exists
    let feedback = input.nextElementSibling;
    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        input.parentNode.insertBefore(feedback, input.nextSibling);
    }

    feedback.textContent = message;
}

function markValid(input) {
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');

    // Remove any existing error message
    const feedback = input.nextElementSibling;
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.textContent = '';
    }
}

function showNotification(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    toast.className = `toast text-white bg-${type}`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.setAttribute('id', toastId);

    // Create toast content
    toast.innerHTML = `
        <div class="toast-header">
            <strong class="me-auto">Notification</strong>
            <small>just now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            ${message}
        </div>
    `;

    // Append toast to container
    toastContainer.appendChild(toast);

    // Initialize and show toast
    const toastElement = new bootstrap.Toast(toast);
    toastElement.show();

    // Remove toast after it's hidden
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}
