<?php
/**
 * Category Class
 * Handles category operations
 */
class Category {
    // Database connection and table name
    private $conn;
    private $table_name = "categories";

    // Category properties
    public $id;
    public $name;
    public $description;
    public $created;

    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Read all categories
     *
     * @return PDOStatement
     */
    public function readAll() {
        // Select all categories
        $query = "SELECT id, name, description FROM " . $this->table_name . " ORDER BY name ASC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    /**
     * Create category (admin function)
     *
     * @return boolean
     */
    public function create() {
        // Query to insert category
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    name = :name,
                    description = :description,
                    created = :created";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind values
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->created = date('Y-m-d H:i:s');

        // Bind data
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":created", $this->created);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Read one category
     *
     * @return boolean
     */
    public function readOne() {
        // Query to read single category
        $query = "SELECT id, name, description FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind id
        $stmt->bindParam(1, $this->id);

        // Execute query
        $stmt->execute();

        // Get row count
        $num = $stmt->rowCount();

        // If category exists
        if ($num > 0) {
            // Get record details
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Set values to object properties
            $this->name = $row['name'];
            $this->description = $row['description'];

            return true;
        }

        return false;
    }

    /**
     * Update category (admin function)
     *
     * @return boolean
     */
    public function update() {
        // Query to update category
        $query = "UPDATE " . $this->table_name . "
                SET
                    name = :name,
                    description = :description
                WHERE
                    id = :id";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind values
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind data
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":id", $this->id);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Delete category (admin function)
     *
     * @return boolean
     */
    public function delete() {
        // Query to delete category
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
}
?>
