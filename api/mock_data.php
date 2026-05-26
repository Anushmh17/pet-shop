<?php
header('Content-Type: text/plain');
set_time_limit(120);
require_once '../includes/config.php';

// SVG-based small images (URI-encoded) for visual variety
$images = [
    'dog' => "data:image/svg+xml;utf8,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20width='80'%20height='80'%3E%3Crect%20width='100%25'%20height='100%25'%20fill='%23ffefef'/%3E%3Ctext%20x='50%25'%20y='50%25'%20dominant-baseline='middle'%20text-anchor='middle'%20font-size='36'%3E%F0%9F%90%B6%3C/text%3E%3C/svg%3E",
    'cat' => "data:image/svg+xml;utf8,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20width='80'%20height='80'%3E%3Crect%20width='100%25'%20height='100%25'%20fill='%23fff7e6'/%3E%3Ctext%20x='50%25'%20y='50%25'%20dominant-baseline='middle'%20text-anchor='middle'%20font-size='36'%3E%F0%9F%90%B1%3C/text%3E%3C/svg%3E",
    'bird' => "data:image/svg+xml;utf8,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20width='80'%20height='80'%3E%3Crect%20width='100%25'%20height='100%25'%20fill='%23e8fff0'/%3E%3Ctext%20x='50%25'%20y='50%25'%20dominant-baseline='middle'%20text-anchor='middle'%20font-size='34'%3E%F0%9F%9F%9C%EF%B8%8F%3C/text%3E%3C/svg%3E",
    'fish' => "data:image/svg+xml;utf8,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20width='80'%20height='80'%3E%3Crect%20width='100%25'%20height='100%25'%20fill='%23e6f2ff'/%3E%3Ctext%20x='50%25'%20y='50%25'%20dominant-baseline='middle'%20text-anchor='middle'%20font-size='36'%3E%F0%9F%90%A0%3C/text%3E%3C/svg%3E",
    'rabbit' => "data:image/svg+xml;utf8,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20width='80'%20height='80'%3E%3Crect%20width='100%25'%20height='100%25'%20fill='%23fff0f7'/%3E%3Ctext%20x='50%25'%20y='50%25'%20dominant-baseline='middle'%20text-anchor='middle'%20font-size='34'%3E%F0%9F%90%B0%3C/text%3E%3C/svg%3E",
];

