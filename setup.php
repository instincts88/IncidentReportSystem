<?php
/**
 * One-time setup script — DELETE AFTER USE
 * Run this once to create the admin account with a secure password.
 * Visit: http://localhost/IncidentReportSystem/setup.php
 */
define('SETUP_KEY', 'CHANGE_ME_BEFORE_USE'); // Change this!

if (($_GET['key'] ?? '') !== SETUP_KEY) {
    http_response_code(403);
    die('<h1>403 Forbidden</h1><p>Invalid or missing setup key.</p>');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/security.php';

try {
    // Create tables from schema
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    // Split and execute (skip comments)
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        if (!empty($stmt) && !str_starts_with(ltrim($stmt), '--')) {
            try { db()->exec($stmt); } catch (PDOException $e) { /* ignore duplicate errors */ }
        }
    }

    // Create admin user
    $adminPassword = 'Admin@1234'; // Change this before running!
    $hash = Security::hashPassword($adminPassword);
    $pdo = db();
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, is_active) VALUES (?,?,?,'admin',1) ON DUPLICATE KEY UPDATE password=?");
    $stmt->execute(['Administrator', 'admin@example.com', $hash, $hash]);

    echo '<div style="font-family:sans-serif;max-width:500px;margin:50px auto;padding:2rem;border:1px solid #ddd;border-radius:8px;">';
    echo '<h2 style="color:green">✅ Setup Complete!</h2>';
    echo '<p><strong>Admin Email:</strong> admin@example.com</p>';
    echo '<p><strong>Admin Password:</strong> Admin@1234</p>';
    echo '<p style="color:red"><strong>⚠️ Change the password after first login and delete this file!</strong></p>';
    echo '<a href="index.php" style="background:#2563EB;color:#fff;padding:0.5rem 1.5rem;text-decoration:none;border-radius:6px;">Go to Login</a>';
    echo '</div>';
} catch (Exception $e) {
    echo '<p style="color:red">Setup failed: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
