<?php
/**
 * BETELITE - Sports Prediction SaaS MySQLi Connection Configuration
 * Code is fully compatible with PHP 8+ cPanel & standard Shared Hosting
 */

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'betelite_db';

// Establish Secure MySQLi Connection
$conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check for connection errors
if ($conn->connect_error) {
    // In production, log error secure-wise instead of showing raw exception
    error_log("Database connection failed: " . $conn->connect_error);
    
    // Output standard JSON error if requested via AJAX, otherwise show clean error screen
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed. Please contact administrator or verify config.'
        ]);
        exit;
    } else {
        die("<div style='font-family:sans-serif;background:#0d0e12;color:#f3f4f6;padding:40px;text-align:center;height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;'>
            <h1 style='color:#ef4444;font-size:28px;margin-bottom:10px;'>🏆 BETELITE Database Offline</h1>
            <p style='color:#9ca3af;font-size:16px;max-width:500px;'>The sports analytics engine is currently offline due to database configuration. Please import <b>database.sql</b> and configure server credentials in <b>/config/database.php</b>.</p>
        </div>");
    }
}

// Set connection charset to support utf8mb4 emojis (crucial for team logos and Telegram avatars)
$conn->set_charset("utf8mb4");
