<?php
function generateOrderNumber($conn) {
    $result = $conn->query("SELECT MAX(id) as max_id FROM orders");
    $row = $result->fetch_assoc();
    $next = ($row['max_id'] ?? 0) + 1;
    return 'AH-' . str_pad($next, 5, '0', STR_PAD_LEFT);
}

function getTodayOrders($conn) {
    $r = $conn->query("SELECT COUNT(*) as c, SUM(quantity) as q FROM orders WHERE date_ordered = CURDATE()");
    return $r->fetch_assoc();
}

function getWeekOrders($conn) {
    $r = $conn->query("SELECT COUNT(*) as c, SUM(quantity) as q FROM orders WHERE YEARWEEK(date_ordered, 1) = YEARWEEK(CURDATE(), 1)");
    return $r->fetch_assoc();
}

function getMonthOrders($conn) {
    $r = $conn->query("SELECT COUNT(*) as c, SUM(quantity) as q FROM orders WHERE MONTH(date_ordered) = MONTH(CURDATE()) AND YEAR(date_ordered) = YEAR(CURDATE())");
    return $r->fetch_assoc();
}

function getTotalSales($conn) {
    $r = $conn->query("SELECT SUM(CASE WHEN gallon_type='Slim' THEN quantity*" . PRICE_SLIM . " ELSE quantity*" . PRICE_ROUND . " END) as total FROM orders WHERE status != 'Cancelled'");
    $row = $r->fetch_assoc();
    return $row['total'] ?? 0;
}

function getWeeklySalesData($conn) {
    $r = $conn->query("SELECT DAYNAME(date_ordered) as day, SUM(CASE WHEN gallon_type='Slim' THEN quantity*" . PRICE_SLIM . " ELSE quantity*" . PRICE_ROUND . " END) as sales FROM orders WHERE YEARWEEK(date_ordered,1)=YEARWEEK(CURDATE(),1) AND status!='Cancelled' GROUP BY date_ordered ORDER BY date_ordered");
    $data = [];
    while ($row = $r->fetch_assoc()) $data[] = $row;
    return $data;
}

function getMonthlySalesData($conn) {
    $r = $conn->query("SELECT MONTHNAME(MIN(date_ordered)) as month, SUM(CASE WHEN gallon_type='Slim' THEN quantity*" . PRICE_SLIM . " ELSE quantity*" . PRICE_ROUND . " END) as sales FROM orders WHERE YEAR(date_ordered)=YEAR(CURDATE()) AND status!='Cancelled' GROUP BY MONTH(date_ordered) ORDER BY MONTH(date_ordered)");
    $data = [];
    while ($row = $r->fetch_assoc()) $data[] = $row;
    return $data;
}

function getDailyOrdersData($conn) {
    $r = $conn->query("SELECT DATE_FORMAT(date_ordered,'%b %d') as day, COUNT(*) as orders FROM orders WHERE date_ordered >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) GROUP BY date_ordered ORDER BY date_ordered");
    $data = [];
    while ($row = $r->fetch_assoc()) $data[] = $row;
    return $data;
}

function getInactiveCustomers($conn) {
    $r = $conn->query("SELECT customer_name FROM orders WHERE status != 'Cancelled' GROUP BY customer_name HAVING MAX(date_ordered) <= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)");
    $inactive = [];
    while ($row = $r->fetch_assoc()) {
        $inactive[$row['customer_name']] = true;
    }
    return $inactive;
}

function sanitize($conn, $val) {
    return $conn->real_escape_string(trim($val));
}
