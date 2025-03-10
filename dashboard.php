<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: index.php');
    exit();
}

// Include database connection
include 'db.php';

// Fetch current student's information using session ID
$stmt = $conn->prepare("SELECT * FROM students WHERE id_number = ?");
$stmt->bind_param("s", $_SESSION['student_id']);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// First get the student's ID (primary key) from the database
$stmt = $conn->prepare("SELECT id FROM students WHERE id_number = ?");
$stmt->bind_param("s", $_SESSION['student_id']);
$stmt->execute();
$result = $stmt->get_result();
$student_data = $result->fetch_assoc();
$student_id = $student_data['id'];

// Now fetch clearance requirements using the student's ID
$sql = "SELECT description, status FROM clearance WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id); // Note: using "i" for integer
$stmt->execute();
$clearance_result = $stmt->get_result();

// Prepare clearance status data for JavaScript
$clearance_status = [];
$total_items = 0;
$completed_items = 0;
while ($row = $clearance_result->fetch_assoc()) {
    $row['status'] = (int) $row['status'];
    $clearance_status[] = $row;
    $total_items++;
    if ($row['status'] === 1) {
        $completed_items++;
    }
}

// Calculate overall progress
$overall_progress = $total_items > 0 ? ($completed_items / $total_items) * 100 : 0;
$progress_colors = getProgressColorClass($overall_progress);

