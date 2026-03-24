<?php
/**
 * Pet Shop — Complete Visual Data Injector
 */
header('Content-Type: text/plain');
require_once '../includes/config.php';

try {
    echo "--- 🔌 CONNECIVITY TEST ---\n";
    echo "Host: " . DB_HOST . " | Port: 3307 | DB: " . DB_NAME . "\n";
    $pdo->query("SELECT 1");
    echo "✅ Success: Database ALIVE.\n\n";

    // 1. CLEAR OLD DATA
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("TRUNCATE TABLE pet_images;");
    $pdo->exec("TRUNCATE TABLE sales;");
    $pdo->exec("TRUNCATE TABLE drawer;");
    $pdo->exec("TRUNCATE TABLE pets;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "--- 🧹 CLEANUP ---\n";
    echo "✅ Success: Database Reset.\n\n";

    // 2. SEED PETS (This gives us pet_ids for sales)
    $pets = [
        ['Labrador', 'dog', 'Chocolate Hunter', 'Dealer Supplied', 'Single', 3, 15000.00, 12000.00, '🐶', 10],
        ['Siamese Cat', 'cat', 'Royal Blue Point', 'Customer Supplied', 'Single', 2, 9500.00, 7000.00, '🐱', 5],
        ['Indian Fantail', 'bird', 'Pigeon', 'Dealer Supplied', 'Pair/Couple', 8, 2500.00, 1800.00, '🕊️', 10],
        ['Red Cap Oranda', 'fish', 'Goldfish', 'Dealer Supplied', 'Single', 25, 450.00, 300.00, '🐠', 20],
        ['African Grey', 'bird', 'Parrot', 'Dealer Supplied', 'Single', 1, 85000.00, 75000.00, '🦜', 2]
    ];
    $ins = $pdo->prepare("INSERT INTO pets (name, category, pet_variety, source, type, qty, price, cost, icon, alert_level) VALUES (?,?,?,?,?,?,?,?,?,?)");
    foreach($pets as $p) { $ins->execute($p); }
    echo "--- 🏷️ PET INVENTORY ---\n";
    echo "✅ Success: 5 Pets Added.\n\n";

    // 3. SEED SALES (This triggers the Chart)
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    $sales = [
        [1, 'Labrador', '🐶', 2, 15000.00, 30000.00, $today],
        [2, 'Siamese Cat', '🐱', 1, 9500.00, 9500.00, $today],
        [4, 'Red Cap Oranda', '🐠', 4, 450.00, 1800.00, $yesterday],
        [1, 'Labrador', '🐶', 1, 15000.00, 15000.00, $yesterday]
    ];
    $sIns = $pdo->prepare("INSERT INTO sales (pet_id, pet_name, pet_icon, qty, price, total, sale_date) VALUES (?,?,?,?,?,?,?)");
    foreach($sales as $s) { $sIns->execute($s); }
    echo "--- 📊 PET SALES RECORDS ---\n";
    echo "✅ Success: 4 Performance data points added (Populates the Chart).\n\n";

    // 4. SEED FINANCIAL DATA (Drawer)
    $data = [
        'openingBalance' => 25000.00, 'cashIn' => 41300.00, 'cashOut' => 500.00, 'closingBalance' => 65800.00,
        'entries' => [
            ['type' => 'Cash In', 'desc' => 'Sale: Labradors (2)', 'amount' => 30000],
            ['type' => 'Cash In', 'desc' => 'Sale: Siamese (1)', 'amount' => 9500],
            ['type' => 'Cash In', 'desc' => 'Sale: Fish (4)', 'amount' => 1800],
            ['type' => 'Cash Out', 'desc' => 'Fish Food Restock', 'amount' => 500]
        ]
    ];
    $json = json_encode($data);
    $dIns = $pdo->prepare("INSERT INTO drawer (entry_date, drawer_data) VALUES (?, ?)");
    $dIns->execute([$today, $json]);
    $dIns->execute([$yesterday, $json]);
    echo "--- 💰 CASH DRAWER ---\n";
    echo "✅ Success: Drawer Logs Synchronized.\n\n";

    echo "🎉 COMPLETE! Dashboard, Sales, and Drawer are now full of data.\n";
    echo "🔄 REFRESH YOUR DASHBOARD NOW!";

} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
?>
