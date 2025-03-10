<?php
include 'db.php';

header('Content-Type: application/json');
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' https://cdn.jsdelivr.net; img-src 'self' data:; font-src 'self';");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

session_start();

if (!isset($_SESSION['admin_username'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];
$status = $data['status'];

$response = [];

if ($stmt = $conn->prepare("UPDATE clearance SET status = ? WHERE id = ?")) {
    $stmt->bind_param('ii', $status, $id);
    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['success'] = false;
        $response['error'] = $stmt->error;
    }
    $stmt->close();
} else {
    $response['success'] = false;
    $response['error'] = $conn->error;
}

echo json_encode($response);
?>