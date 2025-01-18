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
                    categories.name AS category_name,
                    GROUP_CONCAT(tags.name) AS tags,
                    courses.status
                FROM 
                    courses
                LEFT JOIN 
                    categories ON courses.category_id = categories.category_id
                LEFT JOIN 
                    course_tags ON courses.course_id = course_tags.course_id
                LEFT JOIN 
                    tags ON course_tags.tag_id = tags.tag_id
                GROUP BY 
                    courses.course_id;
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while fetching courses.");
        }
    }

    public function displayCourses() {
        $courses = $this->getAllCourseDetails();
        if (empty($courses)) {
            echo "<p>No courses found.</p>";
            return;
        }
        foreach ($courses as $course) {
            ?>
            <div class="col-md-4">
                <div class="card user-card mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><?= $course['title']; ?></h5>
                        <p class="card-text"><?= $course['description']; ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary"><?= $course['category_name']; ?></span>
                            <div>
                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">Tags: <?= $course['tags']; ?></small>
                        </div>
                        <div class="mt-2">
                            <span class="badge <?= $course['status'] === 'accepted' ? 'bg-success' : 'bg-warning'; ?>">
                                <?= ucfirst($course['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <?php
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