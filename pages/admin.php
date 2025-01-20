<?php
session_start();
require_once('../prosses/admin.php');
$display = new Admin('', '', '');

try {
    $statistics = $display->getGlobalStatistics();
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduTech Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.min.js"></script>
    <script src="https://unpkg.com/htmx.org@2.0.4"></script>
    <style>
        .sidebar {
            min-height: 100vh;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar .nav-link {
            color: #333;
            padding: 0.8rem 1rem;
            margin: 0.2rem 0;
            border-radius: 0.5rem;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }
        
        .sidebar .nav-link i {
            width: 24px;
        }
        
        .content-area {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        
        .search-bar {
            max-width: 400px;
        }
        
        .user-card {
            transition: transform 0.2s;
        }
        
        .user-card:hover {
            transform: translateY(-5px);
        }
        
        .section {
            display: none;
        }
        
        .section.active {
            display: block;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-auto px-0 bg-white sidebar">
            <div class="p-3">
                <a href="index.php" class="d-flex align-items-center mb-4 text-decoration-none">
                    <i class="fas fa-graduation-cap fs-4 text-primary me-2"></i>
                    <span class="fs-4 text-dark">EduTech</span>
                </a>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="#dashboard" class="nav-link active" onclick="showSection('dashboard')">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#users" class="nav-link" onclick="showSection('users')">
                            <i class="fas fa-users"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#courses" class="nav-link" onclick="showSection('courses')">
                            <i class="fas fa-book"></i>
                            <span>Courses</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#teaching" class="nav-link" onclick="showSection('teaching')">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span>Teaching Management</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col content-area p-4">
            <!-- Top Navigation -->
            <nav class="navbar bg-white rounded-3 mb-4 p-3">
                <div class="container-fluid">
                    <div class="search-bar">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" placeholder="Search...">
                        </div>
                    </div>
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            <img src="https://via.placeholder.com/40" class="rounded-circle me-2" alt="Profile">
                            <span>Admin Name</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Dashboard Section -->
            <div id="dashboard" class="section active">
                <div class="row g-3">
                    <!-- Total Courses -->
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Courses</h5>
                                <h2 class="mb-0"><?php echo $statistics['total_courses']; ?></h2>
                            </div>
                        </div>
                    </div>

                    <!-- Courses by Category -->
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Courses by Category</h5>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($statistics['courses_by_category'] as $category): ?>
                                        <li><?php echo htmlspecialchars($category['category_name']) . ": " . $category['course_count']; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Course with Most Students -->
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Most Popular Course</h5>
                                <?php if (!empty($statistics['course_with_most_students'])): ?>
                                    <p class="mb-0"><?php echo htmlspecialchars($statistics['course_with_most_students']['title']); ?></p>
                                    <p class="mb-0"><?php echo $statistics['course_with_most_students']['student_count']; ?> students</p>
                                <?php else: ?>
                                    <p class="mb-0">No data available.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Top 3 Teachers -->
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Top 3 Teachers</h5>
                                <ol class="mb-0">
                                    <?php foreach ($statistics['top_3_teachers'] as $teacher): ?>
                                        <li><?php echo htmlspecialchars($teacher['teacher_name']) . " (" . $teacher['student_count'] . " students)"; ?></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Section -->
            <div id="users" class="section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Users Management</h4>
                    <button class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add User</button>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $display->displayEtudient();
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div id="courses" class="section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Courses Management</h4>
                    <button id="btn-categorie" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Course</button>
                    <div id="form-categories" class="absolute z-50 bg-gray-300 p-8 rounded-lg shadow-md w-full max-w-md hidden top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                        <h2 class="text-2xl font-bold mb-6 text-gray-800">Create Category</h2>
                        <form action="../prosses/admin.php" method="POST">
                            <div class="mb-4">
                                <label for="name" class="block text-gray-700 text-sm font-medium mb-2">Category Name</label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name_cate" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Enter category name"
                                    required
                                >
                            </div>
                            <button 
                                type="submit" 
                                class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 transition duration-200"
                            >
                                Create Category
                            </button>
                        </form>
                    </div>
                </div>
                <div class="row g-4">
                <?php
                    $display->displayCourses();
                ?>
                </div>
            </div>

            <!-- Teaching Management Section -->
            <div id="teaching" class="section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Teaching Management</h4>
                    <button class="btn btn-primary"><i class="fas fa-plus me-2"></i>Assign Teacher</button>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Email</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                    $display->displayUsers();
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('btn-categorie').addEventListener('click', function(e) {
    e.preventDefault();

    const formElement = document.getElementById('form-categories');
    formElement.classList.remove('hidden');

    const formData = new FormData(this);
    const categoryName = formData.get('name');

    if (!categoryName.trim()) {
        alert('Please enter a category name');
        return;
    }

    const category = {
        name: categoryName.trim()
    };

    console.log('New Category:', category);
    alert('Category created successfully!');
    this.reset();
});

function showSection(sectionId) {
    document.querySelectorAll('.section').forEach(section => {
        section.classList.remove('active');
    });
    
    const selectedSection = document.getElementById(sectionId);
    if (selectedSection) {
        selectedSection.classList.add('active');
    } else {
        console.error("Section not found:", sectionId);
    }
    
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        link.classList.remove('active');
    });
    const activeLink = document.querySelector(`a[href="#${sectionId}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    } else {
        console.error("Link not found for section:", sectionId);
    }
}

</script>

</body>
</html>