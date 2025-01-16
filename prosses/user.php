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
            $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (:nom, :email, :password, :role)");
            $username = $this->nom . " " . $this->prenom;
            $stmt->bindParam(':nom', $username);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':password', $this->passwordHash);
            $stmt->bindParam(':role', $this->role);
            $stmt->execute();
            header('Location: ../pages/login.php');
            exit;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while saving the user.");
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
                if ($_SESSION['user']['role'] === 'Enseignant') {
                    $id = $_SESSION['user']['id'];
                    $stmt = $db->prepare("UPDATE users
                    SET is_active = FALSE
                    WHERE user_id = $id;
                    ");
                    $stmt->execute();
                    header('Location: ../pages/index.php');
                    exit;
                }else {
                    header('Location: ../pages/index.php');
                    exit;
                }
                
                
            } else {
                echo "Invalid email or password.";
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while signing in.");
        }
    }

    public function signOut() {
        if (isset($_SESSION['user'])) {
            unset($_SESSION['user']);
            session_destroy();
            header('Location: ../pages/login.php');
            exit;
        }
    }

}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["signup-email"])) {
        $firstName = $_POST["first-name"];
        $lastName = $_POST["last-name"];
        $email = $_POST["signup-email"];
        $password = $_POST["confirm-password"];
        $role = $_POST["role"];

        $user = new User($firstName, $lastName, $email, $password, $role);
        $user->save();
    }

    if (isset($_POST["login-email"])) {
        $email = $_POST["login-email"];
        echo $email;
        $password = $_POST["login-password"];
        echo $password;
        $user = new User('', '', $email);
        $user->signIn($email, $password);
    }else {
        echo 'no!!!';
    }
}
?>