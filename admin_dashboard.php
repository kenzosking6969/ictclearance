<?php
session_start();

// Check if admin is not logged in
if (!isset($_SESSION['admin_username'])) {
    header('Location: index.php');
    exit();
}

// Generate a nonce for inline scripts and styles
$nonce = base64_encode(random_bytes(16));

// Include database connection
include 'db.php';

$student = null;
$clearance_status = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['studentId'])) {
    $studentId = htmlspecialchars($_POST['studentId']);

    // Fetch student information
    $sql = "SELECT id, id_number, fullname, year FROM students WHERE id_number = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("s", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        die("Error executing query: " . $stmt->error);
    }

    $student = $result->fetch_assoc();

    // Fetch clearance requirements for the entered student ID
    if ($student) {
        $sql = "SELECT c.id, c.description, c.status 
                FROM clearance c 
                WHERE c.student_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("i", $student['id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            die("Error executing query: " . $stmt->error);
        }

        while ($row = $result->fetch_assoc()) {
            $clearance_status[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Bulaga Kenneth">
    <meta name="description" content="ICT Clearance Admin Dashboard for St. John Paul II College of Davao.">
    <meta name="keywords" content="ICT Clearance, Admin Dashboard, SJPIICD, St. John Paul II College of Davao">
    <title>Admin Dashboard - ICT Clearance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.344.0/dist/umd/lucide.min.js" defer></script>
    <link href="styles.css" rel="stylesheet">
    <link rel="icon" href="images/CICT_Logo.png" type="image/png">
</head>
<body class="gradient-background dashboard-body">
    <nav class="navbar navbar-dark">
        <div class="container d-flex align-items-center">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i data-lucide="graduation-cap" class="text-warning me-2"></i>
                ICT Clearance System
            </a>
            <span class="nav-item dropdown">
                <button class="btn btn-outline-light dropdown-toggle user-dropdown-btn" 
                        type="button" 
                        id="userDropdown" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false">
                    <i data-lucide="user" class="me-2"></i>
                    <span class="user-id"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                </button>
                <ul class="dropdown-menu glass-dropdown" aria-labelledby="userDropdown">
                    <li>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
                            <i data-lucide="user" class="me-2"></i>
                            Profile
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="logout.php">
                            <i data-lucide="log-out" class="me-2"></i>
                            Logout
                        </a>
                    </li>
                </ul>
            </span>
        </div>
    </nav>

    <div class="toast-container position-fixed top-0 end-0 p-3" aria-live="polite" aria-atomic="true">
        <div id="notificationToast" class="toast" role="alert">
            <div class="toast-header">
                <i data-lucide="bell" class="text-warning me-2"></i>
                <strong class="me-auto">Notification</strong>
                <small>Just now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="notificationMessage">
            </div>
        </div>
    </div>

    <div class="container py-4">
        <header class="text-center mb-4 header-spacing">
            <h1 class="display-5 fw-bold text-white mb-2">Admin Dashboard</h1>
            <p class="text-blue-100">St. John Paul II College of Davao</p>
        </header>

        <section class="glass-card mb-4" aria-labelledby="search-student-form">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                <h2 id="search-student-form" class="text-white mb-3 mb-md-0">Search Student</h2>
            </div>
            <form method="POST" action="" class="row g-3">
                <div class="col-12">
                    <label for="studentId" class="form-label text-white">Student ID</label>
                    <div class="input-group">
                        <span class="input-group-text glass-input border-end-0">
                            <i data-lucide="search" class="text-blue-120"></i>
                        </span>
                        <input type="text" class="form-control glass-input border-start-0" 
                               id="studentId" name="studentId"
                               placeholder="Enter Student ID" maxlength="8" 
                               pattern="\d{8}" required>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning text-white">
                            <i data-lucide="search" class="me-2"></i>
                            Search
                        </button>
                        <button type="button" class="btn btn-outline-light" id="clearBtn">
                            <i data-lucide="x" class="me-2"></i>
                            Clear
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <?php if ($student): ?>
        <section class="glass-card mb-4" id="personalInfoCard" aria-labelledby="personal-info">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                <h2 id="personal-info" class="text-white mb-3 mb-md-0 d-flex align-items-center">
                    <i data-lucide="user" class="me-2"></i>
                    Personal Information
                </h2>
            </div>
            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-center info-card">
                        <i data-lucide="credit-card" class="text-warning me-2"></i>
                        <div>
                            <p class="text-blue-100 mb-0 small">Student ID</p>
                            <p class="text-white fw-medium mb-0"><?php echo htmlspecialchars($student['id_number']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-center info-card">
                        <i data-lucide="user" class="text-warning me-2"></i>
                        <div>
                            <p class="text-blue-100 mb-0 small">Full Name</p>
                            <p class="text-white fw-medium mb-0"><?php echo htmlspecialchars($student['fullname']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-center info-card">
                        <i data-lucide="graduation-cap" class="text-warning me-2"></i>
                        <div>
                            <p class="text-blue-100 mb-0 small">Year Level</p>
                            <p class="text-white fw-medium mb-0"><?php echo htmlspecialchars($student['year']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($clearance_status)): ?>
        <section class="glass-card" id="clearanceTableCard" aria-labelledby="clearance-requirements">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                <h2 id="clearance-requirements" class="text-white mb-3 mb-md-0">Clearance Requirements</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr class="border-bottom border-white-20">
                            <th class="desc-col text-white text-start">Description</th>
                            <th class="status-col text-white text-center">Status</th>
                            <th class="actions-col text-white text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clearance_status as $row): ?>
                        <tr data-id="<?php echo $row['id']; ?>">
                            <td class="desc-col text-white text-start">
                                <div class="desc-text"><?php echo htmlspecialchars($row['description']); ?></div>
                            </td>
                            <td class="status-col text-center">
                                <span class="badge <?php echo $row['status'] ? 'status-paid' : 'status-unpaid'; ?>">
                                    <i data-lucide="<?php echo $row['status'] ? 'check-circle' : 'alert-circle'; ?>" class="me-1"></i>
                                    <?php 
                                    if (strtolower($row['description']) === 'ict funday attendance') {
                                        echo $row['status'] ? 'Present' : 'Absent';
                                    } else {
                                        echo $row['status'] ? 'Paid' : 'Unpaid';
                                    }
                                    ?>
                                </span>
                            </td>
                            <td class="actions-col text-center">
                                <button class="btn btn-sm btn-warning edit-btn" data-clearance-id="<?php echo $row['id']; ?>">
                                    <i data-lucide="edit-2" class="me-1"></i>
                                    Edit
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>
    </div>

    <div class="modal fade" id="editStatusModal" tabindex="-1" aria-labelledby="editStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-0">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title text-white" id="editStatusModalLabel">
                        <i data-lucide="edit-2" class="text-warning me-2"></i>
                        Edit Status
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editStatusId">
                    <div class="mb-3">
                        <label for="editStatusSelect" class="form-label text-white">Status</label>
                        <select class="form-select glass-input" id="editStatusSelect">
                            <option value="1">Paid</option>
                            <option value="0">Unpaid</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning text-white" id="saveStatusBtn">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-0">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title text-white" id="profileModalLabel">
                        <i data-lucide="user" class="text-warning me-2"></i>
                        Admin Profile
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="profileForm">
                        <div class="mb-3">
                            <label for="adminUsername" class="form-label text-white">Username</label>
                            <div class="input-group">
                                <span class="input-group-text glass-input border-end-0">
                                    <i data-lucide="user" class="text-warning"></i>
                                </span>
                                <input type="text" class="form-control glass-input border-start-0" 
                                       id="adminUsername" name="username" 
                                       value="<?php echo htmlspecialchars($_SESSION['admin_username']); ?>" 
                                       required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label text-white">Current Password</label>
                            <div class="input-group">
                                <span class="input-group-text glass-input border-end-0">
                                    <i data-lucide="lock" class="text-warning"></i>
                                </span>
                                <input type="password" class="form-control glass-input border-start-0 border-end-0" 
                                       id="currentPassword" name="currentPassword" 
                                       placeholder="Enter current password">
                                <button class="input-group-text glass-input border-start-0 password-toggle" type="button">
                                    <i data-lucide="eye" class="text-warning"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label text-white">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text glass-input border-end-0">
                                    <i data-lucide="key" class="text-warning"></i>
                                </span>
                                <input type="password" class="form-control glass-input border-start-0 border-end-0" 
                                       id="newPassword" name="newPassword" 
                                       placeholder="Enter new password">
                                <button class="input-group-text glass-input border-start-0 password-toggle" type="button">
                                    <i data-lucide="eye" class="text-warning"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label text-white">Confirm New Password</label>
                            <div class="input-group">
                                <span class="input-group-text glass-input border-end-0">
                                    <i data-lucide="key" class="text-warning"></i>
                                </span>
                                <input type="password" class="form-control glass-input border-start-0 border-end-0" 
                                       id="confirmPassword" name="confirmPassword" 
                                       placeholder="Confirm new password">
                                <button class="input-group-text glass-input border-start-0 password-toggle" type="button">
                                    <i data-lucide="eye" class="text-warning"></i>
                                </button>
                            </div>
                        </div>
                        <div id="profileUpdateMessage" class="alert d-none"></div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning text-white" id="saveProfileBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin_dashboard.js" nonce="<?php echo $nonce; ?>"></script>
</body>
</html>
