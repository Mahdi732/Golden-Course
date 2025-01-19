<?php
require_once('database.php');
require_once('user.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Admin extends User {
    public function __construct($nom, $prenom, $email, $password = null, $role = null) {
        parent::__construct($nom, $prenom, $email, $password, $role);
    }

    private function getDbConnection() {
        return Database::getInstance()->getConnection();
    }

    public function getAllEtudients() {
        $db = $this->getDbConnection();
        try {
            $stmt = $db->prepare("SELECT * FROM users WHERE role = 'Etudiant'");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while fetching students.");
        }
    }

    public function displayEtudient() {
        $users = $this->getAllEtudients();
        if (empty($users)) {
            echo "<p>No users found.</p>";
            return;
        }
        foreach ($users as $user) {
            ?>
            <tr id="">
                <td><?= $user['username']; ?></td>
                <td><?= $user['role']; ?></td>
                <td><?= $user['email']; ?></td>
                <td><?= $user['created_at']; ?></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
            <?php
        }
    }

    public function getAllTeachers() {
        $db = $this->getDbConnection();
        try {
            $stmt = $db->prepare("SELECT * FROM users WHERE role = 'Enseignant'");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while fetching teachers.");
        }
    }

    public function toggleUserStatus($userId, $newStatus) {
        $db = $this->getDbConnection();
        try {
            $stmt = $db->prepare("UPDATE users SET is_active = :newStatus WHERE user_id = :userId");
            $stmt->bindParam(':newStatus', $newStatus, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $newStatus;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while updating user status.");
        }
    }

    public function displayUsers() {
        $users = $this->getAllTeachers();
        if (empty($users)) {
            echo "<p>No users found.</p>";
            return;
        }
        foreach ($users as $user) {
            ?>
            <tr id="user-row-<?= $user['user_id']; ?>">
                <td><?= $user['username']; ?></td>
                <td><?= $user['role']; ?></td>
                <td><?= $user['email']; ?></td>
                <td id="user-status-badge-<?= $user['user_id']; ?>">
                    <?php if ($user['is_active'] == 0): ?>
                        <span class="badge bg-danger">Inactive</span>
                    <?php else: ?>
                        <span class="badge bg-success">Active</span>
                    <?php endif; ?>
                </td>
                <td class="d-flex justify-content-start align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash"></i>
                    </button>
                    <form hx-post="../prosses/admin.php" hx-target="#user-status-badge-<?= $user['user_id']; ?>" hx-swap="innerHTML">
                        <input type="hidden" name="userId" value="<?= $user['user_id']; ?>">
                        <input type="hidden" name="newStatus" value="1">
                        <button type="submit" name="activateUser" class="btn btn-sm btn-outline-success">
                            Activate
                        </button>
                    </form>
                    <form hx-post="../prosses/admin.php" hx-target="#user-status-badge-<?= $user['user_id']; ?>" hx-swap="innerHTML">
                        <input type="hidden" name="userId" value="<?= $user['user_id']; ?>">
                        <input type="hidden" name="newStatus" value="0">
                        <button type="submit" name="deactivateUser" class="btn btn-sm btn-outline-danger">
                            Deactivate
                        </button>
                    </form>
                </td>
            </tr>
            <?php
        }
    }

    public function getAllCourseDetails() {
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
                GROUP BY 
                    courses.course_id
                ORDER BY 
                    courses.date_creation DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            echo "Database error: " . $e->getMessage();
            throw new Exception("An error occurred while fetching courses.");
        }
    }

    public function getStatusBadgeClass($status) {
        return match($status) {
            'accepted' => 'bg-success',
            'rejected' => 'bg-danger',
            'pending' => 'bg-warning',
            default => 'bg-secondary'
        };
    }

    public function displayCourses() {
        try {
            $courses = $this->getAllCourseDetails();
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
                                            <span class="badge <?php echo $this->getStatusBadgeClass($course['status']); ?>">
                                                <?php echo $course['status']; ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="card-img-top position-relative bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <?php if ($course['document_content']): ?>
                                            <div class="text-center">
                                                <i class="fas fa-file-alt fa-3x text-primary mb-2"></i>
                                                <div class="document-preview">
                                                    <?php 
                                                    $preview = substr(strip_tags($course['document_content']), 0, 100);
                                                    echo $preview . (strlen($course['document_content']) > 100 ? '...' : '');
                                                    ?>
                                                </div>
                                                <div class="position-absolute top-0 end-0 m-2">
                                                    <span class="badge <?php echo $this->getStatusBadgeClass($course['status']); ?>">
                                                        <?php echo $course['status']; ?>
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
                                                    class="btn btn-outline-secondary btn-sm" 
                                                    onclick="editCourse(<?php echo $course['course_id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($course['status'] !== 'accepted'): ?>
                                                <button type="button" 
                                                        class="btn btn-outline-success btn-sm"
                                                        onclick="acceptCourse(<?php echo $course['course_id']; ?>)">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" 
                                                    class="btn btn-outline-danger btn-sm"
                                                    onclick="deleteCourse(<?php echo $course['course_id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
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

    public function acceptCourse($courseId) {
        $db = $this->getDbConnection();
        try {
            $stmt = $db->prepare("UPDATE courses SET status = 'accepted' WHERE course_id = :courseId");
            $stmt->bindParam(':courseId', $courseId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while accepting the course.");
        }
    }

    public function rejectCourse($courseId) {
        $db = $this->getDbConnection();
        try {
            $stmt = $db->prepare("UPDATE courses SET status = 'rejected' WHERE course_id = :courseId");
            $stmt->bindParam(':courseId', $courseId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while rejecting the course.");
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $admin = new Admin("", "", "");
    if (isset($_POST['activateUser']) || isset($_POST['deactivateUser'])) {
        $userId = (int)$_POST['userId'];
        $newStatus = (int)$_POST['newStatus'];
        try {
            $admin->toggleUserStatus($userId, $newStatus);
            $statusBadge = $newStatus ? "<span class='badge bg-success'>Active</span>" : "<span class='badge bg-danger'>Inactive</span>";
            echo $statusBadge;
            exit;
        } catch (Exception $e) {
            echo "<span class='text-danger'>Error: " . $e->getMessage() . "</span>";
            exit;
        }
    }
    if (isset($_POST['acceptCourse'])) {
        $courseId = (int)$_POST['courseId'];
        try {
            $admin->acceptCourse($courseId);
            echo "<span class='badge bg-success'>Accepted</span>";
            exit;
        } catch (Exception $e) {
            echo "<span class='text-danger'>Error: " . $e->getMessage() . "</span>";
            exit;
        }
    }
    if (isset($_POST['rejectCourse'])) {
        $courseId = (int)$_POST['courseId'];
        try {
            $admin->rejectCourse($courseId);
            echo "<span class='badge bg-danger'>Rejected</span>";
            exit;
        } catch (Exception $e) {
            echo "<span class='text-danger'>Error: " . $e->getMessage() . "</span>";
            exit;
        }
    }
}
?>