<?php
session_start();

// Generate a nonce for inline scripts
$nonce = base64_encode(random_bytes(16));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Bulaga Kenneth">
    <meta name="description" content="QR Code Generator for ICT Funday Attendance">
    <title>Generate QR Code - ICT Clearance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="attendance.css" rel="stylesheet">
    <link rel="icon" href="images/CICT_Logo.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.344.0/dist/umd/lucide.min.js"></script>
</head>

<body class="gradient-background">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="text-center mb-4">
                    <div class="logo-container mb-3">
                        <img src="images/SJP_Logo.png" alt="School Logo" class="logo">
                        <img src="images/CICT_Logo.png" alt="Department Logo" class="logo">
                    </div>
                    <h1 class="display-6 fw-bold text-white">QR Code Generator</h1>
                    <p class="text-blue-100">ICT Funday Attendance System</p>
                </div>

                <section class="glass-card" aria-labelledby="qr-form">
                    <form id="qrForm" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="lastName" class="form-label text-white">Last Name</label>
                            <div class="input-group">
                                <span class="input-group-text glass-input border-end-0">
                                    <i data-lucide="user" class="text-warning"></i>
                                </span>
                                <input type="text" class="form-control glass-input border-start-0" id="lastName"
                                    name="lastName" autofocus required placeholder="Enter your last name">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="firstName" class="form-label text-white">First Name</label>
                            <div class="input-group">
                                <span class="input-group-text glass-input border-end-0">
                                    <i data-lucide="user" class="text-warning"></i>
                                </span>
                                <input type="text" class="form-control glass-input border-start-0" id="firstName"
                                    name="firstName" required placeholder="Enter your first name">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="idNumber" class="form-label text-white">ID Number</label>
                            <div class="input-group">
                                <span class="input-group-text glass-input border-end-0">
                                    <i data-lucide="credit-card" class="text-warning"></i>
                                </span>
                                <input type="text" class="form-control glass-input border-start-0" id="idNumber"
                                    name="idNumber" required pattern="\d{8}" maxlength="8"
                                    placeholder="Enter your 8-digit ID number">
                            </div>
                            <div class="invalid-feedback">
                                Please enter a valid 8-digit ID number.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="program" class="form-label text-white">Program</label>
                            <div class="input-group">
                                <span class="input-group-text glass-input border-end-0">
                                    <i data-lucide="graduation-cap" class="text-warning"></i>
                                </span>
                                <select class="form-select glass-input border-start-0" id="program" name="program"
                                    required>
                                    <option value="" disabled selected>Select your program</option>
                                    <option value="Regular">Regular Program</option>
                                    <option value="Special">Special Program</option>
                                </select>
                            </div>
                            <div class="invalid-feedback">
                                Please select your program.
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-warning text-white w-100">
                                <i data-lucide="qr-code" class="me-2"></i>
                                Generate QR Code
                            </button>
                            <a href="index.php" class="btn btn-outline-light w-100 mt-2 premium-btn">
                                <i data-lucide="arrow-left" class="me-2"></i>
                                Back to Login
                            </a>
                        </div>
                    </form>
                </section>

                <div id="qrResult" class="glass-card mt-4 text-center d-none">
                    <h4 class="text-white mb-3">Your QR Code</h4>
                    <div id="qrcode" class="mb-3"></div>
                    <p class="text-blue-100 mb-4">
                        Present this QR code to the officers assigned during the event.
                    </p>
                    <button id="downloadQR" class="btn btn-warning text-white">
                        <i data-lucide="download" class="me-2"></i>
                        Download QR Code
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Load scripts in correct order -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script src="js/attendance.js" nonce="<?php echo $nonce; ?>"></script>
</body>

</html>