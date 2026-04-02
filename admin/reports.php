<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';
require_once '../includes/functions.php';

$filter  = $_GET['filter'] ?? 'today';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo   = $_GET['date_to'] ?? '';

// Build date condition
switch ($filter) {
    case 'today':
        $dateCondition = "date_ordered = CURDATE()";
        $label = 'Today – ' . date('F j, Y');
        break;
    case 'week':
        $dateCondition = "YEARWEEK(date_ordered,1) = YEARWEEK(CURDATE(),1)";
        $label = 'This Week';
        break;
    case 'month':
        $dateCondition = "MONTH(date_ordered)=MONTH(CURDATE()) AND YEAR(date_ordered)=YEAR(CURDATE())";
        $label = 'This Month – ' . date('F Y');
        break;
    case 'custom':
        $from = $dateFrom ?: date('Y-m-d');
        $to   = $dateTo   ?: date('Y-m-d');
        $dateCondition = "date_ordered BETWEEN '$from' AND '$to'";
        $label = date('M j, Y', strtotime($from)) . ' – ' . date('M j, Y', strtotime($to));
        break;
    default:
        $dateCondition = "date_ordered = CURDATE()";
        $label = 'Today';
}

// CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="AguaHeart_Report_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Order #', 'Customer Name', 'Contact', 'Location', 'Type', 'Qty', 'Amount', 'Date', 'Time', 'Status', 'Notes']);
    $res = $conn->query("SELECT * FROM orders WHERE $dateCondition ORDER BY date_ordered DESC, time_ordered DESC");
    while ($r = $res->fetch_assoc()) {
        $price = $r['gallon_type'] === 'Slim' ? PRICE_SLIM : PRICE_ROUND;
        fputcsv($out, [$r['order_number'], $r['customer_name'], $r['contact_number'], $r['location'], $r['gallon_type'], $r['quantity'], '₱' . ($price * $r['quantity']), $r['date_ordered'], $r['time_ordered'], $r['status'], $r['notes']]);
    }
    fclose($out);
    exit;
}

// Summary stats
$summary = $conn->query("SELECT COUNT(*) as orders, SUM(quantity) as gallons, SUM(CASE WHEN gallon_type='Slim' THEN quantity*" . PRICE_SLIM . " ELSE quantity*" . PRICE_ROUND . " END) as revenue, SUM(CASE WHEN status='Delivered' THEN 1 ELSE 0 END) as delivered, SUM(CASE WHEN status='Pending' THEN 1 ELSE 0 END) as pending FROM orders WHERE $dateCondition")->fetch_assoc();

