<?php
require_once('database.php');
require_once('user.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Admin extends User {
    public function __construct($nom , $prenom , $email , $password = null, $role = null) {
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
            throw new Exception("An error occurred while fetching users.");
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
            throw new Exception("An error occurred while fetching users.");
        }
    }

    public function toggleUserStatus($userId, $currentStatus) {
        $db = $this->getDbConnection();
        try {
            $newStatus = $currentStatus ? 0 : 1;
            $stmt = $db->prepare("UPDATE users SET is_active = :newStatus WHERE user_id = :userId");
            $stmt->bindParam(':newStatus', $newStatus);
            $stmt->bindParam(':userId', $userId);
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
                        <input type="hidden" name="currentStatus" value="<?= (int)$user['is_active']; ?>" id="current-status-<?= $user['user_id']; ?>">
                        <button type="submit" name="toggleStatus" class="btn btn-sm btn-outline-primary">
                            Toggle Status
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
                    GROUP_CONCAT(tags.name) AS tags
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
    

}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['toggleStatus'])) {
    $userId = (int)$_POST['userId'];
    $currentStatus = (int)$_POST['currentStatus'];

    $admin = new Admin("", "", "");
    try {
        $newStatus = $admin->toggleUserStatus($userId, $currentStatus);
        $statusBadge = $newStatus ? "<span class='badge bg-success'>Active</span>" : "<span class='badge bg-danger'>Inactive</span>";
        echo $statusBadge;
        exit;
    } catch (Exception $e) {
        echo "<span class='text-danger'>Error: " . $e->getMessage() . "</span>";
        exit;
    }
}
?>