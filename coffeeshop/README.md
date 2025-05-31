# Brew Haven Coffee Shop

A complete e-commerce website for a coffee shop, built with PHP, MySQL, and JavaScript.

## Features

- **User Authentication**
  - Registration with validation
  - Login with secure password hashing
  - Session management
  - Access control

- **Product Catalog**
  - Browse coffee products by category
  - Search functionality
  - Product details

- **Shopping Cart**
  - Add/remove items
  - Update quantities
  - Calculate totals

- **Checkout System**
  - Order processing
  - Order confirmation

- **Admin Panel**
  - Dashboard with stats
  - Product management (CRUD)
  - Category management
  - Order management

## Technology Stack

- **Backend:** PHP with PDO for database connectivity
- **Database:** MySQL
- **Frontend:** HTML5, CSS3, JavaScript
- **UI Framework:** Bootstrap 5
- **Icons:** Font Awesome

## Directory Structure

```
coffee-shop/
│
├── public/              # Web root directory
│   ├── admin/           # Admin panel pages
│   ├── assets/          # CSS, JS, images
│   │   ├── css/         # CSS files
│   │   ├── js/          # JavaScript files
│   │   └── images/      # Image files
│   ├── classes/         # PHP classes
│   ├── config/          # Configuration files
│   └── includes/        # Reusable PHP components
│
└── index.php            # Redirect to public folder
```

## Installation

1. Clone the repository
2. Create a MySQL database named `coffeeshop_db`
3. Import the database schema from `public/config/database.sql`
4. Configure database connection in `public/config/database.php`
5. Navigate to the project URL in your browser

## Admin Access

- Email: admin@coffeeshop.com
- Password: Admin123

## Security Features

- Password hashing with bcrypt
- Input validation and sanitization
- Protection against SQL injection
- XSS prevention
- CSRF protection

## Credits

- Bootstrap: [https://getbootstrap.com/](https://getbootstrap.com/)
- Font Awesome: [https://fontawesome.com/](https://fontawesome.com/)
- jQuery: [https://jquery.com/](https://jquery.com/)
