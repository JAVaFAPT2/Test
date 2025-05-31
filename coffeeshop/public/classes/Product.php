<?php
/**
 * Product Class
 * Handles product operations
 */
class Product {
    // Database connection and table name
    private $conn;
    private $table_name = "products";

    // Product properties
    public $id;
    public $name;
    public $description;
    public $price;
    public $image;
    public $category_id;
    public $created;

    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Read all products
     *
     * @return PDOStatement
     */
    public function readAll() {
        // Select all products
        $query = "SELECT p.id, p.name, p.description, p.price, p.image, p.category_id, c.name as category_name
                FROM " . $this->table_name . " p
                LEFT JOIN categories c ON p.category_id = c.id
                ORDER BY p.name ASC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    /**
     * Read products by category
     *
     * @return PDOStatement
     */
    public function readByCategory() {
        // Select products by category
        $query = "SELECT p.id, p.name, p.description, p.price, p.image, p.category_id, c.name as category_name
                FROM " . $this->table_name . " p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.category_id = ?
                ORDER BY p.name ASC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind category_id
        $stmt->bindParam(1, $this->category_id);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    /**
     * Read one product
     *
     * @return boolean
     */
    public function readOne() {
        // Query to read single product
        $query = "SELECT p.id, p.name, p.description, p.price, p.image, p.category_id, c.name as category_name
                FROM " . $this->table_name . " p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id = ?
                LIMIT 0,1";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind id
        $stmt->bindParam(1, $this->id);

        // Execute query
        $stmt->execute();

        // Get row count
        $num = $stmt->rowCount();

        // If product exists
        if ($num > 0) {
            // Get record details
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Set values to object properties
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->price = $row['price'];
            $this->image = $row['image'];
            $this->category_id = $row['category_id'];

            return true;
        }

        return false;
    }

    /**
     * Create product (admin function)
     *
     * @return boolean
     */
    public function create() {
        // Check if required fields are valid
        if (!$this->validateProductInput()) {
            return false;
        }

        // Query to insert product
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    name = :name,
                    description = :description,
                    price = :price,
                    image = :image,
                    category_id = :category_id,
                    created = :created";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind values
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->created = date('Y-m-d H:i:s');

        // Bind data
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":created", $this->created);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Update product (admin function)
     *
     * @return boolean
     */
    public function update() {
        // Check if required fields are valid
        if (!$this->validateProductInput()) {
            return false;
        }

        // Query to update product
        $query = "UPDATE " . $this->table_name . "
                SET
                    name = :name,
                    description = :description,
                    price = :price,
                    image = :image,
                    category_id = :category_id
                WHERE
                    id = :id";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind values
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind data
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":id", $this->id);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Delete product (admin function)
     *
     * @return boolean
     */
    public function delete() {
        // Query to delete product
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind id
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Search products
     *
     * @return PDOStatement
     */
    public function search($keywords) {
        // Select products based on search keywords
        $query = "SELECT p.id, p.name, p.description, p.price, p.image, p.category_id, c.name as category_name
                FROM " . $this->table_name . " p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?
                ORDER BY p.name ASC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Sanitize keywords
        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";

        // Bind keywords
        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);
        $stmt->bindParam(3, $keywords);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    /**
     * Validate product input
     *
     * @return boolean
     */
    private function validateProductInput() {
        // Validate name (not empty, 2-100 chars)
        if (empty($this->name) || strlen($this->name) < 2 || strlen($this->name) > 100) {
            $_SESSION['error_msg'] = "Product name must be between 2-100 characters.";
            return false;
        }

        // Validate price (numeric, positive)
        if (!is_numeric($this->price) || $this->price <= 0) {
            $_SESSION['error_msg'] = "Price must be a positive number.";
            return false;
        }

        // Validate category ID (must be numeric)
        if (!is_numeric($this->category_id)) {
            $_SESSION['error_msg'] = "Invalid category.";
            return false;
        }

        return true;
    }

    /**
     * Get product image by ID
     *
     * @param int $id The product ID
     * @return string|false The binary image data or false if not found
     */
    public function getImage($id) {
        // Query to get image for a single product
        $query = "SELECT image
                FROM " . $this->table_name . "
                WHERE id = ?
                LIMIT 0,1";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind id
        $stmt->bindParam(1, $id);

        // Execute query
        $stmt->execute();

        // Get row count
        $num = $stmt->rowCount();

        // If image exists
        if ($num > 0) {
            // Fetch the row
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Return the image data
            return $row['image'];
        }

        return false;
    }
}
?>
