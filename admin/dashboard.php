<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';
require_once '../includes/functions.php';

$today   = getTodayOrders($conn);
$week    = getWeekOrders($conn);
$month   = getMonthOrders($conn);
$sales   = getTotalSales($conn);
$weekly  = getWeeklySalesData($conn);
$monthly = getMonthlySalesData($conn);
$daily   = getDailyOrdersData($conn);

// Gallon type breakdown
$typeRes = $conn->query("SELECT gallon_type, SUM(quantity) as total FROM orders GROUP BY gallon_type");
$typeData = ['slim' => 0, 'round' => 0];
while ($r = $typeRes->fetch_assoc()) {
    if ($r['gallon_type'] === 'Slim') $typeData['slim'] = (int)$r['total'];
    else $typeData['round'] = (int)$r['total'];
}

// Recent orders
$recent = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 8");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – Agua Heart Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💧</text></svg>">
</head>
<body>
<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="../index.php" class="sidebar-logo">
                <div class="logo-icon">💧</div>
                <span>Agua Heart</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Main</div>
            <a href="dashboard.php" class="active">
                <span class="nav-icon">📊</span> Dashboard
            </a>
            <a href="orders.php">
                <span class="nav-icon">📋</span> Orders
            </a>
            <div class="nav-label">Analytics</div>
            <a href="reports.php">
                <span class="nav-icon">📈</span> Reports
            </a>
            <div class="nav-label">Site</div>
            <a href="../index.php" target="_blank">
                <span class="nav-icon">🌐</span> View Website
            </a>
            <a href="../order.php" target="_blank">
                <span class="nav-icon">🛒</span> Order Form
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php">
                <span>🚪</span> Logout (<?= htmlspecialchars($_SESSION['admin_username']) ?>)
            </a>
        </div>
    </aside>

    <!-- Main -->
    <div class="main-content">
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:15px">
                <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
                <div class="topbar-left">
                    <h2>Dashboard</h2>
                    <p>Welcome back, <?= htmlspecialchars($_SESSION['admin_username']) ?>!</p>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-date">📅 <?= date('F j, Y') ?></div>
                <div class="admin-avatar"><?= strtoupper(substr($_SESSION['admin_username'], 0, 1)) ?></div>
            </div>
        </div>

        <div class="page-content">

            <!-- Stat Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">📦</div>
                    <div class="stat-info">
                        <div class="value"><?= $today['c'] ?? 0 ?></div>
                        <div class="label">Orders Today</div>
                        <div class="sub">+<?= $today['q'] ?? 0 ?> gallons</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">📅</div>
                    <div class="stat-info">
                        <div class="value"><?= $week['c'] ?? 0 ?></div>
                        <div class="label">Orders This Week</div>
                        <div class="sub">+<?= $week['q'] ?? 0 ?> gallons</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange">🗓️</div>
                    <div class="stat-info">
                        <div class="value"><?= $month['c'] ?? 0 ?></div>
                        <div class="label">Orders This Month</div>
                        <div class="sub">+<?= $month['q'] ?? 0 ?> gallons</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple">💰</div>
                    <div class="stat-info">
                        <div class="value">₱<?= number_format($sales) ?></div>
                        <div class="label">Total Sales</div>
                        <div class="sub">All time revenue</div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 1 -->
            <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>📊 Weekly Sales</h3>
                        <span>This Week</span>
                    </div>
                    <div style="height:250px">
                        <canvas id="weeklyChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>🫙 Gallon Types</h3>
                        <span>All Time</span>
                    </div>
                    <div style="height:250px">
                        <canvas id="typeChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Charts Row 2 -->
            <div class="charts-grid" style="grid-template-columns:1fr 1fr;margin-bottom:30px">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>📈 Monthly Sales</h3>
                        <span><?= date('Y') ?></span>
                    </div>
                    <div style="height:220px">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>📦 Daily Orders</h3>
                        <span>Last 14 Days</span>
                    </div>
                    <div style="height:220px">
                        <canvas id="dailyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="table-card">
                <div class="table-header">
                    <h3>🕐 Recent Orders</h3>
                    <a href="orders.php" class="btn-sm btn-outline">View All →</a>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Location</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $recent->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['order_number']) ?></strong></td>
                                <td>
                                    <div><?= htmlspecialchars($row['customer_name']) ?></div>
                                    <small style="color:#6c757d"><?= htmlspecialchars($row['contact_number']) ?></small>
                                </td>
                                <td><?= htmlspecialchars(substr($row['location'], 0, 30)) ?>...</td>
                                <td><span class="badge badge-<?= strtolower($row['gallon_type']) ?>"><?= $row['gallon_type'] ?></span></td>
                                <td><strong><?= $row['quantity'] ?></strong></td>
                                <td><?= date('M j', strtotime($row['date_ordered'])) ?><br><small style="color:#6c757d"><?= date('g:i A', strtotime($row['time_ordered'])) ?></small></td>
                                <td>
                                    <span class="badge badge-<?= strtolower($row['status']) ?>">
                                        <?= $row['status'] === 'Pending' ? '⏳' : ($row['status'] === 'Delivered' ? '✅' : '❌') ?>
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <?php if ($row['status'] === 'Pending'): ?>
                                            <button class="btn-sm btn-success" onclick="updateStatus(<?= $row['id'] ?>,'Delivered')">✅</button>
                                            <button class="btn-sm btn-danger" onclick="updateStatus(<?= $row['id'] ?>,'Cancelled')">❌</button>
                                        <?php else: ?>
                                            <span style="color:#6c757d;font-size:0.8rem">—</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
window.weeklyData  = <?= json_encode($weekly) ?>;
window.monthlyData = <?= json_encode($monthly) ?>;
window.dailyData   = <?= json_encode($daily) ?>;
window.typeData    = <?= json_encode($typeData) ?>;
</script>
<script src="../assets/js/dashboard.js"></script>
</body>
</html>