// Orders list
$orders = $conn->query("SELECT * FROM orders WHERE $dateCondition ORDER BY date_ordered DESC, time_ordered DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports – Agua Heart Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💧</text></svg>">
    <style>
        @media print {
            .sidebar, .topbar, .report-filters, .table-actions, .sidebar-toggle { display: none !important; }
            .main-content { margin-left: 0 !important; }
            .page-content { padding: 0 !important; }
            .print-header { display: block !important; }
        }
        .print-header { display: none; text-align: center; margin-bottom: 20px; }
        .print-header h2 { font-size: 1.5rem; }
        .print-header p { color: #6c757d; }
    </style>
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
            <a href="orders.php"><span class="nav-icon">📋</span> Orders</a>
            <div class="nav-label">Analytics</div>
            <a href="reports.php" class="active"><span class="nav-icon">📈</span> Reports</a>
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
                    <h2>Reports</h2>
                    <p><?= htmlspecialchars($label) ?></p>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-date">📅 <?= date('F j, Y') ?></div>
                <div class="admin-avatar"><?= strtoupper(substr($_SESSION['admin_username'], 0, 1)) ?></div>
            </div>
        </div>

        <div class="page-content">

            <!-- Filters -->
            <form method="GET" class="report-filters">
                <div class="filter-group">
                    <label>Quick Filter</label>
                    <div style="display:flex;gap:8px;flex-wrap:wrap">
                        <?php foreach (['today' => '📅 Today', 'week' => '📆 This Week', 'month' => '🗓️ This Month', 'custom' => '🔧 Custom'] as $val => $lbl): ?>
                            <a href="?filter=<?= $val ?>" class="btn-sm <?= $filter === $val ? 'btn-primary' : 'btn-outline' ?>"><?= $lbl ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php if ($filter === 'custom'): ?>
                <div class="filter-group">
                    <label>From</label>
                    <input type="date" name="date_from" class="filter-select" value="<?= htmlspecialchars($dateFrom) ?>">
                </div>
                <div class="filter-group">
                    <label>To</label>
                    <input type="date" name="date_to" class="filter-select" value="<?= htmlspecialchars($dateTo) ?>">
                </div>
                <input type="hidden" name="filter" value="custom">
                <div class="filter-group" style="justify-content:flex-end">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn-sm btn-primary">Apply</button>
                </div>
                <?php endif; ?>
            </form>

            <!-- Summary Cards -->
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="val"><?= $summary['orders'] ?? 0 ?></div>
                    <div class="lbl">Total Orders</div>
                </div>
                <div class="summary-card" style="border-top-color:#28a745">
                    <div class="val" style="color:#28a745"><?= $summary['gallons'] ?? 0 ?></div>
                    <div class="lbl">Total Gallons</div>
                </div>
                <div class="summary-card" style="border-top-color:#6f42c1">
                    <div class="val" style="color:#6f42c1">₱<?= number_format($summary['revenue'] ?? 0) ?></div>
                    <div class="lbl">Total Revenue</div>
                </div>
                <div class="summary-card" style="border-top-color:#28a745">
                    <div class="val" style="color:#28a745"><?= $summary['delivered'] ?? 0 ?></div>
                    <div class="lbl">Delivered</div>
                </div>
                <div class="summary-card" style="border-top-color:#ffc107">
                    <div class="val" style="color:#ffc107"><?= $summary['pending'] ?? 0 ?></div>
                    <div class="lbl">Pending</div>
                </div>
            </div>

            <!-- Report Table -->
            <div class="table-card">
                <div class="table-header">
                    <h3>📋 Order Report — <?= htmlspecialchars($label) ?></h3>
                    <div class="table-actions">
                        <a href="?filter=<?= $filter ?><?= $dateFrom ? '&date_from='.$dateFrom : '' ?><?= $dateTo ? '&date_to='.$dateTo : '' ?>&export=csv" class="btn-sm btn-success">⬇️ Download CSV</a>
                        <button onclick="window.print()" class="btn-sm btn-outline">🖨️ Print Report</button>
                    </div>
                </div>

                <!-- Print Header (visible only on print) -->
                <div class="print-header">
                    <h2>💧 Agua Heart — Order Report</h2>
                    <p><?= htmlspecialchars($label) ?> | Generated: <?= date('F j, Y g:i A') ?></p>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Contact</th>
                                <th>Location</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $i = 1;
                        $totalAmt = 0;
                        if ($orders->num_rows === 0): ?>
                            <tr><td colspan="11" style="text-align:center;padding:40px;color:#6c757d">No orders found for this period.</td></tr>
                        <?php else:
                        while ($row = $orders->fetch_assoc()):
                            $price = $row['gallon_type'] === 'Slim' ? PRICE_SLIM : PRICE_ROUND;
                            $amount = $price * $row['quantity'];
                            $totalAmt += $amount;
                        ?>
                            <tr>
                                <td style="color:#6c757d"><?= $i++ ?></td>
                                <td><strong style="color:#0077b6"><?= htmlspecialchars($row['order_number']) ?></strong></td>
                                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                <td><?= htmlspecialchars($row['contact_number']) ?></td>
                                <td style="max-width:140px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($row['location']) ?></td>
                                <td><span class="badge badge-<?= strtolower($row['gallon_type']) ?>"><?= $row['gallon_type'] ?></span></td>
                                <td><strong><?= $row['quantity'] ?></strong></td>
                                <td><strong style="color:#0077b6">₱<?= number_format($amount) ?></strong></td>
                                <td><?= date('M j, Y', strtotime($row['date_ordered'])) ?></td>
                                <td><?= date('g:i A', strtotime($row['time_ordered'])) ?></td>
                                <td><span class="badge badge-<?= strtolower($row['status']) ?>"><?= $row['status'] ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                        <tr style="background:#f8fbff;font-weight:700">
                            <td colspan="7" style="text-align:right;padding:14px 20px">TOTAL:</td>
                            <td style="color:#0077b6;font-size:1rem">₱<?= number_format($totalAmt) ?></td>
                            <td colspan="3"></td>
                        </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="../assets/js/dashboard.js"></script>
</body>
</html>
