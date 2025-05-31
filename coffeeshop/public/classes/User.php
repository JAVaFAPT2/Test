<?php
/**
 * User Class
 * Handles user registration, login, and validation
 */
class User {
    // Database connection and table name
    private $conn;
    private $table_name = "users";

    // User properties
    public $id;
    public $username;
    public $email;
    public $password;
    public $name;
    public $created;
    public $role;

    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Register new user with validation
     *
     * @return boolean
     */
    public function register() {
        // Validate input
        if (!$this->validateInput()) {
            return false;
        }

        // Check if email already exists
        if ($this->emailExists()) {
            $_SESSION['error_msg'] = "Email already exists. Please use a different email.";
            return false;
        }

        // Query to insert new user
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    username = :username,
                    email = :email,
                    password = :password,
                    name = :name,
                    role = :role,
                    created = :created";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind values
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->role = isset($this->role) ? $this->role : 'admin';
        $this->created = date('Y-m-d H:i:s');

        // Hash the password
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);

        // Bind data
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":created", $this->created);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Validate user input
     *
     * @return boolean
     */
    private function validateInput() {
        // Validate username (alphanumeric, 4-20 chars)
        if (!preg_match('/^[a-zA-Z0-9]{4,20}$/', $this->username)) {
            $_SESSION['error_msg'] = "Username must be 4-20 alphanumeric characters.";
            return false;
        }

        // Validate email
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_msg'] = "Invalid email format.";
            return false;
        }

        // Validate password (min 8 chars, at least one letter and one number)
        if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $this->password)) {
            $_SESSION['error_msg'] = "Password must be at least 8 characters and contain at least one letter and one number.";
            return false;
        }

        // Validate name (letters, spaces, 2-50 chars)
        if (!preg_match('/^[a-zA-Z ]{2,50}$/', $this->name)) {
            $_SESSION['error_msg'] = "Name must be 2-50 characters and contain only letters and spaces.";
            return false;
        }

        return true;
    }

    /**
     * Check if email exists
     *
     * @return boolean
     */
    private function emailExists() {
        // Query to check if email exists
        $query = "SELECT id, username, password
                FROM " . $this->table_name . "
                WHERE email = ?
                LIMIT 0,1";

        // Prepare the query
        $stmt = $this->conn->prepare($query);

        // Sanitize email
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Bind email value
        $stmt->bindParam(1, $this->email);

        // Execute the query
        $stmt->execute();

        // Get number of rows
        $num = $stmt->rowCount();

        // If email exists
        if ($num > 0) {
            return true;
        }

        return false;
    }

    /**
     * User login
     *
     * @return boolean
     */
    public function login() {
        // Query to check credentials
        $query = "SELECT id, username, password, role, name
                FROM " . $this->table_name . "
                WHERE email = ?
                LIMIT 0,1";

        // Prepare the query
        $stmt = $this->conn->prepare($query);

        // Sanitize email
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Bind email parameter
        $stmt->bindParam(1, $this->email);

        // Execute the query
        $stmt->execute();

        // Get number of rows
        $num = $stmt->rowCount();

        // If user found
        if ($num > 0) {
            // Get record details
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password
            if (password_verify($this->password, $row['password'])) {
                // Set values to object properties
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->role = $row['role'];
                $this->name = $row['name'];

                // Create session variables
                $_SESSION['user_id'] = $this->id;
                $_SESSION['username'] = $this->username;
                $_SESSION['role'] = $this->role;
                $_SESSION['name'] = $this->name;

                return true;
            }
        }

        // If login failed
        $_SESSION['error_msg'] = "Invalid email or password.";
        return false;
    }
}
?>
