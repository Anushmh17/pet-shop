<?php
/**
 * Pet Shop Management — Unified Data Handler (API)
 * Handles all requests from storage.js (MySQL back-end)
 */
header('Content-Type: application/json');
require_once '../includes/config.php';

$action = $_GET['action'] ?? '';
$input  = json_decode(file_get_contents('php://input'), true);

switch ($action) {

    // --- PETS ---
    case 'getPets':
        try {
            // Get pets
            $stmt = $pdo->query("SELECT * FROM pets ORDER BY id DESC");
            $pets = $stmt->fetchAll();

            // Append images to each pet
            foreach ($pets as &$pet) {
                $imgStmt = $pdo->prepare("SELECT image_data FROM pet_images WHERE pet_id = ?");
                $imgStmt->execute([$pet['id']]);
                $pet['images'] = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
                // Type conversion for front-end
                $pet['qty'] = (int)$pet['qty'];
                $pet['price'] = (float)$pet['price'];
                $pet['alertLevel'] = (int)$pet['alert_level'];
                $pet['stopAlert'] = (bool)$pet['stop_alert'];
            }
            echo json_encode($pets);
        } catch (PDOException $e) { respondError($e->getMessage()); }
        break;

    case 'savePet':
        try {
            $p = $input;
            $sql = "INSERT INTO pets (name, category, source, type, qty, price, cost, icon, alert_level, stop_alert, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $p['name'], $p['category'], $p['source'], $p['type'],
                $p['qty'], $p['price'], $p['cost'] ?? 0, $p['icon'],
                $p['alertLevel'] ?? 10, $p['stopAlert'] ? 1 : 0, $p['notes'] ?? ''
            ]);
            $newId = $pdo->lastInsertId();

            // Save images
            if (!empty($p['images'])) {
                $imgStmt = $pdo->prepare("INSERT INTO pet_images (pet_id, image_data) VALUES (?, ?)");
                foreach ($p['images'] as $img) {
                    $imgStmt->execute([$newId, $img]);
                }
            }
            echo json_encode(['success' => true, 'id' => $newId]);
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
                $s['date'] = $s['sale_date']; // match frontend naming
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
                date('Y-m-d')
            ]);

            // Deduct Stock
            $upd = $pdo->prepare("UPDATE pets SET qty = qty - ? WHERE id = ?");
            $upd->execute([$s['qty'], $s['petId']]);

            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (PDOException $e) { 
            $pdo->rollBack();
            respondError($e->getMessage()); 
        }
        break;

    // --- DRAWER ---
    case 'getDrawer':
        try {
            $stmt = $pdo->prepare("SELECT drawer_data FROM drawer WHERE entry_date = ?");
            $stmt->execute([$_GET['date']]);
            $row = $stmt->fetch();
            echo $row ? $row['drawer_data'] : json_encode((object)[]);
        } catch (PDOException $e) { respondError($e->getMessage()); }
        break;

    case 'saveDrawer':
        try {
            $sql = "INSERT INTO drawer (entry_date, drawer_data) VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE drawer_data = VALUES(drawer_data)";
            $stmt = $pdo->prepare($sql);
            // Store the full drawer object (openingBalance, entries, closingBalance)
            $stmt->execute([$input['date'], json_encode($input['data'])]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) { respondError($e->getMessage()); }
        break;

    default:
        respondError('Action not found');
        break;
}

function respondError($msg) {
    http_response_code(500);
    echo json_encode(['error' => $msg]);
    exit;
}
?>
