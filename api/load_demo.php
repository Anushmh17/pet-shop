<?php
/**
 * Pet Shop — Complete Visual Data Injector for Reports
 */
header('Content-Type: text/plain');
require_once '../includes/config.php';

try {
    echo "--- 🔌 CONNECTIVITY TEST ---\n";
    echo "Host: " . DB_HOST . " | DB: " . DB_NAME . "\n";
    $pdo->query("SELECT 1");
    echo "✅ Success: Database ALIVE.\n\n";

    // 1. CLEAR OLD DATA
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("TRUNCATE TABLE pet_images;");
    $pdo->exec("TRUNCATE TABLE customer_suppliers;");
    $pdo->exec("TRUNCATE TABLE sales;");
    $pdo->exec("TRUNCATE TABLE drawer;");
    $pdo->exec("TRUNCATE TABLE pets;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "--- 🧹 CLEANUP ---\n";
    echo "✅ Success: Database Reset.\n\n";

    // 2. SEED COMPREHENSIVE PET INVENTORY
    $pets = [
        // Dogs
        ['Labrador', 'dog', 'Chocolate Hunter', 'Raja Pets Suppliers', 'Single', 3, 15000.00, 12000.00, '🐶', 10],
        ['German Shepherd', 'dog', 'Show Line', 'Ashan Dogs Ltd', 'Single', 2, 25000.00, 20000.00, '🐕', 5],
        ['Poodle', 'dog', 'Standard White', 'Raja Pets Suppliers', 'Pair/Couple', 2, 12000.00, 9000.00, '🐩', 8],
        ['Bulldog', 'dog', 'French', 'Ashan Dogs Ltd', 'Single', 1, 18000.00, 14000.00, '🐶', 4],
        
        // Cats
        ['Siamese Cat', 'cat', 'Royal Blue Point', 'Customer Supplied', 'Single', 2, 9500.00, 7000.00, '🐱', 5],
        ['Persian Cat', 'cat', 'White Fluffy', 'Cat Paradise', 'Single', 3, 12000.00, 9500.00, '🐱', 6],
        ['Bengal Cat', 'cat', 'Spotted', 'Cat Paradise', 'Single', 1, 18000.00, 15000.00, '🐱', 3],
        
        // Birds
        ['Indian Fantail', 'bird', 'Pigeon', 'Dealer Supplied', 'Pair/Couple', 8, 2500.00, 1800.00, '🕊️', 10],
        ['Sun Conure', 'bird', 'Parrot', 'Exotic Birds Co', 'Single', 4, 35000.00, 30000.00, '🦜', 5],
        ['African Grey', 'bird', 'Parrot', 'Exotic Birds Co', 'Single', 1, 85000.00, 75000.00, '🦜', 2],
        ['Budgerigar', 'bird', 'Blue Wave', 'Birds Paradise', 'Single', 15, 800.00, 500.00, '🦜', 20],
        
        // Fish
        ['Red Cap Oranda', 'fish', 'Goldfish', 'Dealer Supplied', 'Single', 25, 450.00, 300.00, '🐠', 20],
        ['Fancy Guppy', 'fish', 'Delta Tail', 'Aqua World', 'Single', 100, 150.00, 80.00, '🐟', 50],
        ['Betta Fish', 'fish', 'Siamese Fighter', 'Aqua World', 'Single', 12, 350.00, 200.00, '🐠', 15],
        
        // Rabbits
        ['Dwarf Hotot', 'rabbit', 'Snow White', 'Customer Supplied', 'Single', 5, 4500.00, 3500.00, '🐰', 10],
        ['Flemish Giant', 'rabbit', 'Brown', 'Happy Rabbits Farm', 'Single', 2, 6000.00, 4500.00, '🐰', 5],
    ];
    
    $petIns = $pdo->prepare("INSERT INTO pets (name, category, pet_variety, source, type, qty, price, cost, icon, alert_level, supplier_uid, supplier_name, payment_status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $petIds = [];
    $i = 0;
    foreach($pets as $p) {
        $supplierId = 'SUP-' . str_pad($i, 3, '0', STR_PAD_LEFT);
        $petIns->execute(array_merge($p, [$supplierId, $p[3], 'Paid']));
        $petIds[] = $pdo->lastInsertId();
        $i++;
    }
    echo "--- 🏷️ PET INVENTORY ---\n";
    echo "✅ Success: " . count($pets) . " Pets Added.\n\n";

    // 3. SEED CUSTOMER SUPPLIERS (Payment Tracking)
    $today = date('Y-m-d');
    $customerSuppliers = [
        // Customer 1 - Siamese Cat
        [
            'pet_id' => $petIds[4],
            'full_name' => 'Ravi Silva',
            'nic' => '931234567V',
            'address' => '45/2, Galle Road, Colombo 3',
            'cost_paid' => 7000.00,
            'description' => 'From my home breeding',
            'supplier_uid' => 'SUP-004',
            'payment_status' => 'Paid',
            'due_date' => null,
            'payment_note' => 'Paid cash on delivery'
        ],
        // Customer 2 - Dwarf Hotot
        [
            'pet_id' => $petIds[14],
            'full_name' => 'Nandana De Silva',
            'nic' => '198765432123',
            'address' => '123, Colombo Gardens, Mt. Lavinia',
            'cost_paid' => 0.00,
            'description' => 'Quality rabbit from farm',
            'supplier_uid' => 'SUP-015',
            'payment_status' => 'Pending',
            'due_date' => date('Y-m-d', strtotime('+7 days')),
            'payment_note' => 'Payment due within 1 week'
        ],
    ];
    
    $csIns = $pdo->prepare("INSERT INTO customer_suppliers (pet_id, full_name, nic, address, cost_paid, description, supplier_uid, payment_status, due_date, payment_note) VALUES (?,?,?,?,?,?,?,?,?,?)");
    foreach($customerSuppliers as $cs) {
        $csIns->execute([
            $cs['pet_id'], $cs['full_name'], $cs['nic'], $cs['address'], $cs['cost_paid'],
            $cs['description'], $cs['supplier_uid'], $cs['payment_status'], $cs['due_date'], $cs['payment_note']
        ]);
    }
    echo "--- 👥 CUSTOMER SUPPLIERS ---\n";
    echo "✅ Success: " . count($customerSuppliers) . " Supplier Records Added (Payment Tracking).\n\n";

    // 4. SEED SALES DATA (Multiple dates for reports)
    $salesData = [];
    $dates = [];
    for ($d = 30; $d >= 0; $d--) {
        $dates[] = date('Y-m-d', strtotime("-$d days"));
    }
    
    // Generate varied sales across dates
    $salesPairs = [
        [0, 'Labrador', '🐶', 1, 15000.00],
        [1, 'German Shepherd', '🐕', 1, 25000.00],
        [4, 'Siamese Cat', '🐱', 2, 9500.00],
        [6, 'Bengal Cat', '🐱', 1, 18000.00],
        [7, 'Indian Fantail', '🕊️', 2, 2500.00],
        [8, 'Sun Conure', '🦜', 1, 35000.00],
        [11, 'Red Cap Oranda', '🐠', 5, 450.00],
        [12, 'Fancy Guppy', '🐟', 10, 150.00],
        [13, 'Betta Fish', '🐠', 2, 350.00],
    ];
    
    $sIns = $pdo->prepare("INSERT INTO sales (pet_id, pet_name, pet_icon, qty, price, total, sale_date) VALUES (?,?,?,?,?,?,?)");
    foreach ($dates as $date) {
        // Random 1-3 sales per day
        $numSales = rand(1, 3);
        for ($s = 0; $s < $numSales; $s++) {
            $pair = $salesPairs[array_rand($salesPairs)];
            $qty = rand(1, 3);
            $total = $pair[4] * $qty;
            $sIns->execute([$petIds[$pair[0]], $pair[1], $pair[2], $qty, $pair[4], $total, $date]);
        }
    }
    echo "--- 📊 SALES RECORDS ---\n";
    echo "✅ Success: 30 days of randomized sales data added.\n\n";

    // 5. SEED DRAWER DATA (Daily cash tracking)
    $drawerIns = $pdo->prepare("INSERT INTO drawer (entry_date, drawer_data) VALUES (?, ?)");
    foreach ($dates as $date) {
        $opening = rand(15000, 35000);
        $cashIn = rand(5000, 25000);
        $cashOut = rand(500, 3000);
        $closing = $opening + $cashIn - $cashOut;
        
        $drawerData = [
            'openingBalance' => floatval($opening),
            'cashIn' => floatval($cashIn),
            'cashOut' => floatval($cashOut),
            'closingBalance' => floatval($closing),
            'entries' => [
                ['type' => 'Cash In', 'desc' => 'Pet Sales', 'amount' => rand(3000, 15000)],
                ['type' => 'Cash In', 'desc' => 'Other Income', 'amount' => rand(1000, 5000)],
                ['type' => 'Cash Out', 'desc' => 'Supplies', 'amount' => rand(500, 2000)],
                ['type' => 'Cash Out', 'desc' => 'Utilities', 'amount' => rand(300, 1500)],
            ]
        ];
        $drawerIns->execute([$date, json_encode($drawerData)]);
    }
    echo "--- 💰 CASH DRAWER RECORDS ---\n";
    echo "✅ Success: 30 days of drawer entries added.\n\n";

    echo "═══════════════════════════════════════════\n";
    echo "🎉 COMPLETE! All mock data loaded successfully.\n";
    echo "═══════════════════════════════════════════\n";
    echo "✅ " . count($pets) . " Pets\n";
    echo "✅ " . count($customerSuppliers) . " Customer Suppliers\n";
    echo "✅ 30+ Sales Transactions\n";
    echo "✅ 30 Days of Drawer Records\n\n";
    echo "📊 Ready for Reports: Sales, Payments, Inventory, Drawer\n";
    echo "🔄 REFRESH YOUR PAGES NOW!\n";

} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage();
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
?>
