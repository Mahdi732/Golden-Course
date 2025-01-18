<?php
require_once 'database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

abstract class Cours {
    protected $title;
    protected $description;
    protected $price;
    protected $category_id;
    protected $teacher_id;

    public function __construct($title, $description, $price, $category_id, $teacher_id) {
        $this->title = $title;
        $this->description = $description;
        $this->price = $price;
        $this->category_id = $category_id;
        $this->teacher_id = $teacher_id;
    }

    protected static function getDbConnection() {
        return Database::getInstance()->getConnection();
    }

    abstract public function ajouterCours();

    abstract public static function afficherCours();
}

class VideoCours extends Cours {
    private $videoUrl;

    public function __construct($title, $description, $videoUrl, $price, $category_id, $teacher_id) {
        parent::__construct($title, $description, $price, $category_id, $teacher_id);
        $this->videoUrl = $videoUrl;
    }

    public function ajouterCours() {
        $db = $this->getDbConnection();

        try {
            $sql = "INSERT INTO courses (title, description, course_type, video_url, price, category_id, teacher_id)
                    VALUES (:title, :description, 'video', :video_url, :price, :category_id, :teacher_id)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':title', $this->title);
            $stmt->bindParam(':description', $this->description);
            $stmt->bindParam(':video_url', $this->videoUrl);
            $stmt->bindParam(':price', $this->price);
            $stmt->bindParam(':category_id', $this->category_id);
            $stmt->bindParam(':teacher_id', $this->teacher_id);

            $stmt->execute();
            return "Video course added successfully!";
        } catch (PDOException $e) {
            throw new Exception("Error adding video course: " . $e->getMessage());
        }
    }

    public static function afficherCours() {
        $db = Cours::getDbConnection();
        try {
            $sql = "SELECT * FROM courses WHERE course_type = 'video'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching video courses: " . $e->getMessage());
        }
    }
}

class DocumentCours extends Cours {
    private $documentText;

    public function __construct($title, $description, $documentText, $price, $category_id, $teacher_id) {
        parent::__construct($title, $description, $price, $category_id, $teacher_id);
        $this->documentText = $documentText;
    }

    public function ajouterCours() {
        $db = $this->getDbConnection();

        try {
            $sql = "INSERT INTO courses (title, description, course_type, document_content, price, category_id, teacher_id)
                    VALUES (:title, :description, 'document', :document_content, :price, :category_id, :teacher_id)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':title', $this->title);
            $stmt->bindParam(':description', $this->description);
            $stmt->bindParam(':document_content', $this->documentText);
            $stmt->bindParam(':price', $this->price);
            $stmt->bindParam(':category_id', $this->category_id);
            $stmt->bindParam(':teacher_id', $this->teacher_id);

            $stmt->execute();
            return "Document course added successfully!";
        } catch (PDOException $e) {
            throw new Exception("Error adding document course: " . $e->getMessage());
        }
    }

    public static function afficherCours() {
        $db = Cours::getDbConnection();

        try {
            $sql = "SELECT * FROM courses WHERE course_type = 'document'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching document courses: " . $e->getMessage());
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['CreateCourseSub'])) {
    if (empty($_POST['course_title']) || empty($_POST['course_description']) || empty($_POST['categories_select']) || empty($_POST['course_price']) || empty($_POST['course_type'])) {
        echo 'youare gay 3iu';
    }

    $course_title = $_POST['course_title'];
    $course_description = $_POST['course_description'];
    $categories_select = $_POST['categories_select'];
    $course_price = $_POST['course_price'];
    $course_type = $_POST['course_type'];

    if ($course_type === 'video') {
        if (!isset($_FILES['video_file']) || $_FILES['video_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Video file is required.'
            ];
            header('Location: course.classes.php');
            exit();
        }

        $uploadDir = __DIR__ . '/../uploads/videos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $videoFile = $_FILES['video_file'];
        $videoPath = $uploadDir . basename($videoFile['name']);

        if (move_uploaded_file($videoFile['tmp_name'], $videoPath)) {
            $course = new VideoCours($course_title, $course_description, $videoPath, $course_price, $categories_select, $_SESSION['user']['id']);
            try {
                $course->ajouterCours();
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => 'Video course added successfully!'
                ];
                header('Location: ../pages/teacher.php');
                exit();
            } catch (Exception $e) {
                echo 'youare gay 1';
            }
        } else {
            echo 'youare gay 2';
        }
    } elseif ($course_type === 'document') {
        if (empty($_POST['document_content'])) {
            echo 'youare gay 88';
        }

        $content = $_POST['document_content'];
        try {
            $course = new DocumentCours($course_title, $course_description, $content, $course_price, $categories_select, $_SESSION['user']['id']);
            $course->ajouterCours();
            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Document course added successfully!'
            ];
            header('Location: ../pages/teacher.php');
            exit();
        } catch (Exception $e) {
            echo 'youare gay 3';
        }
    } else {
        echo 'youare gay 3';
    }
}