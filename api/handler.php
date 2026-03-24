<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

// Helper to respond with error
function respondError($msg) {
    echo json_encode(['error' => $msg]);
    exit;
}

// Read JSON input for POST requests
$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

switch ($action) {
    // --- PETS ---
    case 'getPets':
        try {
            $stmt = $pdo->query("SELECT * FROM pets ORDER BY id DESC");
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) { respondError($e->getMessage()); }
        break;

    case 'getPetImages':
        try {
            $petId = (int)($_GET['pet_id'] ?? 0);
            if (!$petId) { echo json_encode([]); break; }
            $stmt = $pdo->prepare("SELECT image_data FROM pet_images WHERE pet_id = ?");
            $stmt->execute([$petId]);
            $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo json_encode($rows);
        } catch (PDOException $e) { respondError($e->getMessage()); }
        break;

    case 'savePet':
        try {
            $p = $input;
            if (isset($p['id'])) {
                $sql = "UPDATE pets SET name=?, category=?, pet_variety=?, source=?, type=?, qty=?, price=?, cost=?, icon=?, alert_level=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $p['name'], $p['category'], $p['pet_variety'], $p['source'],
                    $p['type'], $p['qty'], $p['price'], $p['cost'],
                    $p['icon'], $p['alert_level'], $p['id']
                ]);
            } else {
                $sql = "INSERT INTO pets (name, category, pet_variety, source, type, qty, price, cost, icon, alert_level) VALUES (?,?,?,?,?,?,?,?,?,?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $p['name'], $p['category'], $p['pet_variety'], $p['source'],
                    $p['type'], $p['qty'], $p['price'], $p['cost'],
                    $p['icon'] ?? '🐾', $p['alert_level'] ?? 5
                ]);
            }
            echo json_encode(['success' => true]);
        } catch (PDOException $e) { respondError($e->getMessage()); }
        break;

    case 'updateStock':
        try {
            $stmt = $pdo->prepare("UPDATE pets SET qty = qty + ? WHERE id = ?");
            $stmt->execute([$input['change'], $input['id']]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) { respondError($e->getMessage()); }
        break;

    case 'toggleAlert':
        try {
            $stmt = $pdo->prepare("UPDATE pets SET stop_alert = ? WHERE id = ?");
            $stmt->execute([$input['stop'] ? 1 : 0, $input['id']]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) { respondError($e->getMessage()); }
        break;

    // --- SALES ---
    case 'getSales':
        try {
            $stmt = $pdo->query("SELECT * FROM sales ORDER BY id DESC");
            $sales = $stmt->fetchAll();
            foreach ($sales as &$s) {
                $s['id'] = (int)$s['id'];
                $s['petId'] = (int)$s['pet_id'];
                $s['qty'] = (int)$s['qty'];
                $s['price'] = (float)$s['price'];
                $s['total'] = (float)$s['total'];
                $s['date'] = $s['sale_date']; 
                $s['petName'] = $s['pet_name'];
                $s['petIcon'] = $s['pet_icon'];
            }
            echo json_encode($sales);
        } catch (PDOException $e) { respondError($e->getMessage()); }
        break;

    case 'addSale':
        try {
            $s = $input;
            $pdo->beginTransaction();

            $sql = "INSERT INTO sales (pet_id, pet_name, pet_icon, qty, price, total, sale_date)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $s['petId'], $s['petName'], $s['petIcon'],
                $s['qty'], $s['price'], $s['total'], 
                $s['saleDate'] ?? date('Y-m-d')
            ]);

            // Deduct Stock
            $upd = $pdo->prepare("UPDATE pets SET qty = qty - ? WHERE id = ?");
            $upd->execute([$s['qty'], $s['petId']]);

            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            respondError($e->getMessage()); 
        }
        break;

    // --- DRAWER ---
    case 'getDrawer':
        try {
            $date = $_GET['date'] ?? date('Y-m-d');
            $stmt = $pdo->prepare("SELECT drawer_data FROM drawer WHERE entry_date = ?");
            $stmt->execute([$date]);
            $row = $stmt->fetch();
            if ($row) {
                echo $row['drawer_data'];
            } else {
                echo json_encode((object)[]);
            }
        } catch (PDOException $e) { respondError($e->getMessage()); }
        break;

    case 'saveDrawer':
        try {
            $sql = "INSERT INTO drawer (entry_date, drawer_data) VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE drawer_data = VALUES(drawer_data)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$input['date'], json_encode($input['data'])]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) { respondError($e->getMessage()); }
        break;

    default:
        respondError('Action not found: ' . $action);
        break;
}
