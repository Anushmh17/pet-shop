<?php
require_once '../includes/config.php';

echo "Sales counts:\n";
$today = date('Y-m-d');
$startWeek = date('Y-m-d', strtotime('Sunday this week'));
$startMonth = date('Y-m-01');

$stmt = $pdo->prepare('SELECT COUNT(*) FROM sales WHERE sale_date = ?');
$stmt->execute([$today]);
echo "Today ({$today}): " . $stmt->fetchColumn() . "\n";

$stmt = $pdo->prepare('SELECT COUNT(*) FROM sales WHERE sale_date >= ? AND sale_date <= ?');
$stmt->execute([$startWeek, $today]);
echo "This Week ({$startWeek} - {$today}): " . $stmt->fetchColumn() . "\n";

$stmt->execute([$startMonth, $today]);
echo "This Month ({$startMonth} - {$today}): " . $stmt->fetchColumn() . "\n";

// Also show total sales
$tot = $pdo->query('SELECT COUNT(*) FROM sales')->fetchColumn();
echo "Total sales rows: {$tot}\n";
?>