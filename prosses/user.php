<?php
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct($dsn, $username, $password) {
        try {
            $this->pdo = new PDO($dsn, $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed.");
        }
    }

    public static function getInstance($dsn = null, $username = null, $password = null) {
        if (self::$instance === null) {
            $dsn = $dsn ?? 'mysql:host=localhost;dbname=your_database_name';
            $username = $username ?? 'your_username';
            $password = $password ?? 'your_password';
            self::$instance = new Database($dsn, $username, $password);
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}

class User {
    private $id;
    private $nom;
    private $prenom;
    private $email;
    private $passwordHash;

    public function __construct($id, $nom, $prenom, $email, $passwordHash = null) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
    }

    public function __toString() {
        return $this->nom . " " . $this->prenom;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getPrenom() { return $this->prenom; }
    public function getEmail() { return $this->email; }

    // Password hashing method
    private function setPasswordHash($password) {
        $this->passwordHash = password_hash($password, PASSWORD_BCRYPT);
    }

    // Save user to the database
    public function save() {
        $db = Database::getInstance()->getConnection();
        try {
            if ($this->id) {
                // Update user
                $stmt = $db->prepare("UPDATE users SET nom = :nom, prenom = :prenom, email = :email WHERE id = :id");
                $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
                $stmt->bindParam(':nom', $this->nom, PDO::PARAM_STR);
                $stmt->bindParam(':prenom', $this->prenom, PDO::PARAM_STR);
                $stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
                $stmt->execute();
            } else {
                // Insert new user
                $stmt = $db->prepare("INSERT INTO users (nom, prenom, email, password) VALUES (:nom, :prenom, :email, :password)");
                $stmt->bindParam(':nom', $this->nom, PDO::PARAM_STR);
                $stmt->bindParam(':prenom', $this->prenom, PDO::PARAM_STR);
                $stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
                $stmt->bindParam(':password', $this->passwordHash, PDO::PARAM_STR);
                $stmt->execute();
                $this->id = $db->lastInsertId(); // Set the new user ID
            }
            return $this->id;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while saving the user.");
        }
    }
    
        // Search user by name
    public function searchUserByName($name)
    {
        $db = Database::getInstance()->getConnection();

        // Prepare the SQL query
        $stmt = $db->prepare("SELECT * FROM users WHERE nom LIKE :name OR prenom LIKE :name");

        // Bind the parameter for name search (using wildcards for partial match)
        $stmt->bindValue(':name', '%' . $name . '%', PDO::PARAM_STR);

        // Execute the query
        $stmt->execute();

        // Fetch all matching users
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return an array of User objects
        $users = [];
        foreach ($results as $result) {
            $users[] = new User(
                $result['id'],
                $result['nom'],
                $result['prenom'],
                $result['email'],
                $result['password']
            );
        }

        return $users;
    }

    // Get user by ID
    public function getUserById($id)
    {
        $db = Database::getInstance()->getConnection();

        // Prepare the SQL query
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return new User(
                $result['id'],
                $result['nom'],
                $result['prenom'],
                $result['email'],
                $result['password']
            );
        }

        return null; // User not found
    }

    // Static method to search user by email
    public static function findByEmail($email) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return new User($result['id'], $result['nom'], $result['prenom'], $result['email'], $result['password']);
        }

        return null;
    }

    // Method to register a new user (signup)
    public static function signup($nom, $prenom, $email, $password) {
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    // Validate password length
    if (strlen($password) < 6) {
        throw new Exception("Password must be at least 6 characters long");
    }

    // Sanitize name fields
    $nom = htmlspecialchars($nom);
    $prenom = htmlspecialchars($prenom);

    // Check if email already exists
    if (self::findByEmail($email)) {
        throw new Exception("Email is already registered");
    }

    // Create a new user object
    $user = new User(null, $nom, $prenom, $email);
    $user->setPasswordHash($password); // Hash the password
    return $user->save();
}


    // Method to login (signin)
    public static function signin($email, $password) {
        $user = self::findByEmail($email);

        // Check if user exists and password is correct
        if (!$user || !password_verify($password, $user->passwordHash)) {
            throw new Exception("Invalid email or password");
        }

        return $user; // Successful login
    }

    // Method to change the user's password
    public function changePassword($newPassword) {
        $this->setPasswordHash($newPassword); // Hash the new password
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->bindParam(':password', $this->passwordHash, PDO::PARAM_STR);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
    }
}

?>