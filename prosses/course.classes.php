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
            return $db->lastInsertId(); 
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
            return $db->lastInsertId(); 
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

class TagManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function handleTags($tags, $course_id) {
        foreach ($tags as $tag) {
            $tag = trim($tag);
            if (!empty($tag)) {
                $tag_id = $this->getTagId($tag);
                if (!$tag_id) {
                    $tag_id = $this->insertTag($tag);
                }
                $this->insertCourseTag($course_id, $tag_id);
            }
        }
    }

    private function getTagId($tag) {
        $stmt = $this->db->prepare("SELECT tag_id FROM tags WHERE name = :name");
        $stmt->bindParam(':name', $tag);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function insertTag($tag) {
        $stmt = $this->db->prepare("INSERT INTO tags (name) VALUES (:name)");
        $stmt->bindParam(':name', $tag);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    private function insertCourseTag($course_id, $tag_id) {
        $stmt = $this->db->prepare("INSERT INTO course_tags (course_id, tag_id) VALUES (:course_id, :tag_id)");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':tag_id', $tag_id);
        $stmt->execute();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['CreateCourseSub'])) {
    if (empty($_POST['course_title']) || empty($_POST['course_description']) || empty($_POST['categories_select']) || empty($_POST['course_price']) || empty($_POST['course_type'])) {
        echo 'fill your form corect';
    }

    $course_title = $_POST['course_title'];
    $course_description = $_POST['course_description'];
    $categories_select = $_POST['categories_select'];
    $course_price = $_POST['course_price'];
    $course_type = $_POST['course_type'];
    $tags = [];
    if (!empty($_POST['tags'])) {
        $tagsInput = json_decode($_POST['tags'], true); 
        if (is_array($tagsInput)) {
            foreach ($tagsInput as $tag) {
                if (isset($tag['value'])) {
                    $tags[] = trim($tag['value']); 
                }
            }
        }
    }

    if ($course_type === 'video') {
        if (!isset($_FILES['video_file']) || $_FILES['video_file']['error'] !== UPLOAD_ERR_OK) {
            echo 'theres probleme whit your file video';
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
                $course_id = $course->ajouterCours(); 
                $tagManager = new TagManager();
                $tagManager->handleTags($tags, $course_id);
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => 'Video course added successfully!'
                ];
                header('Location: ../pages/teacher.php');
                exit();
            } catch (Exception $e) {
                echo 'Error adding video course' . $e->getMessage();
            }
        } else {
            echo 'Error uploading video file.';
        }
    }
    elseif ($course_type === 'document') {
        echo 'Document content is required.';    
        }

        $content = $_POST['document_content'];
        try {
            $course = new DocumentCours($course_title, $course_description, $content, $course_price, $categories_select, $_SESSION['user']['id']);
            $course_id = $course->ajouterCours(); 
            $tagManager = new TagManager();
            $tagManager->handleTags($tags, $course_id);

            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Document course added successfully!'
            ];
            header('Location: ../pages/teacher.php');
            exit();
        } catch (Exception $e) {
            echo 'Error adding document course: ' . $e->getMessage();
        }
    } else {
        echo 'Invalid course type.';
    }
