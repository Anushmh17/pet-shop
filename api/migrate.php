<?php
// ONE-TIME MIGRATION: Creates customer_suppliers table
// Visit this URL once in browser, then delete this file.
require_once '../includes/config.php';

$sql1 = "CREATE TABLE IF NOT EXISTS customer_suppliers (
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

$sql2 = "CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$sql3 = "INSERT IGNORE INTO admins (username, password) 
        VALUES ('admin', '$2y$10$UoE2RByR2x7L2c5.xZp7re/u5fXQ5YmRz0Zp9e3x0.v/7s9p8u3Kq')";

try {
    $pdo->exec($sql1);
    $pdo->exec($sql2);
    $pdo->exec($sql3);
    echo '<h2 style="font-family:sans-serif; color:green;">✅ Migration complete!</h2>';
    echo '<p style="font-family:sans-serif;">Tables <code>customer_suppliers</code> and <code>admins</code> are ready. Initial admin: <code>admin</code> / <code>admin123</code></p>';
} catch (PDOException $e) {
    echo '<h2 style="font-family:sans-serif; color:red;">❌ Error: ' . $e->getMessage() . '</h2>';
}
