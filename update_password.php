<?php
require_once 'includes/db.php';

$new_password = 'erdie_manaay1728';
$hash = password_hash($new_password, PASSWORD_BCRYPT);

$stmt = $conn->prepare("UPDATE admins SET password = ? WHERE username = ?");
$stmt->bind_param('ss', $hash, $username);
$username = 'admin';
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "✅ Admin password updated successfully!";
} else {
    echo "❌ Failed to update password";
}
$stmt->close();
$conn->close();
?>
