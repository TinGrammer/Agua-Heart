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

if ($action === 'update_payment') {
    $id = (int)($_POST['id'] ?? 0);
    $paymentType = strtolower(trim((string)($_POST['payment_type'] ?? '')));
    $amountRaw = trim((string)($_POST['amount'] ?? ''));

    if (!$id) {
        echo json_encode(['error' => 'Invalid order selected.']);
        exit;
    }

    $orderStmt = $conn->prepare("SELECT order_number FROM orders WHERE id = ?");
    $orderStmt->bind_param('i', $id);
    $orderStmt->execute();
    $order = $orderStmt->get_result()->fetch_assoc();
    $orderStmt->close();

    if (!$order) {
        echo json_encode(['error' => 'Invalid order selected.']);
        exit;
    }

    if ($paymentType === '') {
        $stmt = $conn->prepare("UPDATE orders SET payment_type = NULL, amount = CASE WHEN id = ? THEN NULL ELSE amount END WHERE order_number = ?");
        $stmt->bind_param('is', $id, $order['order_number']);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Update failed']);
        }
        exit;
    }

    if (!in_array($paymentType, ['paid', 'credit'], true)) {
        echo json_encode(['error' => 'Payment type must be Paid or Credit.']);
        exit;
    }

    if ($amountRaw === '' || !is_numeric($amountRaw) || (float)$amountRaw < 0 || !preg_match('/^\d+(?:\.\d{1,2})?$/', $amountRaw)) {
        echo json_encode(['error' => 'Amount is required, must be a number, and can have at most 2 decimal places.']);
        exit;
    }

    $amount = (float)$amountRaw;
    $amount = round($amount, 2);

    $stmt = $conn->prepare("UPDATE orders SET payment_type = ?, amount = CASE WHEN id = ? THEN ? ELSE amount END WHERE order_number = ?");
    $stmt->bind_param('sids', $paymentType, $id, $amount, $order['order_number']);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Update failed']);
    }
    exit;
}

echo json_encode(['error' => 'Unknown action']);
