<?php
require_once('database.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
class User {
    private $nom;
    private $prenom;
    private $email;
    private $passwordHash;
    private $role;

    public function __construct($nom, $prenom, $email, $password = null, $role = null) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        if ($password) {
            $this->passwordHash = password_hash($password, PASSWORD_BCRYPT);
        }
        $this->role = $role;
    }

    private function getDbConnection() {
        return Database::getInstance()->getConnection();
    }

    public function save() {
        $db = $this->getDbConnection();
        try {
            $stmt = $db->prepare("
                INSERT INTO users (username, email, password_hash, role, is_active)
                VALUES (:username, :email, :password, :role, :is_active)
            ");
            $username = $this->nom . " " . $this->prenom;
            $isActive = ($this->role === 'Enseignant') ? 0 : 1;
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':password', $this->passwordHash);
            $stmt->bindParam(':role', $this->role);
            $stmt->bindParam(':is_active', $isActive);
            $stmt->execute();
            header('Location: ../pages/login.php');
            exit;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $_SESSION['error_message'] = "An error occurred while saving the user. Please try again.";
            header('Location: ../pages/error.php');
            exit;
        }
    }
    

    public function signIn($email, $password) {
        $db = $this->getDbConnection();
        try {
            $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user'] = [
                    'id' => $user['user_id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'etat' => $user['is_active']
                ];
    
                header('Location: ../pages/index.php');
                exit;
            } else {
                $_SESSION['error_message'] = "Invalid email or password.";
                header('Location: ../pages/login.php');
                exit;
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $_SESSION['error_message'] = "An error occurred while signing in. Please try again.";
            header('Location: ../pages/error.php');
            exit;
        }
    }
    
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST["signup-email"])) {
            $firstName = trim($_POST["first-name"]);
            $lastName = trim($_POST["last-name"]);
            $email = trim($_POST["signup-email"]);
            $password = trim($_POST["confirm-password"]);
            $role = trim($_POST["role"]);
            if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($role)) {
                throw new Exception("All fields are required.");
            }

            $user = new User($firstName, $lastName, $email, $password, $role);
            $user->save();
        }
        if (isset($_POST["login-email"])) {
            $email = trim($_POST["login-email"]);
            $password = trim($_POST["login-password"]);
            if (empty($email) || empty($password)) {
                throw new Exception("Email and password are required.");
            }

            $user = new User(null, null, $email);
            $user->signIn($email, $password);
        }
}