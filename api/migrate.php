<?php
// ONE-TIME MIGRATION: Creates customer_suppliers table
// Visit this URL once in browser, then delete this file.
require_once '../includes/config.php';

$sql = "CREATE TABLE IF NOT EXISTS customer_suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT UNIQUE,
    full_name VARCHAR(255),
    nic VARCHAR(50),
    nic_photo LONGTEXT,
    address TEXT,
    cost_paid DECIMAL(10,2),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
)";

try {
    $pdo->exec($sql);
    echo '<h2 style="font-family:sans-serif; color:green;">✅ Migration complete! Table customer_suppliers created.</h2>';
    echo '<p style="font-family:sans-serif;">You can now delete <code>api/migrate.php</code>.</p>';
} catch (PDOException $e) {
    echo '<h2 style="font-family:sans-serif; color:red;">❌ Error: ' . $e->getMessage() . '</h2>';
}
