<?php
require_once '../includes/config.php';
set_time_limit(120);

try {
    echo "--- START: Filling sales for Today/This Week/This Month ---\n";

    // fetch pet ids
    $pets = $pdo->query("SELECT id, name, price, icon FROM pets")->fetchAll(PDO::FETCH_ASSOC);
    if (count($pets) === 0) throw new Exception('No pets found');

    $ins = $pdo->prepare("INSERT INTO sales (pet_id, pet_name, pet_icon, qty, price, total, sale_date) VALUES (?,?,?,?,?,?,?)");

    $added = 0;

    // Today's entries: 15
    $today = date('Y-m-d');
    for ($i=0;$i<15;$i++) {
        $p = $pets[array_rand($pets)];
        $qty = rand(1,3);
        $price = $p['price'] ?: rand(100,20000);
        $total = $qty * $price;
        $ins->execute([$p['id'], $p['name'], $p['icon'], $qty, $price, $total, $today]);
        $added++;
    }

    // This week (excluding today): 20 (distributed over last 6 days)
    for ($i=0;$i<20;$i++) {
        $daysAgo = rand(1,6);
        $date = date('Y-m-d', strtotime("-{$daysAgo} days"));
        $p = $pets[array_rand($pets)];
        $qty = rand(1,4);
        $price = $p['price'] ?: rand(100,20000);
        $total = $qty * $price;
        $ins->execute([$p['id'], $p['name'], $p['icon'], $qty, $price, $total, $date]);
        $added++;
    }

    // This month (excluding this week): 30 (distributed over days 7..29)
    for ($i=0;$i<30;$i++) {
        $daysAgo = rand(7,29);
        $date = date('Y-m-d', strtotime("-{$daysAgo} days"));
        $p = $pets[array_rand($pets)];
        $qty = rand(1,5);
        $price = $p['price'] ?: rand(100,20000);
        $total = $qty * $price;
        $ins->execute([$p['id'], $p['name'], $p['icon'], $qty, $price, $total, $date]);
        $added++;
    }

    echo "✅ Added {$added} sales entries.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

?>