function getProgressColorClass($progress)
{
    if ($progress >= 100) {
        return ['text' => 'text-success progress-status', 'bg' => 'bg-success'];
    } elseif ($progress >= 50) {
        return ['text' => 'text-warning progress-status', 'bg' => 'bg-warning'];
    } else {
        return ['text' => 'text-danger progress-status', 'bg' => 'bg-danger'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Bulaga Kenneth">
    <meta name="description" content="ICT Clearance Dashboard for students of St. John Paul II College of Davao.">
    <meta name="keywords" content="ICT Clearance, Dashboard, SJPIICD, St. John Paul II College of Davao">
    <title>Dashboard - ICT Clearance System</title>
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
                <button class="btn btn-outline-light dropdown-toggle user-dropdown-btn" type="button" id="userDropdown"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i data-lucide="user" class="me-2"></i>
                    <span class="user-id"><?php echo htmlspecialchars($_SESSION['student_id']); ?></span>
                </button>
                <ul class="dropdown-menu glass-dropdown" aria-labelledby="userDropdown">
                    <li>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
                            <i data-lucide="user" class="me-2"></i>
                            Profile
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
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
            <h1 class="display-5 fw-bold text-white mb-2">ICT Clearance Dashboard</h1>
            <p class="text-blue-100">St. John Paul II College of Davao</p>
        </header>

        <section class="glass-card mb-4" aria-labelledby="overall-progress">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 id="overall-progress" class="text-white m-0">Overall Progress</h3>
                <span class="badge progress-status <?php echo $progress_colors['text']; ?>">
                    <?php echo round($overall_progress); ?>% Complete
                </span>
            </div>
            <div class="progress" style="height: 10px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated <?php echo $progress_colors['bg']; ?>"
                    role="progressbar" style="width: <?php echo $overall_progress; ?>%"
                    aria-valuenow="<?php echo $overall_progress; ?>" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
        </section>

        <section class="glass-card mb-4" aria-labelledby="personal-info">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 id="personal-info" class="text-white mb-0 d-flex align-items-center">
                    <i data-lucide="user" class="me-2"></i>
                    Personal Information
                </h2>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="d-flex align-items-center info-card">
                        <i data-lucide="credit-card" class="text-warning me-2"></i>
                        <div>
                            <p class="text-blue-100 mb-0 small">Student ID</p>
                            <p class="text-white fw-medium mb-0"><?php echo $student['id_number']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center info-card">
                        <i data-lucide="user" class="text-warning me-2"></i>
                        <div>
                            <p class="text-blue-100 mb-0 small">Full Name</p>
                            <p class="text-white fw-medium mb-0"><?php echo $student['fullname']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center info-card">
                        <i data-lucide="graduation-cap" class="text-warning me-2"></i>
                        <div>
                            <p class="text-blue-100 mb-0 small">Year Level</p>
                            <p class="text-white fw-medium mb-0"><?php echo $student['year']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="glass-card" aria-labelledby="clearance-requirements">
            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                <h2 id="clearance-requirements" class="text-white mb-3 mb-md-0">Clearance Requirements</h2>
                <div class="d-flex gap-2 flex-wrap">
                    <button
                        class="btn btn-sm btn-outline-light ms-2 btn-collection-schedule flex-grow-1 w-100 w-md-auto"
                        data-bs-toggle="modal" data-bs-target="#scheduleModal">
                        <i data-lucide="calendar" class="me-1"></i>
                        Collection Schedule
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr class="border-bottom border-white-20">
                            <th class="desc-col text-white text-start">Description</th>
                            <th class="status-col text-white text-center">Status</th>
                            <th class="remarks-col text-white text-center">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clearance_status as $row): ?>
                            <tr>
                                <td class="desc-col text-white text-start">
                                    <div class="desc-text"><?php echo $row['description']; ?></div>
                                </td>
                                <td class="status-col text-center">
                                    <span class="badge <?php
                                    if (strtolower($row['description']) === 'ict funday attendance') {
                                        echo $row['status'] ? 'status-present' : 'status-absent';
                                    } else {
                                        echo $row['status'] ? 'status-paid' : 'status-unpaid';
                                    }
                                    ?>">
                                        <i data-lucide="<?php echo $row['status'] ? 'check-circle' : 'alert-circle'; ?>"
                                            class="me-1"></i>
                                        <?php
                                        if (strtolower($row['description']) === 'ict funday attendance') {
                                            echo $row['status'] ? 'Present' : 'Absent';
                                        } else {
                                            echo $row['status'] ? 'Paid' : 'Unpaid';
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td class="remarks-col text-center">
                                    <span
                                        class="badge <?php echo $row['status'] ? 'remarks-cleared' : 'remarks-pending'; ?>">
                                        <i data-lucide="<?php echo $row['status'] ? 'check-circle' : 'x-circle'; ?>"
                                            class="me-1"></i>
                                        <?php
                                        if (strtolower($row['description']) === 'ict funday attendance') {
                                            echo $row['status'] ? 'Cleared' : 'Sanction + Letter';
                                        } else {
                                            echo $row['status'] ? 'Cleared' : 'Pending';
                                        }
                                        ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.clearanceStatus = <?php echo json_encode($clearance_status); ?>;
        window.overallProgress = <?php echo $overall_progress; ?>;
    </script>
    <script src="js/dashboard.js" nonce="<?php echo $nonce; ?>"></script>

    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-0">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title text-white" id="scheduleModalLabel">
                        <i data-lucide="calendar" class="text-warning me-2"></i>
                        Payment Collection Schedule
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="schedule-info">
                        <div class="schedule-day">
                            <i data-lucide="calendar-clock" class="text-warning mb-2"></i>
                            <h6 class="text-white">Collection Days</h6>
                            <p class="text-blue-100 mb-0">Thursday and Friday</p>
                        </div>
                        <div class="schedule-time">
                            <i data-lucide="clock" class="text-warning mb-2"></i>
                            <h6 class="text-white">Collection Hours</h6>
                            <p class="text-blue-100 mb-0">12:00 PM - 3:00 PM</p>
                        </div>
                    </div>
                    <div class="schedule-note mt-4">
                        <i data-lucide="info" class="text-warning me-2"></i>
                        <p class="text-blue-100 mb-0">
                            Please note: The collection venue is either Quadrangle or Canteen.
                        </p>
                    </div>
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
                        Student Profile
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="profileForm">
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label text-white">Current Password</label>
                            <div class="input-group">
                                <span class="input-group-text glass-input border-end-0">
                                    <i data-lucide="lock" class="text-warning"></i>
                                </span>
                                <input type="password" class="form-control glass-input border-start-0 border-end-0"
                                    id="currentPassword" name="currentPassword" placeholder="Enter current password">
                                <button class="input-group-text glass-input border-start-0 password-toggle"
                                    type="button">
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
                                    id="newPassword" name="newPassword" placeholder="Enter new password">
                                <button class="input-group-text glass-input border-start-0 password-toggle"
                                    type="button">
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
                                    id="confirmPassword" name="confirmPassword" placeholder="Confirm new password">
                                <button class="input-group-text glass-input border-start-0 password-toggle"
                                    type="button">
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
</body>

</html>