<?php
/**
 * Pet Shop — Final Stability Reset & Data Loader
 */
header('Content-Type: text/plain');
require_once '../includes/config.php';

try {
    echo "--- 🔌 CONNECIVITY TEST ---\n";
    echo "Host: " . DB_HOST . " | Port: 3307 | DB: " . DB_NAME . "\n";
    $pdo->query("SELECT 1");
    echo "✅ Success: Database connection is ALIVE.\n\n";

    // 1. ENSURE TABLE COMPATIBILITY (Change JSON to LONGTEXT)
    echo "--- 🛠️ COMPATIBILITY FIX ---\n";
    $pdo->exec("ALTER TABLE drawer MODIFY COLUMN drawer_data LONGTEXT;");
    echo "✅ Success: Transformed 'drawer_data' to LONGTEXT (Full Compability).\n\n";

    // 2. CLEAR EXISTING DATA
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("TRUNCATE TABLE pet_images;");
    $pdo->exec("TRUNCATE TABLE sales;");
    $pdo->exec("TRUNCATE TABLE drawer;");
    $pdo->exec("TRUNCATE TABLE pets;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "--- 🧹 CLEANUP ---\n";
    echo "✅ Success: Old data cleared. Fresh start initialized.\n\n";

    // 3. SEED PETS
    $pets = [
        ['Labrador', 'dog', 'Chocolate Hunter', 'Dealer Supplied', 'Single', 3, 15000.00, 12000.00, '🐶', 10],
        ['Siamese Cat', 'cat', 'Royal Blue Point', 'Customer Supplied', 'Single', 2, 9500.00, 7000.00, '🐱', 5],
        ['Indian Fantail', 'bird', 'Pigeon', 'Dealer Supplied', 'Pair/Couple', 8, 2500.00, 1800.00, '🕊️', 10],
        ['Red Cap Oranda', 'fish', 'Goldfish', 'Dealer Supplied', 'Single', 25, 450.00, 300.00, '🐠', 20],
        ['African Grey', 'bird', 'Parrot', 'Dealer Supplied', 'Single', 1, 85000.00, 75000.00, '🦜', 2]
    ];
    $stmt = $pdo->prepare("INSERT INTO pets (name, category, pet_variety, source, type, qty, price, cost, icon, alert_level) VALUES (?,?,?,?,?,?,?,?,?,?)");
    foreach($pets as $p) { $stmt->execute($p); }
    echo "--- 🏷️ PET INVENTORY ---\n";
    echo "✅ Success: 5 proposal-grade pets added.\n\n";

    // 4. SEED FINANCIAL DATA (Three day window for timezone safety)
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $tomorrow = date('Y-m-d', strtotime('+1 day'));

    $data = [
        'openingBalance' => 12500.00, 'cashIn' => 15900.00, 'cashOut' => 2000.00, 'closingBalance' => 26400.00,
        'entries' => [
            ['type' => 'Cash In', 'desc' => 'Pet Sale - Labrador', 'amount' => 15000],
            ['type' => 'Cash In', 'desc' => 'Pet Sale - Oranda', 'amount' => 900],
            ['type' => 'Cash Out', 'desc' => 'Electricity Bill', 'amount' => 2000]
        ]
    ];
    $json = json_encode($data);

    $dStmt = $pdo->prepare("INSERT INTO drawer (entry_date, drawer_data) VALUES (?, ?)");
    $dStmt->execute([$yesterday, $json]);
    $dStmt->execute([$today, $json]);
    $dStmt->execute([$tomorrow, $json]);
    echo "--- 💰 CASH DRAWER ---\n";
    echo "✅ Success: Financial logs created for Yesterday, Today, and Tomorrow.\n\n";

    echo "🎉 ALL DONE! Your website is now fully populated and stable.\n";
    echo "🔄 CLOSE THIS TAB AND REFRESH YOUR DASHBOARD!";

} catch (PDOException $e) {
    echo "❌ FATAL ERROR: \n" . $e->getMessage();
    echo "\n\nTip: Make sure you created 'petshop_db' in phpMyAdmin and that MySQL is running on port 3307.";
}
?>
