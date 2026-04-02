<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'update_status') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';

    if (!$id || !in_array($status, ['Pending', 'Delivered', 'Cancelled'])) {
        echo json_encode(['error' => 'Invalid data']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Update failed']);
    }
    exit;
}

echo json_encode(['error' => 'Unknown action']);
