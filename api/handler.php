<?php
header('Content-Type: application/json');

// Session config for 9-hour timeout (9 * 60 * 60 = 32400s)
$timeout = 32400;
ini_set('session.gc_maxlifetime', $timeout);
session_set_cookie_params($timeout);
session_start();

// Auto-logout if older than 9 hours
if (isset($_SESSION['admin_auth'], $_SESSION['login_time'])) {
    if (time() - $_SESSION['login_time'] > $timeout) {
        session_destroy();
        respondError('Session expired. Please log in again.');
    }
}

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
            $stmt = $pdo->prepare("SELECT cs.*, p.name as pet_name, p.icon as pet_icon 
                                   FROM customer_suppliers cs 
                                   JOIN pets p ON cs.pet_id = p.id 
                                   WHERE cs.pet_id = ?");
            $stmt->execute([$petId]);
            $row = $stmt->fetch();
            echo json_encode($row ?: null);
        } catch (PDOException $e) { respondError($e->getMessage()); }
        break;

    case 'getAllCustomerSuppliers':
        try {
            $stmt = $pdo->query("SELECT cs.*, p.name as pet_name, p.icon as pet_icon, p.category, p.pet_variety 
                                 FROM customer_suppliers cs 
                                 JOIN pets p ON cs.pet_id = p.id 
                                 ORDER BY cs.created_at DESC");
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) { respondError($e->getMessage()); }
        break;

    case 'getUniqueDealers':
        try {
            // Dealers are sources NOT equal to 'Customer Supplied'
            $stmt = $pdo->query("SELECT DISTINCT source FROM pets WHERE source != 'Customer Supplied' AND source != '' ORDER BY source ASC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
        } catch (PDOException $e) { respondError($e->getMessage()); }
        break;

    case 'getDealerPets':
        try {
            $dealer = $_GET['dealer'] ?? '';
            $stmt = $pdo->prepare("SELECT * FROM pets WHERE source = ? ORDER BY created_at DESC");
            $stmt->execute([$dealer]);
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) { respondError($e->getMessage()); }
        break;

    case 'saveCustomerSupplier':
        try {
            $d = $input;
            $sql = "INSERT INTO customer_suppliers (pet_id, full_name, nic, nic_photo, address, cost_paid, description, supplier_uid, payment_status, due_date, payment_note)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        full_name=VALUES(full_name), nic=VALUES(nic), nic_photo=VALUES(nic_photo),
                        address=VALUES(address), cost_paid=VALUES(cost_paid), description=VALUES(description),
                        supplier_uid=VALUES(supplier_uid), payment_status=VALUES(payment_status), 
                        due_date=VALUES(due_date), payment_note=VALUES(payment_note)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                (int)$d['pet_id'],
                $d['full_name'] ?? '',
                $d['nic']       ?? '',
                $d['nic_photo'] ?? null,
                $d['address']   ?? '',
                (float)($d['cost_paid'] ?? 0),
                $d['description'] ?? '',
                $d['supplier_uid'] ?? null,
                $d['payment_status'] ?? 'Paid',
                $d['due_date'] ?? null,
                $d['payment_note'] ?? null
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
            
            // New fields
            $supId   = $p['supplierUid'] ?? null;
            $supName = $p['supplierName'] ?? null;
            $payStat = $p['paymentStatus'] ?? 'Paid';
            $dueDate = $p['dueDate'] ?? null;
            $payNote = $p['paymentNote'] ?? null;

            if ($isUpdate) {
                $sql = "UPDATE pets SET name=?, category=?, pet_variety=?, source=?, type=?, qty=?, price=?, cost=?, icon=?, alert_level=?, notes=?, supplier_uid=?, supplier_name=?, payment_status=?, due_date=?, payment_note=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $cat, $variety, $src, $type, $qty, $price, $cost, $icon, $alert, $notes, $supId, $supName, $payStat, $dueDate, $payNote, $p['id']]);
                $petId = $p['id'];
            } else {
                $sql = "INSERT INTO pets (name, category, pet_variety, source, type, qty, price, cost, icon, alert_level, notes, supplier_uid, supplier_name, payment_status, due_date, payment_note) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $cat, $variety, $src, $type, $qty, $price, $cost, $icon, $alert, $notes, $supId, $supName, $payStat, $dueDate, $payNote]);
                $petId = $pdo->lastInsertId();
            }

            // --- Handle Images ---
            if (isset($p['images']) && is_array($p['images'])) {
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

    case 'markAsPaid':
        try {
            $petId = (int)($input['pet_id'] ?? 0);
            if (!$petId) respondError('Missing Pet ID');

            $pdo->beginTransaction();
            $stmt1 = $pdo->prepare("UPDATE pets SET payment_status = 'Paid' WHERE id = ?");
            $stmt1->execute([$petId]);
            $stmt2 = $pdo->prepare("UPDATE customer_suppliers SET payment_status = 'Paid' WHERE pet_id = ?");
            $stmt2->execute([$petId]);
            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            respondError($e->getMessage());
        }
        break;

    case 'updatePayment':
        try {
            $d = $input;
            $petId = (int)($d['pet_id'] ?? 0);
            if (!$petId) respondError('Missing Pet ID');

            $pdo->beginTransaction();

            // Update pets
            $sqlPets = "UPDATE pets SET cost=?, payment_status=?, due_date=?, payment_note=? WHERE id=?";
            $stmtPets = $pdo->prepare($sqlPets);
            $stmtPets->execute([
                (float)($d['cost_paid'] ?? 0),
                $d['payment_status'] ?? 'Paid',
                $d['due_date'] ?? null,
                $d['payment_note'] ?? null,
                $petId
            ]);

            // Update customer_suppliers
            $sqlCS = "UPDATE customer_suppliers SET cost_paid=?, payment_status=?, due_date=?, payment_note=? WHERE pet_id=?";
            $stmtCS = $pdo->prepare($sqlCS);
            $stmtCS->execute([
                (float)($d['cost_paid'] ?? 0),
                $d['payment_status'] ?? 'Paid',
                $d['due_date'] ?? null,
                $d['payment_note'] ?? null,
                $petId
            ]);

            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            respondError($e->getMessage());
        }
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
                $_SESSION['login_time'] = time();
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
