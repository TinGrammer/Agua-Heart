<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Pagination
$perPage = 15;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Filters
$search  = trim($_GET['search'] ?? '');
$status  = $_GET['status'] ?? '';
$type    = $_GET['type'] ?? '';
$date    = $_GET['date'] ?? '';

$where = [];
$params = [];
$types = '';

if ($search) {
    $where[] = "(customer_name LIKE ? OR order_number LIKE ? OR contact_number LIKE ?)";
    $s = "%$search%";
    $params = array_merge($params, [$s, $s, $s]);
    $types .= 'sss';
}
if ($status) { $where[] = "status = ?"; $params[] = $status; $types .= 's'; }
if ($type)   { $where[] = "gallon_type = ?"; $params[] = $type; $types .= 's'; }
if ($date)   { $where[] = "date_ordered = ?"; $params[] = $date; $types .= 's'; }

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Count
$countStmt = $conn->prepare("SELECT COUNT(*) as c FROM orders $whereSQL");
if ($params) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['c'];
$totalPages = ceil($total / $perPage);

// Fetch
$stmt = $conn->prepare("SELECT * FROM orders $whereSQL ORDER BY created_at DESC LIMIT ? OFFSET ?");
$allParams = array_merge($params, [$perPage, $offset]);
$allTypes = $types . 'ii';
$stmt->bind_param($allTypes, ...$allParams);
$stmt->execute();
$orders = $stmt->get_result();
$inactiveCustomers = getInactiveCustomers($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders – Agua Heart Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💧</text></svg>">
</head>
<body>
<div class="admin-layout">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="../index.php" class="sidebar-logo">
                <div class="logo-icon">💧</div>
                <span>Agua Heart</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Main</div>
            <a href="dashboard.php"><span class="nav-icon">📊</span> Dashboard</a>
            <a href="orders.php" class="active"><span class="nav-icon">📋</span> Orders</a>
            <div class="nav-label">Analytics</div>
            <a href="reports.php"><span class="nav-icon">📈</span> Reports</a>
            <div class="nav-label">Site</div>
            <a href="../index.php" target="_blank"><span class="nav-icon">🌐</span> View Website</a>
            <a href="../order.php" target="_blank"><span class="nav-icon">🛒</span> Order Form</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php"><span>🚪</span> Logout</a>
        </div>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:15px">
                <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
                <div class="topbar-left">
                    <h2>Orders</h2>
                    <p>Manage all customer orders</p>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-date">📅 <?= date('F j, Y') ?></div>
                <div class="admin-avatar"><?= strtoupper(substr($_SESSION['admin_username'], 0, 1)) ?></div>
            </div>
        </div>

        <div class="page-content">
            <div class="table-card">
                <div class="table-header">
                    <h3>📋 All Orders <span style="font-size:0.8rem;color:#6c757d;font-weight:400">(<?= $total ?> total)</span></h3>
                    <div class="table-actions">
                        <a href="reports.php?export=csv<?= $search ? '&search='.urlencode($search) : '' ?><?= $status ? '&status='.urlencode($status) : '' ?>" class="btn-sm btn-success">⬇️ Export CSV</a>
                        <button onclick="window.print()" class="btn-sm btn-outline">🖨️ Print</button>
                    </div>
                </div>

                <!-- Search & Filter -->
                <form method="GET" class="search-filter">
                    <input type="text" name="search" class="search-input"
                           placeholder="🔍 Search name, order #, phone..."
                           value="<?= htmlspecialchars($search) ?>">
                    <select name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="Pending" <?= $status==='Pending'?'selected':'' ?>>⏳ Pending</option>
                        <option value="Delivered" <?= $status==='Delivered'?'selected':'' ?>>✅ Delivered</option>
                        <option value="Cancelled" <?= $status==='Cancelled'?'selected':'' ?>>❌ Cancelled</option>
                    </select>
                    <select name="type" class="filter-select">
                        <option value="">All Types</option>
                        <option value="Slim" <?= $type==='Slim'?'selected':'' ?>>🫙 Slim</option>
                        <option value="Round" <?= $type==='Round'?'selected':'' ?>>🪣 Round</option>
                    </select>
                    <input type="date" name="date" class="filter-select" value="<?= htmlspecialchars($date) ?>">
                    <button type="submit" class="btn-sm btn-primary">Filter</button>
                    <?php if ($search || $status || $type || $date): ?>
                        <a href="orders.php" class="btn-sm btn-outline">Clear</a>
                    <?php endif; ?>
                </form>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Contact</th>
                                <th>Location</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Amount</th>
                                <th>Date & Time</th>
                                <th>Notes</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($orders->num_rows === 0): ?>
                            <tr><td colspan="11" style="text-align:center;padding:40px;color:#6c757d">No orders found.</td></tr>
                        <?php else: ?>
                        <?php while ($row = $orders->fetch_assoc()):
                            $price = $row['gallon_type'] === 'Slim' ? PRICE_SLIM : PRICE_ROUND;
                            $amount = $price * $row['quantity'];
                            $isInactive = isset($inactiveCustomers[$row['customer_name']]);
                        ?>
                            <tr>
                                <td><strong style="color:#0077b6"><?= htmlspecialchars($row['order_number']) ?></strong></td>
                                <td>
                                    <div style="<?= $isInactive ? 'color:#dc3545;font-weight:700' : '' ?>"><?= htmlspecialchars($row['customer_name']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($row['contact_number']) ?></td>
                                <td style="max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="<?= htmlspecialchars($row['location']) ?>"><?= htmlspecialchars($row['location']) ?></td>
                                <td><span class="badge badge-<?= strtolower($row['gallon_type']) ?>"><?= $row['gallon_type'] ?></span></td>
                                <td><strong><?= $row['quantity'] ?></strong></td>
                                <td><strong style="color:#0077b6">₱<?= number_format($amount) ?></strong></td>
                                <td>
                                    <?= date('M j, Y', strtotime($row['date_ordered'])) ?><br>
                                    <small style="color:#6c757d"><?= date('g:i A', strtotime($row['time_ordered'])) ?></small>
                                </td>
                                <td style="max-width:120px;font-size:0.82rem;color:#6c757d">
                                    <?= $row['notes'] ? htmlspecialchars(substr($row['notes'], 0, 40)) . (strlen($row['notes']) > 40 ? '...' : '') : '—' ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= strtolower($row['status']) ?>">
                                        <?= $row['status'] === 'Pending' ? '⏳' : ($row['status'] === 'Delivered' ? '✅' : '❌') ?>
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <?php if ($row['status'] === 'Pending'): ?>
                                            <button class="btn-sm btn-success" onclick="updateStatus(<?= $row['id'] ?>,'Delivered')" title="Mark Delivered">✅</button>
                                            <button class="btn-sm btn-danger" onclick="updateStatus(<?= $row['id'] ?>,'Cancelled')" title="Cancel">❌</button>
                                        <?php elseif ($row['status'] === 'Delivered'): ?>
                                            <button class="btn-sm btn-warning" onclick="updateStatus(<?= $row['id'] ?>,'Pending')" title="Revert to Pending">↩️</button>
                                        <?php else: ?>
                                            <button class="btn-sm btn-warning" onclick="updateStatus(<?= $row['id'] ?>,'Pending')" title="Revert to Pending">↩️</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <span>Showing <?= min($offset + 1, $total) ?>–<?= min($offset + $perPage, $total) ?> of <?= $total ?> orders</span>
                    <div class="page-btns">
                        <?php for ($i = 1; $i <= $totalPages; $i++):
                            $q = http_build_query(array_merge($_GET, ['page' => $i]));
                        ?>
                            <a href="?<?= $q ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/dashboard.js"></script>
</body>
</html>
