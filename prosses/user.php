<?php
require_once('database.php');
class User {
    private $id;
    private $nom;
    private $prenom;
    private $email;
    private $passwordHash;

    public function __construct($nom, $prenom, $email, $password = null, $id = null) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->passwordHash = password_hash($password, PASSWORD_BCRYPT);
    }

    public function save(){
        $db = Database::getInstance()->getConnection();
        try {
        $stmt = $db->prepare("INSERT INTO users (username, email, password_hash) VALUES (:nom, :email, :password)");
                $username = $this->nom . " " . $this->prenom;
                $stmt->bindParam( ':nom', $username);
                $stmt->bindParam(':email', $this->email);
                $stmt->bindParam(':password', $this->passwordHash);
                $stmt->execute();
        } catch(PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while saving the user hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh.");
        }
        
    }

    public function 
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fristName = $_POST["first-name"];
    $lastName = $_POST["last-name"];
    $email = $_POST["signup-email"];
    $password = $_POST["confirm-password"];

    $save = new User($fristName, $lastName, $email, $password);
    $save->save();
}