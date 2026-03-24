<?php
header('Content-Type: application/json');
session_start();
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

    case 'getCustomerSupplier':
        try {
            $petId = (int)($_GET['pet_id'] ?? 0);
            if (!$petId) { echo json_encode(null); break; }
            $stmt = $pdo->prepare("SELECT * FROM customer_suppliers WHERE pet_id = ?");
            $stmt->execute([$petId]);
            $row = $stmt->fetch();
            echo json_encode($row ?: null);
        } catch (PDOException $e) { respondError($e->getMessage()); }
        break;

    case 'saveCustomerSupplier':
        try {
            $d = $input;
            $sql = "INSERT INTO customer_suppliers (pet_id, full_name, nic, nic_photo, address, cost_paid, description)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        full_name=VALUES(full_name), nic=VALUES(nic), nic_photo=VALUES(nic_photo),
                        address=VALUES(address), cost_paid=VALUES(cost_paid), description=VALUES(description)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                (int)$d['pet_id'],
                $d['full_name'] ?? '',
                $d['nic']       ?? '',
                $d['nic_photo'] ?? null,
                $d['address']   ?? '',
                (float)($d['cost_paid'] ?? 0),
                $d['description'] ?? ''
            ]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) { respondError($e->getMessage()); }
        break;

    case 'savePet':
        try {
            $p = $input;
            $isUpdate = isset($p['id']);
            
            // Map camelCase from JS to snake_case for DB
            $name     = $p['name'] ?? '';
            $cat      = $p['category'] ?? '';
            $variety  = $p['petVariety'] ?? '';
            $src      = $p['source'] ?? '';
            $type     = $p['type'] ?? 'Single';
            $qty      = (int)($p['qty'] ?? 0);
            $price    = (float)($p['price'] ?? 0);
            $cost     = (float)($p['cost'] ?? 0);
            $icon     = $p['icon'] ?? '🐾';
            $alert    = (int)($p['alertLevel'] ?? 5);
            $notes    = $p['notes'] ?? '';

            if ($isUpdate) {
                $sql = "UPDATE pets SET name=?, category=?, pet_variety=?, source=?, type=?, qty=?, price=?, cost=?, icon=?, alert_level=?, notes=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $cat, $variety, $src, $type, $qty, $price, $cost, $icon, $alert, $notes, $p['id']]);
                $petId = $p['id'];
            } else {
                $sql = "INSERT INTO pets (name, category, pet_variety, source, type, qty, price, cost, icon, alert_level, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $cat, $variety, $src, $type, $qty, $price, $cost, $icon, $alert, $notes]);
                $petId = $pdo->lastInsertId();
            }

            // --- Handle Images ---
            if (isset($p['images']) && is_array($p['images'])) {
                // If update, maybe clear old images? Usually better to keep if no new ones, but if new ones exist, we replace
                if ($isUpdate && !empty($p['images'])) {
                    $pdo->prepare("DELETE FROM pet_images WHERE pet_id = ?")->execute([$petId]);
                }
                
                $imgStmt = $pdo->prepare("INSERT INTO pet_images (pet_id, image_data) VALUES (?, ?)");
                foreach ($p['images'] as $base64) {
                    if (!empty($base64)) {
                        $imgStmt->execute([$petId, $base64]);
                    }
                }
            }

            echo json_encode(['success' => true, 'id' => (int)$petId]);
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

    // --- AUTHENTICATION ---
    case 'login':
        try {
            $u = $input['username'] ?? '';
            $p = $input['password'] ?? '';
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$u]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($p, $admin['password'])) {
                $_SESSION['admin_auth'] = [
                    'id' => $admin['id'],
                    'username' => $admin['username']
                ];
                echo json_encode(['success' => true]);
            } else {
                respondError('Incorrect username or password');
            }
        } catch (PDOException $e) { respondError($e->getMessage()); }
        break;

    case 'logout':
        session_destroy();
        echo json_encode(['success' => true]);
        break;

    case 'changePassword':
        try {
            if (!isset($_SESSION['admin_auth'])) respondError('Unauthorized');
            $curr = $input['currentPassword'] ?? '';
            $new  = $input['newPassword'] ?? '';
            
            $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
            $stmt->execute([$_SESSION['admin_auth']['id']]);
            $hash = $stmt->fetchColumn();

            if (password_verify($curr, $hash)) {
                $newHash = password_hash($new, PASSWORD_DEFAULT);
                $upd = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $upd->execute([$newHash, $_SESSION['admin_auth']['id']]);
                echo json_encode(['success' => true]);
            } else {
                respondError('Incorrect current password');
            }
        } catch (PDOException $e) { respondError($e->getMessage()); }
        break;

    default:
        respondError('Action not found: ' . $action);
        break;
}
