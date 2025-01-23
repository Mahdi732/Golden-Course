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
                WHERE 
                    courses.status = 'active'
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
            $stmt = $db->query("SELECT COUNT(*) FROM courses WHERE status = 'active'");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while fetching total courses.");
        }
    }

    public function updateCourse($course_id, $title, $description, $status, $course_type, $document_content, $video_url, $category_id, $teacher_id) {
        $db = $this->getDbConnection();
        try {
            $stmt = $db->prepare("
                UPDATE courses
                SET 
                    title = :title,
                    description = :description,
                    status = :status,
                    course_type = :course_type,
                    document_content = :document_content,
                    video_url = :video_url,
                    category_id = :category_id,
                    teacher_id = :teacher_id
                WHERE 
                    course_id = :course_id
            ");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':course_type', $course_type);
            $stmt->bindParam(':document_content', $document_content);
            $stmt->bindParam(':video_url', $video_url);
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
            $stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
            $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while updating the course.");
        }
    }

    public function deleteCourse($course_id) {
        $db = $this->getDbConnection();
        try {
            $stmt = $db->prepare("DELETE FROM courses WHERE course_id = :course_id");
            $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while deleting the course.");
        }
    }

    public function getAllCourseDetailsForTeacher($teacherId) {
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
                    users.username AS teacher_name
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
                    courses.teacher_id = :teacher_id
                GROUP BY 
                    courses.course_id
                ORDER BY 
                    courses.date_creation DESC
            ");
            $stmt->bindParam(':teacher_id', $teacherId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while fetching courses.");
        }
    }

    public function createCourse($title, $description, $status, $course_type, $document_content, $video_url, $category_id, $teacher_id) {
        $db = $this->getDbConnection();
        try {
            $stmt = $db->prepare("
                INSERT INTO courses (title, description, status, course_type, document_content, video_url, category_id, teacher_id)
                VALUES (:title, :description, :status, :course_type, :document_content, :video_url, :category_id, :teacher_id)
            ");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':course_type', $course_type);
            $stmt->bindParam(':document_content', $document_content);
            $stmt->bindParam(':video_url', $video_url);
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
            $stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while creating the course.");
        }
    }

    private function getStatusBadgeClass($status) {
        return $status === 'active' ? 'bg-success' : 'bg-danger';
    }

    public function displayCoursesForTeacher($teacherId) {
        try {
            $courses = $this->getAllCourseDetailsForTeacher($teacherId);
            if (empty($courses)) {
                echo '<div class="alert alert-info">No courses found.</div>';
                return;
            }
            ?>
            <div class="container-fluid py-4">
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($courses as $course): ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm hover-shadow transition-all">
                                <?php if ($course['video_url']): ?>
                                    <div class="card-img-top position-relative">
                                        <video 
                                            class="w-100" 
                                            style="height: 200px; object-fit: cover;"
                                            controls
                                            poster="path/to/your/default-thumbnail.jpg"
                                        >
                                            <source src="<?php echo $course['video_url']; ?>" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span id="course-status-badge-<?= $course['course_id']; ?>">
                                                <span class="badge <?php echo $this->getStatusBadgeClass($course['status']); ?>">
                                                    <?php echo $course['status'] === 'active' ? 'Accepted' : 'Rejected'; ?>
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="card-img-top position-relative bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <?php if ($course['document_content']): ?>
                                            <div class="text-center">
                                                <i class="fas fa-file-alt fa-3x text-primary mb-2"></i>
                                                <div class="document-preview">
                                                </div>
                                                <div class="position-absolute top-0 end-0 m-2">
                                                    <span id="course-status-badge-<?= $course['course_id']; ?>">
                                                        <span class="badge <?php echo $this->getStatusBadgeClass($course['status']); ?>">
                                                            <?php echo $course['status'] === 'active' ? 'Accepted' : 'Rejected'; ?>
                                                        </span>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center text-muted">
                                                <i class="fas fa-file-alt fa-3x mb-2"></i>
                                                <p>No content available</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="<?php echo $course['profile_image'] ?? 'assets/default-avatar.png'; ?>" 
                                             class="rounded-circle me-2" 
                                             alt="Teacher profile"
                                             width="40" height="40">
                                        <div>
                                            <h6 class="mb-0"><?php echo $course['teacher_name']; ?></h6>
                                            <small class="text-muted">Instructor</small>
                                        </div>
                                    </div>
        
                                    <h5 class="card-title mb-3"><?php echo $course['title']; ?></h5>
                                    
                                    <p class="card-text text-muted">
                                        <?php echo $course['description'], 0, 120 . '...'; ?>
                                    </p>
        
                                    <div class="mb-3">
                                        <span class="badge bg-primary">
                                            <?php echo $course['category_name']; ?>
                                        </span>
                                        <?php if ($course['tags']): ?>
                                            <?php foreach (explode(' ', $course['tags']) as $tag): ?>
                                                <span class="badge bg-light text-dark">
                                                    <?php echo $tag; ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
        
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="course-details.php?id=<?php echo $course['course_id']; ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            View Details
                                        </a>
                                        <div class="btn-group">
                                            <button type="button" 
                                                    id="edits-btn"
                                                    class="btn btn-outline-secondary btn-sm" 
                                                    onclick="editCourse(<?php echo $course['course_id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" action="delete_course.php" onsubmit="return confirm('Are you sure you want to delete this course?');">
                                                <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                                <button type="submit" id="delete" class="btn btn-outline-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        
            <style>
                .hover-shadow:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
                }
                .transition-all {
                    transition: all 0.3s ease;
                }
            </style>
            <?php
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Error displaying courses: " . $e->getMessage() . "</div>";
        }
    }
}
?>