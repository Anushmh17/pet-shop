<?php
require_once '../includes/config.php';

// SVG that centers a bird emoji so it renders in browsers instead of the letter 'B'
$birdSvg = "data:image/svg+xml;utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80'%3E%3Crect width='100%25' height='100%25' fill='%23e8fff0'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-size='36'%3E%F0%9F%A6%9C%3C/text%3E%3C/svg%3E";

try {
    $stmt = $pdo->prepare("UPDATE pet_images pi JOIN pets p ON pi.pet_id = p.id SET pi.image_data = ? WHERE p.category = ?");
    $stmt->execute([$birdSvg, 'bird']);
    echo "Updated bird images: " . $stmt->rowCount() . " rows\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

?>