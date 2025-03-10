<?php
session_start();
include 'db.php';

$nonce = base64_encode(random_bytes(16));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = ['success' => false, 'message' => 'Invalid credentials'];

    if (isset($_POST['studentId'])) {
        $studentId = trim(htmlspecialchars($_POST['studentId']));
        $password = trim($_POST['password']);

        $sql = "SELECT * FROM students WHERE id_number = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Error preparing statement: " . $conn->error);
            $response['message'] = "Server error. Please try again later.";
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
        $stmt->bind_param("s", $studentId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();

            if (password_verify($password, $student['password'])) {
                $_SESSION['student_id'] = $student['id_number'];
                $response['success'] = true;
                $response['redirect'] = 'dashboard.php';
            } else {
                $response['message'] = "Invalid Student ID or Password";
            }
        } else {
            $response['message'] = "Invalid Student ID or Password";
        }
    } elseif (isset($_POST['adminUsername'])) {
        $adminUsername = trim(htmlspecialchars($_POST['adminUsername']));
        $adminPassword = trim(htmlspecialchars($_POST['adminPassword']));

        $sql = "SELECT * FROM super_admin WHERE username = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Error preparing statement: " . $conn->error);
            $response['message'] = "Server error. Please try again later.";
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
        $stmt->bind_param("s", $adminUsername);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            if (password_verify($adminPassword, $admin['password'])) {
                $_SESSION['admin_username'] = $adminUsername;
                $response['success'] = true;
                $response['redirect'] = 'admin_dashboard.php';
            } else {
                $response['message'] = "Invalid Username or Password";
            }
        } else {
            $response['message'] = "Invalid Username or Password";
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Bulaga Kenneth">
    <meta name="description"
        content="ICT Clearance System for St. John Paul II College of Davao (SJPIICD). A web-based system for managing and processing ICT department clearances.">
    <meta name="keywords" content="ICT Clearance, SJPIICD, St. John Paul II College of Davao, Clearance System">
    <meta name="application-name" content="ICT Clearance System">
    <meta name="robots" content="index, follow">
    <link rel="icon" href="images/CICT_Logo.png" type="image/png">
    <title>Login - ICT Clearance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.344.0/dist/umd/lucide.min.js"></script>
    <meta property="og:title" content="ICT Clearance System">
    <meta property="og:description"
        content="ICT Clearance System for St. John Paul II College of Davao (SJPIICD). A web-based system for managing and processing ICT department clearances.">
    <meta property="og:image" content="https://i.imgur.com/VzshIVz.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="https://ict-clearance.infinityfreeapp.com/index.php">
    <meta property="og:type" content="website">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
</head>

<body class="gradient-background">
    <main class="min-vh-100 gradient-background d-flex align-items-center justify-content-center" role="main">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <section class="glass-card login-card" aria-labelledby="login-heading">
                        <div class="text-center mb-4">
                            <div id="logoContainer" class="d-inline-block mb-3 text-center">
                                <img src="images/SJP_Logo.png" alt="School Logo">
                                <img src="images/ELITES_Logo.png" alt="Department Logo">
                                <img src="images/CICT_Logo.png" alt="ELITES Logo">
                                <div class="mt-2">
                                    <i data-lucide="graduation-cap" class="icon-large text-warning"></i>
                                </div>
                            </div>
                            <h1 id="login-heading" class="h3 fw-bold text-white mb-2">ICT Clearance System</h1>
                            <p class="text-blue-100">St. John Paul II College of Davao</p>
                        </div>

                        <form id="studentLoginForm" class="mb-3" method="POST" action="">
                            <div class="mb-3">
                                <label for="studentId" class="form-label text-white">Student ID</label>
                                <div class="input-group">
                                    <span class="input-group-text glass-input border-end-0">
                                        <i data-lucide="credit-card" class="text-warning me-2"></i>
                                    </span>
                                    <input type="text" class="form-control glass-input border-start-0" id="studentId"
                                        name="studentId" placeholder="Enter your Student ID" maxlength="8"
                                        pattern="\d{8}" autocomplete="username" autofocus required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label text-white">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text glass-input border-end-0">
                                        <i data-lucide="lock" class="me-2"></i>
                                    </span>
                                    <input type="password" class="form-control glass-input border-start-0 border-end-0"
                                        id="password" name="password" placeholder="DefaultPass: 123" maxlength="15"
                                        autocomplete="current-password" required>
                                    <button class="input-group-text glass-input border-start-0 password-toggle"
                                        type="button">
                                        <i data-lucide="eye" class="text-warning"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3 form-check d-flex align-items-center">
                                <input type="checkbox" class="form-check-input me-2" id="rememberMe" name="rememberMe">
                                <label class="form-check-label text-white" for="rememberMe">Remember me</label>
                            </div>
                            <div id="studentLoginError" class="alert alert-danger d-none"></div>
                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger">
                                    <?php echo $_SESSION['error'];
                                    unset($_SESSION['error']); ?>
                                </div>
                            <?php endif; ?>

                            <button type="submit" class="btn btn-warning text-white w-100">
                                <i data-lucide="log-in" class="me-2"></i> Login as Student
                            </button>
                        </form>

                        <div class="position-relative my-4">
                            <hr class="text-white-50">
                            <span
                                class="position-absolute top-50 start-50 translate-middle px-3 bg-transparent text-white-50">or</span>
                        </div>

                        <button class="btn btn-outline-light w-100 mb-3 premium-btn" data-bs-toggle="modal"
                            data-bs-target="#adminLoginModal">
                            <i data-lucide="shield" class="me-2"></i> Super Admin Login
                        </button>

                        <a href="attendance.php" class="btn btn-outline-light w-100 premium-btn">
                            <i data-lucide="qr-code" class="me-2"></i> Generate QR Code
                        </a>
                    </section>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="adminLoginModal" tabindex="-1" aria-labelledby="adminLoginModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-white" id="adminLoginModalLabel">
                        <i data-lucide="shield" class="me-2"></i> Super Admin Login
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="adminLoginForm" method="POST">
                        <div class="mb-3">
                            <label for="adminUsername" class="form-label text-white">Username</label>
                            <div class="input-group">
                                <span class="input-group-text glass-input border-end-0">
                                    <i data-lucide="user" class="text-blue-120"></i>
                                </span>
                                <input type="text" class="form-control glass-input border-start-0" id="adminUsername"
                                    name="adminUsername" placeholder="Enter admin username" maxlength="10"
                                    autocomplete="username" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="adminPassword" class="form-label text-white">Password</label>
                            <div class="input-group">
                                <span class="input-group-text glass-input border-end-0">
                                    <i data-lucide="lock" class="text-blue-120"></i>
                                </span>
                                <input type="password" class="form-control glass-input border-start-0 border-end-0"
                                    id="adminPassword" name="adminPassword" placeholder="Enter admin password"
                                    maxlength="15" autocomplete="current-password" required>
                                <button class="input-group-text glass-input border-start-0 password-toggle"
                                    type="button">
                                    <i data-lucide="eye" class="text-warning"></i>
                                </button>
                            </div>
                        </div>
                        <div id="adminLoginError" class="alert alert-danger d-none"></div>
                        <button type="submit" class="btn btn-warning text-white w-100">
                            <i data-lucide="log-in" class="me-2"></i> Login as Super Admin
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer mt-auto py-5">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-lg-5 col-md-6 mb-4 mb-lg-0">
                    <div class="d-flex align-items-center mb-4 justify-content-center justify-content-lg-start">
                        <div class="footer-logo-group">
                            <img src="images/CICT_Logo.png" alt="CICT Logo" height="50" class="me-2">
                            <img src="images/ELITES_Logo.png" alt="ELITES Logo" height="50">
                        </div>
                    </div>
                    <h5 class="text-white mb-3 text-center text-lg-start">ICT Clearance System</h5>
                    <p class="text-blue-100 mb-4 text-center text-lg-start">
                        Streamlining the ICT department clearance process for SJPIICD students with efficiency and
                        innovation.
                    </p>

                    <!-- Department Social Links -->
                    <div class="department-social text-center text-lg-start mb-4">
                        <h6 class="text-white-50 mb-3">ICT Department</h6>
                        <a href="https://www.facebook.com/sjp2cd.elites" class="btn btn-outline-light btn-floating me-2"
                            target="_blank" rel="noopener" aria-label="ICT Department Facebook">
                            <i data-lucide="facebook" class="social-icon"></i>
                            <span class="ms-2 d-none d-sm-inline">ICT ELITES</span>
                        </a>
                    </div>

                    <div class="developer-social text-center">
                        <h6 class="text-white-50 mb-3">Developer</h6>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="#" id="personal-facebook" class="btn btn-outline-primary btn-floating"
                                target="_blank" rel="noopener" aria-label="Developer's Facebook">
                                <i data-lucide="facebook" class="social-icon"></i>
                            </a>
                            <a href="#" id="github-link" class="btn btn-outline-light btn-floating" target="_blank"
                                rel="noopener" aria-label="Developer's GitHub">
                                <i data-lucide="github" class="social-icon"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Links Column -->
                <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                    <div class="quick-links-card p-4 rounded-3">
                        <h5 class="text-white mb-4">Quick Links</h5>
                        <ul class="list-unstyled footer-links">
                            <li class="mb-3">
                                <a href="#"
                                    class="text-blue-100 text-decoration-none footer-link d-flex align-items-center"
                                    data-bs-toggle="modal" data-bs-target="#aboutModal">
                                    <span class="link-icon-wrapper me-3">
                                        <i data-lucide="info" class="link-icon"></i>
                                    </span>
                                    About
                                </a>
                            </li>
                            <li class="mb-3">
                                <a href="#"
                                    class="text-blue-100 text-decoration-none footer-link d-flex align-items-center"
                                    data-bs-toggle="modal" data-bs-target="#privacyModal">
                                    <span class="link-icon-wrapper me-3">
                                        <i data-lucide="shield" class="link-icon"></i>
                                    </span>
                                    Privacy Policy
                                </a>
                            </li>
                            <li>
                                <a href="#"
                                    class="text-blue-100 text-decoration-none footer-link d-flex align-items-center"
                                    data-bs-toggle="modal" data-bs-target="#termsModal">
                                    <span class="link-icon-wrapper me-3">
                                        <i data-lucide="file-text" class="link-icon"></i>
                                    </span>
                                    Terms of Service
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="border-top border-white-20 mt-5 pt-4">
                <div class="row">
                    <div class="col-12 text-center">
                        <div class="developer-credit" data-message="">
                            <div class="fire-particles"></div>
                            <div class="developer-photo">
                                <img src="images/developer.jpg" alt="Bulaga Kenneth" class="rounded-circle">
                            </div>
                            <div class="developer-info">
                                <h6 class="mb-0 text-white">Bulaga Kenneth</h6>
                                <div class="rank-badge mt-1">
                                    <i data-lucide="swords" class="rank-icon" style="width: 16px; height: 16px;"></i>
                                    <span>E-Rank Developer</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <div class="modal fade" id="aboutModal" tabindex="-1" aria-labelledby="aboutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-0">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title text-white" id="aboutModalLabel">
                        <i data-lucide="info" class="text-warning me-2"></i>
                        About ICT Clearance System
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-blue-100">
                    <p>The ICT Clearance System is a web-based platform designed specifically for St. John Paul II
                        College of Davao's ICT Department. This system streamlines the clearance process for students,
                        making it more efficient and accessible.</p>

                    <h6 class="text-white mt-4 mb-3">Key Features:</h6>
                    <ul>
                        <li>Easy tracking of clearance requirements</li>
                        <li>Real-time status updates</li>
                        <li>Secure student authentication</li>
                        <li>Efficient administrative management</li>
                    </ul>

                    <h6 class="text-white mt-4 mb-3">System Version:</h6>
                    <p class="mb-0">Version 1.8.0 (2025)</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content glass-card border-0">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title text-white" id="privacyModalLabel">
                        <i data-lucide="shield" class="text-warning me-2"></i>
                        Privacy Policy
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-blue-100">
                    <h6 class="text-white mb-3">Information We Collect</h6>
                    <p>We collect and process the following information:</p>
                    <ul>
                        <li>Student ID number</li>
                        <li>Full name</li>
                        <li>Academic year level</li>
                        <li>Clearance status information</li>
                    </ul>

                    <h6 class="text-white mt-4 mb-3">How We Use Your Information</h6>
                    <p>Your information is used solely for:</p>
                    <ul>
                        <li>Managing your ICT department clearance</li>
                        <li>Verifying your identity</li>
                        <li>Communicating clearance status</li>
                        <li>Maintaining accurate records</li>
                    </ul>

                    <h6 class="text-white mt-4 mb-3">Data Security</h6>
                    <p>We implement security measures including:</p>
                    <ul>
                        <li>Encrypted passwords</li>
                        <li>Secure session management</li>
                        <li>Regular security updates</li>
                        <li>Limited administrative access</li>
                    </ul>

                    <h6 class="text-white mt-4 mb-3">Data Retention</h6>
                    <p>Student clearance records are maintained throughout your academic enrollment and for a reasonable
                        period afterward for administrative purposes.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content glass-card border-0">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title text-white" id="termsModalLabel">
                        <i data-lucide="file-text" class="text-warning me-2"></i>
                        Terms of Service
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-blue-100">
                    <h6 class="text-white mb-3">Acceptance of Terms</h6>
                    <p>By accessing and using the ICT Clearance System, you agree to these terms and conditions.</p>

                    <h6 class="text-white mt-4 mb-3">User Responsibilities</h6>
                    <ul>
                        <li>Maintain the confidentiality of your account credentials</li>
                        <li>Provide accurate and truthful information</li>
                        <li>Report any unauthorized access to your account</li>
                        <li>Comply with all school policies and regulations</li>
                    </ul>

                    <h6 class="text-white mt-4 mb-3">System Usage</h6>
                    <p>The system should be used for:</p>
                    <ul>
                        <li>Tracking clearance requirements</li>
                        <li>Viewing clearance status</li>
                        <li>Communicating with ICT department</li>
                        <li>Accessing clearance-related information</li>
                    </ul>

                    <h6 class="text-white mt-4 mb-3">Prohibited Activities</h6>
                    <ul>
                        <li>Attempting to bypass system security</li>
                        <li>Sharing account credentials</li>
                        <li>Submitting false information</li>
                        <li>Using the system for non-clearance purposes</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="system-message d-none" id="systemMessage">
        <div class="system-message-content">
            <span class="system-tag">🛑 [SYSTEM ERROR] 🛑</span> 
            <span class="message-text"></span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js" nonce="<?php echo $nonce; ?>"></script>
</body>

</html>