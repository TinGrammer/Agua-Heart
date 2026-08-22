<?php
header('Content-Type: application/json');
require_once 'includes/db.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$name    = sanitize($conn, $_POST['customer_name'] ?? '');
$phone   = sanitize($conn, $_POST['contact_number'] ?? '');
$loc     = sanitize($conn, $_POST['location'] ?? '');
$type    = sanitize($conn, $_POST['gallon_type'] ?? '');
$qty     = (int)($_POST['quantity'] ?? 1);
$notes   = sanitize($conn, $_POST['notes'] ?? '');

// Validate
if (!$name || !$phone || !$loc || !in_array($type, ['Slim', 'Round']) || $qty < 1) {
    echo json_encode(['error' => 'Please fill in all required fields.']);
    exit;
}

if ($qty > 99) $qty = 99;

$order_number = generateOrderNumber($conn);
$date = date('Y-m-d');
$time = date('H:i:s');

$stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, contact_number, location, gallon_type, quantity, notes, date_ordered, time_ordered) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('sssssisss', $order_number, $name, $phone, $loc, $type, $qty, $notes, $date, $time);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'order_number' => $order_number, 'name' => $name]);
} else {
    echo json_encode(['error' => 'Failed to save order. Please try again.']);
}

$stmt->close();
$conn->close();
