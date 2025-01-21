<?php
require_once 'database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class CoursManagement {
    private static function getDbConnection() {
        return Database::getInstance()->getConnection();
    }

    public function getCourseDetailsById($course_id) {
        $db = $this->getDbConnection();
        try {
            $stmt = $db->prepare("
                SELECT 
                    courses.course_id,
                    courses.title,
                    courses.description,
                    courses.status,
                    courses.course_type,
                    courses.document_content,
                    courses.video_url,
                    categories.name AS category_name,
                    GROUP_CONCAT(tags.name SEPARATOR ', ') AS tags,
                    users.username AS teacher_name,
                    courses.date_creation
                FROM 
                    courses
                LEFT JOIN 
                    categories ON courses.category_id = categories.category_id
                LEFT JOIN 
                    course_tags ON courses.course_id = course_tags.course_id
                LEFT JOIN 
                    tags ON course_tags.tag_id = tags.tag_id
                LEFT JOIN 
                    users ON courses.teacher_id = users.user_id
                WHERE 
                    courses.course_id = :course_id
                GROUP BY 
                    courses.course_id
            ");
            $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while fetching the course details.");
        }
    }

    public function getCoursTags($course_id) {
        $db = $this->getDbConnection();
        try {
            $stmt = $db->prepare("
                SELECT tags.name 
                FROM tags
                INNER JOIN course_tags ON tags.tag_id = course_tags.tag_id
                WHERE course_tags.course_id = :course_id
            ");
            $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
            $stmt->execute();
            $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $tags;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while fetching course tags.");
        }
    }

    public function getPaginatedCourses($offset, $limit) {
        $db = $this->getDbConnection();
        try {
            $stmt = $db->prepare("
                SELECT 
                    courses.course_id,
                    courses.title,
                    courses.description,
                    courses.status,
                    courses.course_type,
                    courses.document_content,
                    courses.video_url,
                    categories.name AS category_name,
                    GROUP_CONCAT(tags.name SEPARATOR ', ') AS tags,
                    users.username AS teacher_name,
                    courses.date_creation
                FROM 
                    courses
                LEFT JOIN 
                    categories ON courses.category_id = categories.category_id
                LEFT JOIN 
                    course_tags ON courses.course_id = course_tags.course_id
                LEFT JOIN 
                    tags ON course_tags.tag_id = tags.tag_id
                LEFT JOIN 
                    users ON courses.teacher_id = users.user_id
                GROUP BY 
                    courses.course_id
                ORDER BY 
                    courses.date_creation DESC
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while fetching paginated courses.");
        }
    }

    public function getTotalCourses() {
        $db = $this->getDbConnection();
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM courses");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while fetching total courses.");
        }
    }
}
?>