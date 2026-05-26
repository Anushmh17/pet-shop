<?php
require_once '../includes/config.php';

$today = date('Y-m-d');
$w = (int)date('w'); // 0 (Sun) - 6 (Sat)
$start = date('Y-m-d', strtotime("-{$w} days"));

$stmt = $pdo->prepare("SELECT sale_date, SUM(total) as daily_total, COUNT(*) as cnt FROM sales WHERE sale_date BETWEEN ? AND ? GROUP BY sale_date ORDER BY sale_date ASC");
$stmt->execute([$start, $today]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Week start: {$start}, today: {$today}\n";
foreach ($rows as $r) {
    echo "{$r['sale_date']} -> count={$r['cnt']}, total={$r['daily_total']}\n";
}

?>