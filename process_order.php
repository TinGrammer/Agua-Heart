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
$slimQty = max(0, (int)($_POST['slim_quantity'] ?? 0));
$roundQty = max(0, (int)($_POST['round_quantity'] ?? 0));
$notes   = sanitize($conn, $_POST['notes'] ?? '');

// Validate
if (!$name || !$phone || !$loc || ($slimQty === 0 && $roundQty === 0)) {
    echo json_encode(['error' => 'Please fill in all required fields and select at least one gallon quantity.']);
    exit;
}

if ($slimQty > 99) $slimQty = 99;
if ($roundQty > 99) $roundQty = 99;

$order_number = generateOrderNumber($conn);
$date = date('Y-m-d');
$time = date('H:i:s');
$success = true;

$items = [];
if ($slimQty > 0) $items[] = ['Slim', $slimQty];
if ($roundQty > 0) $items[] = ['Round', $roundQty];

$stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, contact_number, location, gallon_type, quantity, notes, date_ordered, time_ordered) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare order. Please try again.']);
    $conn->close();
    exit;
}

foreach ($items as [$type, $qty]) {
    $stmt->bind_param('sssssisss', $order_number, $name, $phone, $loc, $type, $qty, $notes, $date, $time);
    if (!$stmt->execute()) {
        $success = false;
        break;
    }
}

if ($success) {
    echo json_encode(['success' => true, 'order_number' => $order_number, 'name' => $name]);
} else {
    echo json_encode(['error' => 'Failed to save order. Please try again.']);
}

$stmt->close();
$conn->close();
