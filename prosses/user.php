<?php
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
        if ($password) {
            $this->setPassword($password);
        }
    }

    public function getId() {
        return $this->id;
    }

    public function getNom() {
        return $this->nom;
    }

    public function setNom($nom) {
        $this->nom = $nom;
    }

    public function getPrenom() {
        return $this->prenom;
    }

    public function setPrenom($prenom) {
        $this->prenom = $prenom;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setPassword($password) {
        $this->passwordHash = password_hash($password, PASSWORD_BCRYPT);
    }

    public function verifyPassword($password) {
        return password_verify($password, $this->passwordHash);
    }

    // Save user to database
    public function save() {
        $db = Database::getInstance()->getConnection();
        if ($this->id) {
            // Update existing user
            $stmt = $db->prepare("UPDATE users SET nom = :nom, prenom = :prenom, email = :email, password = :password WHERE id = :id");
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        } else {
            // Insert new user
            $stmt = $db->prepare("INSERT INTO users (nom, prenom, email, password) VALUES (:nom, :prenom, :email, :password)");
        }
        $stmt->bindParam(':nom', $this->nom, PDO::PARAM_STR);
        $stmt->bindParam(':prenom', $this->prenom, PDO::PARAM_STR);
        $stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $this->passwordHash, PDO::PARAM_STR);
        $stmt->execute();

        if (!$this->id) {
            $this->id = $db->lastInsertId();
        }
    }

    // Static methods

    public static function findById($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return new User($result['nom'], $result['prenom'], $result['email'], $result['password'], $result['id']);
        }
        return null;
    }

    public static function findByEmail($email) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return new User($result['nom'], $result['prenom'], $result['email'], $result['password'], $result['id']);
        }
        return null;
    }

    public static function getAllUsers() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM users");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($results as $result) {
            $users[] = new User($result['nom'], $result['prenom'], $result['email'], $result['password'], $result['id']);
        }
        return $users;
    }

    public function delete() {
        if ($this->id) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->execute();
        }
    }
}
