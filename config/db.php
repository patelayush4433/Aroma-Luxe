<?php
/**
 * Database Connection Config
 * Uses PDO for secure database communications
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'perfume_store';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If the database does not exist or server isn't run, redirect to setup helper or error out
    die("Database connection failed. If you haven't run setup yet, please open <a href='setup.php'>setup.php</a> in your browser to initialize database.");
}

// Auto-add role column if not exists
try {
    $pdo->exec("ALTER TABLE `admin` ADD COLUMN `role` VARCHAR(20) DEFAULT 'admin' AFTER `email`");
} catch (PDOException $e) {
    // Column already exists or error is safe to ignore
}

// Convert seeded external product images to local offline images
try {
    $pdo->exec("UPDATE `products` SET `image_url` = 'assets/images/oud_perfume.png' WHERE `id` = 1 AND `image_url` LIKE '%unsplash%'");
    $pdo->exec("UPDATE `products` SET `image_url` = 'assets/images/womens_perfume.png' WHERE `id` = 2 AND `image_url` LIKE '%unsplash%'");
    $pdo->exec("UPDATE `products` SET `image_url` = 'assets/images/mens_perfume.png' WHERE `id` = 3 AND `image_url` LIKE '%unsplash%'");
    $pdo->exec("UPDATE `products` SET `image_url` = 'assets/images/mens_perfume.png' WHERE `id` = 4 AND `image_url` LIKE '%unsplash%'");
    $pdo->exec("UPDATE `products` SET `image_url` = 'assets/images/womens_perfume.png' WHERE `id` = 5 AND `image_url` LIKE '%unsplash%'");
    $pdo->exec("UPDATE `products` SET `image_url` = 'assets/images/unisex_perfume.png' WHERE `id` = 6 AND `image_url` LIKE '%unsplash%'");
    $pdo->exec("UPDATE `products` SET `image_url` = 'assets/images/unisex_perfume.png' WHERE `id` = 7 AND `image_url` LIKE '%unsplash%'");
    $pdo->exec("UPDATE `products` SET `image_url` = 'assets/images/oud_perfume.png' WHERE `id` = 8 AND `image_url` LIKE '%unsplash%'");
} catch (PDOException $e) {
    // Ignore if table does not exist or matches are clean
}

// Load Global Settings
$settings = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM `settings`");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    // Suppress if table doesn't exist yet
}

// Set up currency defaults if not in DB
if (!isset($settings['currency_symbol'])) {
    $settings['currency_symbol'] = '₹';
}
if (!isset($settings['currency'])) {
    $settings['currency'] = 'INR';
}
if (!isset($settings['gst_percentage'])) {
    $settings['gst_percentage'] = '18.00';
}
if (!isset($settings['shipping_fee'])) {
    $settings['shipping_fee'] = '15.00';
}
