<?php
/**
 * Order Class
 * Handles order operations
 */
class Order {
    // Database connection and table names
    private $conn;
    private $table_name = "orders";
    private $order_items_table = "order_items";

    // Order properties
    public $id;
    public $user_id;
    public $total_amount;
    public $status;
    public $created;
    public $items = array(); // Array to hold order items

    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create order
     *
     * @return boolean
     */
    public function create() {
        // Start transaction
        $this->conn->beginTransaction();

        try {
            // First, create the order record
            $query = "INSERT INTO " . $this->table_name . "
                    SET
                        user_id = :user_id,
                        total_amount = :total_amount,
                        status = :status,
                        created = :created";

            // Prepare query
            $stmt = $this->conn->prepare($query);

            // Sanitize and bind values
            $this->user_id = htmlspecialchars(strip_tags($this->user_id));
            $this->total_amount = htmlspecialchars(strip_tags($this->total_amount));
            $this->status = 'pending'; // Default status
            $this->created = date('Y-m-d H:i:s');

            // Bind data
            $stmt->bindParam(":user_id", $this->user_id);
            $stmt->bindParam(":total_amount", $this->total_amount);
            $stmt->bindParam(":status", $this->status);
            $stmt->bindParam(":created", $this->created);

            // Execute query
            $stmt->execute();

            // Get the order ID
            $this->id = $this->conn->lastInsertId();

            // Then, insert order items
            foreach ($this->items as $item) {
                $query = "INSERT INTO " . $this->order_items_table . "
                        SET
                            order_id = :order_id,
                            product_id = :product_id,
                            quantity = :quantity,
                            price = :price";

                // Prepare query
                $stmt = $this->conn->prepare($query);

                // Sanitize and bind values
                $order_id = htmlspecialchars(strip_tags($this->id));
                $product_id = htmlspecialchars(strip_tags($item['product_id']));
                $quantity = htmlspecialchars(strip_tags($item['quantity']));
                $price = htmlspecialchars(strip_tags($item['price']));

                // Bind data
                $stmt->bindParam(":order_id", $order_id);
                $stmt->bindParam(":product_id", $product_id);
                $stmt->bindParam(":quantity", $quantity);
                $stmt->bindParam(":price", $price);

                // Execute query
                $stmt->execute();
            }

            // Commit transaction
            $this->conn->commit();

            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();

            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Read orders by user
     *
     * @return PDOStatement
     */
    public function readByUser() {
        // Select orders by user
        $query = "SELECT id, total_amount, status, created
                FROM " . $this->table_name . "
                WHERE user_id = ?
                ORDER BY created DESC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind user_id
        $stmt->bindParam(1, $this->user_id);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    /**
     * Read all orders (admin function)
     *
     * @return PDOStatement
     */
    public function readAll() {
        // Select all orders
        $query = "SELECT o.id, o.user_id, o.total_amount, o.status, o.created, u.username
                FROM " . $this->table_name . " o
                LEFT JOIN users u ON o.user_id = u.id
                ORDER BY o.created DESC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    /**
     * Read one order with details
     *
     * @return boolean
     */
    public function readOne() {
        // Query to read order details
        $query = "SELECT o.id, o.user_id, o.total_amount, o.status, o.created, u.username, u.email
                FROM " . $this->table_name . " o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.id = ?
                LIMIT 0,1";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind id
        $stmt->bindParam(1, $this->id);

        // Execute query
        $stmt->execute();

        // Get row count
        $num = $stmt->rowCount();

        // If order exists
        if ($num > 0) {
            // Get record details
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Set values to object properties
            $this->user_id = $row['user_id'];
            $this->total_amount = $row['total_amount'];
            $this->status = $row['status'];
            $this->created = $row['created'];

            // Now get the order items
            $query = "SELECT oi.product_id, oi.quantity, oi.price, p.name as product_name
                    FROM " . $this->order_items_table . " oi
                    LEFT JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?";

            // Prepare query statement
            $stmt = $this->conn->prepare($query);

            // Bind order id
            $stmt->bindParam(1, $this->id);

            // Execute query
            $stmt->execute();

            // Get items
            $this->items = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($this->items, $row);
            }

            return true;
        }

        return false;
    }

    /**
     * Update order status (admin function)
     *
     * @return boolean
     */
    public function updateStatus() {
        // Query to update order status
        $query = "UPDATE " . $this->table_name . "
                SET
                    status = :status
                WHERE
                    id = :id";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind values
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind data
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
?>
