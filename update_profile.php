<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Set header to JSON
header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit();
}

$username = $data['username'] ?? '';
$currentPassword = $data['currentPassword'] ?? '';
$newPassword = $data['newPassword'] ?? '';
$confirmPassword = $data['confirmPassword'] ?? '';

// Get current admin info
$stmt = $conn->prepare("SELECT id, password FROM super_admin WHERE username = ?");
$stmt->bind_param("s", $_SESSION['admin_username']);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin) {
    echo json_encode(['success' => false, 'message' => 'Admin not found']);
    exit();
}

// Verify current password if provided
if ($currentPassword) {
    if (!password_verify($currentPassword, $admin['password'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit();
    }
}

// Start building the update query
$updates = [];
$types = "";
$params = [];

// Check if username is being updated
if ($username && $username !== $_SESSION['admin_username']) {
    // Check if new username already exists
    $stmt = $conn->prepare("SELECT id FROM super_admin WHERE username = ? AND id != ?");
    $stmt->bind_param("si", $username, $admin['id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        exit();
    }
    $updates[] = "username = ?";
    $types .= "s";
    $params[] = $username;
}

// Check if password is being updated
if ($newPassword) {
    if ($newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
        exit();
    }
    if (strlen($newPassword) < 3) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 3 characters long']);
        exit();
    }
    $updates[] = "password = ?";
    $types .= "s";
    $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
}

// If there are no updates
if (empty($updates)) {
    echo json_encode(['success' => false, 'message' => 'No changes to update']);
    exit();
}

// Build and execute the update query
$sql = "UPDATE super_admin SET " . implode(", ", $updates) . " WHERE id = ?";
$types .= "i";
$params[] = $admin['id'];

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    // Update session username if it was changed
    if ($username && $username !== $_SESSION['admin_username']) {
        $_SESSION['admin_username'] = $username;
    }
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating profile']);
}