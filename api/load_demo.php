<?php
/**
 * Pet Shop — One-Click Proposal Data Loader
 * Usage: Visit this file in your browser once to populate the DB with demo data.
 */
header('Content-Type: text/plain');
require_once '../includes/config.php';

try {
    // 1. CLEAR EXISTING DATA
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("TRUNCATE TABLE pet_images;");
    $pdo->exec("TRUNCATE TABLE sales;");
    $pdo->exec("TRUNCATE TABLE drawer;");
    $pdo->exec("TRUNCATE TABLE pets;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "CLEANUP: Database wiped successfully.\n";

    // 2. INSERT PETS (Proposal Inventory)
    $pets = [
        ['Labrador', 'dog', 'Chocolate Hunter', 'Dealer Supplied', 'Single', 3, 15000.00, 12000.00, '🐶', 10],
        ['Siamese Cat', 'cat', 'Royal Blue Point', 'Customer Supplied', 'Single', 2, 9500.00, 7000.00, '🐱', 5],
        ['Indian Fantail', 'bird', 'Pigeon', 'Dealer Supplied', 'Pair/Couple', 8, 2500.00, 1800.00, '🕊️', 10],
        ['Red Cap Oranda', 'fish', 'Goldfish', 'Dealer Supplied', 'Single', 25, 450.00, 300.00, '🐠', 20],
        ['Sun Conure', 'bird', 'Parrot', 'Dealer Supplied', 'Single', 4, 35000.00, 30000.00, '🦜', 5],
        ['Dwarf Hotot', 'rabbit', 'Snow White', 'Customer Supplied', 'Single', 5, 4500.00, 3500.00, '🐰', 10],
        ['African Grey', 'bird', 'Parrot', 'Dealer Supplied', 'Single', 1, 85000.00, 75000.00, '🦜', 2],
        ['Fancy Guppy', 'fish', 'Delta Tail', 'Dealer Supplied', 'Single', 100, 150.00, 80.00, '🐟', 50],
        ['German Shepherd', 'dog', 'Show Line', 'Dealer Supplied', 'Single', 2, 25000.00, 20000.00, '🐕', 5]
    ];

    $stmt = $pdo->prepare("INSERT INTO pets (name, category, pet_variety, source, type, qty, price, cost, icon, alert_level) VALUES (?,?,?,?,?,?,?,?,?,?)");
    foreach($pets as $p) { $stmt->execute($p); }
    echo "PETS: 9 unique pets added to inventory.\n";

    // 3. INSERT SALES
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $mar15 = date('Y-m').'-15';
    $febLast = date('Y-m-d', strtotime('last day of last month'));

    $sales = [
        [4, 'Red Cap Oranda', '🐠', 2, 450.00, 900.00, $today],
        [1, 'Labrador', '🐶', 1, 15000.00, 15000.00, $today],
        [3, 'Indian Fantail', '🕊️', 1, 2500.00, 2500.00, $yesterday],
        [8, 'Fancy Guppy', '🐟', 10, 150.00, 1500.00, $yesterday],
        [5, 'Sun Conure', '🦜', 1, 35000.00, 35000.00, $mar15],
        [1, 'Labrador', '🐶', 1, 15000.00, 15000.00, $febLast]
    ];

    $sStmt = $pdo->prepare("INSERT INTO sales (pet_id, pet_name, pet_icon, qty, price, total, sale_date) VALUES (?,?,?,?,?,?,?)");
    foreach($sales as $s) { $sStmt->execute($s); }
    echo "SALES: Sales history generated across Feb/Mar 2026.\n";

    // 4. INSERT CASH DRAWER
    $drDataPrev = [
        'openingBalance' => 10000.00, 'cashIn' => 4000.00, 'cashOut' => 1500.00, 'closingBalance' => 12500.00,
        'entries' => [
            ['type' => 'Cash In', 'desc' => 'Pet Sale - Indian Fantail', 'amount' => 2500],
            ['type' => 'Cash Out', 'desc' => 'Premium Bird Feed', 'amount' => 1000],
            ['type' => 'Cash Out', 'desc' => 'Electricity Bill', 'amount' => 500]
        ]
    ];
    $drDataToday = [
        'openingBalance' => 12500.00, 'cashIn' => 15900.00, 'cashOut' => 2000.00, 'closingBalance' => 26400.00,
        'entries' => [
            ['type' => 'Cash In', 'desc' => 'Pet Sale - Red Cap Oranda', 'amount' => 900],
            ['type' => 'Cash In', 'desc' => 'Pet Sale - Labrador', 'amount' => 15000],
            ['type' => 'Cash Out', 'desc' => 'Staff Salary Advance', 'amount' => 2000]
        ]
    ];

    $dStmt = $pdo->prepare("INSERT INTO drawer (entry_date, drawer_data) VALUES (?, ?)");
    $dStmt->execute([$yesterday, json_encode($drDataPrev)]);
    $dStmt->execute([$today, json_encode($drDataToday)]);
    echo "DRAWER: Financial logs for Yesterday & Today created with carryover.\n";

    echo "\n🎉 SUCCESS: Website is now populated with demo data. Close this tab and refresh your Dashboard!";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
