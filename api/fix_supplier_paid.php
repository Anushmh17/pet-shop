<?php
require_once '../includes/config.php';
set_time_limit(120);

try {
    echo "--- START: Fixing supplier cost_paid from sales totals ---\n";

    $rows = $pdo->query("SELECT id, pet_id, full_name, cost_paid FROM customer_suppliers")->fetchAll(PDO::FETCH_ASSOC);
    $updated = 0;
    foreach ($rows as $r) {
        $petId = $r['pet_id'];
        $current = floatval($r['cost_paid']);
        if ($current > 0) continue; // skip non-zero

        $stmt = $pdo->prepare('SELECT SUM(total) as s FROM sales WHERE pet_id = ?');
        $stmt->execute([$petId]);
        $sum = floatval($stmt->fetchColumn());

        if ($sum > 0) {
            $upd = $pdo->prepare('UPDATE customer_suppliers SET cost_paid = ? WHERE id = ?');
            $upd->execute([$sum, $r['id']]);
            echo "Updated supplier {$r['full_name']} (pet_id={$petId}) -> cost_paid={$sum}\n";
            $updated++;
        }
    }

    echo "✅ Done. Rows updated: {$updated}\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>