try {
    echo "--- START: Inserting 50-entry mock data ---\n";
    $pdo->query("SELECT 1");

    // Reset tables
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE pet_images");
    $pdo->exec("TRUNCATE TABLE customer_suppliers");
    $pdo->exec("TRUNCATE TABLE sales");
    $pdo->exec("TRUNCATE TABLE drawer");
    $pdo->exec("TRUNCATE TABLE pets");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "✅ Cleared tables\n";

    // Generate 50 pets with varied categories and prices
    $categories = ['dog','cat','bird','fish','rabbit'];
    $sources = ['Dealer Supplied','Customer Supplied','Cat Paradise','Aqua World','Exotic Birds Co','Happy Rabbits Farm'];
    $types = ['Single','Pair/Couple'];
    $icons = ['dog'=>'🐶','cat'=>'🐱','bird'=>'🦜','fish'=>'🐠','rabbit'=>'🐰'];

    $insPet = $pdo->prepare("INSERT INTO pets (name, category, pet_variety, source, type, qty, price, cost, icon, alert_level) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $petCount = 50;
    for ($i=1;$i<=$petCount;$i++) {
        $cat = $categories[array_rand($categories)];
        $name = ucfirst($cat) . " #" . $i;
        $variety = ['Standard','Show Line','Mixed','Deluxe'][array_rand(['Standard','Show Line','Mixed','Deluxe'])];
        $source = $sources[array_rand($sources)];
        $type = $types[array_rand($types)];
        $qty = rand(1,50);
        switch ($cat) {
            case 'dog': $price = rand(8000,60000); break;
            case 'cat': $price = rand(5000,40000); break;
            case 'bird': $price = rand(500,90000); break;
            case 'fish': $price = rand(50,5000); break;
            default: $price = rand(500,10000); break;
        }
        $cost = intval($price * (0.7 + (rand(0,30)/100)));
        $icon = $icons[$cat];
        $alert = rand(1,20);
        $insPet->execute([$name, $cat, $variety, $source, $type, $qty, $price, $cost, $icon, $alert]);
    }
    echo "✅ {$petCount} pets inserted\n";

    // Map pet images by category for all pets
    $ids = $pdo->query("SELECT id, category FROM pets ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    $imgStmt = $pdo->prepare("INSERT INTO pet_images (pet_id, image_data) VALUES (?, ?)");
    foreach ($ids as $row) {
        $cat = $row['category'];
        $imgData = $images[$cat] ?? $images['dog'];
        $imgStmt->execute([$row['id'], $imgData]);
    }
    echo "✅ Images added for pets\n";

    // Add 50 suppliers (customers) — map one supplier per pet to avoid unique pet_id constraints
    $cs = $pdo->prepare("INSERT INTO customer_suppliers (pet_id, full_name, nic, nic_photo, address, cost_paid, description, supplier_uid, payment_status, due_date, payment_note) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
    $suppliers = 50;
    // shuffle pet ids and map first N pets to suppliers
    $petIds = $ids;
    shuffle($petIds);
    for ($s=1;$s<=$suppliers;$s++) {
        $pet = $petIds[($s-1) % count($petIds)];
        $name = "Supplier " . $s;
        // generate NIC either old (9 digits + V) or new (12 digits)
        if (rand(0,1)===0) {
            $nic = str_pad(strval(rand(100000000,999999999)),9,'0',STR_PAD_LEFT) . 'V';
        } else {
            $nic = strval(rand(100000000000,999999999999));
        }
        $photo = $images[$pet['category']] ?? $images['dog'];
        $address = rand(1,999) . ", Demo Road";
        $cost_paid = rand(0,1) ? rand(0,10000) : 0;
        $desc = 'Demo supplier record';
        $uid = 'SUP-' . str_pad($s,3,'0',STR_PAD_LEFT);
        $status = rand(0,1) ? 'Paid' : 'Pending';
        $due = ($status==='Pending') ? date('Y-m-d', strtotime('+'.rand(1,30).' days')) : null;
        $note = ($status==='Paid') ? 'Paid' : 'Awaiting payment';
        $cs->execute([$pet['id'], $name, $nic, $photo, $address, $cost_paid, $desc, $uid, $status, $due, $note]);
    }
    echo "✅ {$suppliers} customer supplier records added\n";

    // Sales: add 50 random sales spread across last 90 days
    $saleIns = $pdo->prepare("INSERT INTO sales (pet_id, pet_name, pet_icon, qty, price, total, sale_date) VALUES (?,?,?,?,?,?,?)");
    $saleCount = 50;
    for ($i=0;$i<$saleCount;$i++) {
        $p = $ids[array_rand($ids)];
        $qty = rand(1,5);
        // fetch price from pets table to use consistent price
        $priceRow = $pdo->prepare("SELECT price, name, icon FROM pets WHERE id = ?");
        $priceRow->execute([$p['id']]);
        $pr = $priceRow->fetch(PDO::FETCH_ASSOC);
        $price = $pr ? $pr['price'] : rand(100,10000);
        $name = $pr ? $pr['name'] : ('Pet '.$p['id']);
        $icon = $pr ? $pr['icon'] : $icons[$p['category']];
        $total = $price * $qty;
        $days = rand(0,89);
        $date = date('Y-m-d', strtotime("-{$days} days"));
        $saleIns->execute([$p['id'], $name, $icon, $qty, $price, $total, $date]);
    }
    echo "✅ {$saleCount} sales inserted\n";

    // Drawer records: 50 days of entries (most recent 50 days)
    $drawerIns = $pdo->prepare("INSERT INTO drawer (entry_date, drawer_data) VALUES (?, ?)");
    $drawerCount = 50;
    for ($d = 0; $d < $drawerCount; $d++) {
        $date = date('Y-m-d', strtotime("-{$d} days"));
        $opening = rand(1000,50000);
        $cashIn = rand(0,30000);
        $cashOut = rand(0,5000);
        $closing = $opening + $cashIn - $cashOut;
        $data = json_encode(['openingBalance'=>$opening,'cashIn'=>$cashIn,'cashOut'=>$cashOut,'closingBalance'=>$closing,'entries'=>[['type'=>'Sales','amount'=>$cashIn]]]);
        $drawerIns->execute([$date, $data]);
    }
    echo "✅ {$drawerCount} drawer records added\n";

    echo "--- COMPLETE: 50-entry Mock dataset ready ---\n";
    echo "Pets: {$petCount}, Sales: {$saleCount}, Drawer days: {$drawerCount}, Suppliers: {$suppliers}\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

?>