<?php
/**
 * BETELITE - Sports Prediction SaaS Configuration File
 * Author: Senior Developer & Telegram Mini App Architect
 * Mode: PHP 8+ cPanel & MySQLi Support
 */

// Start Secure Session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400 * 7, // 7 Days
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Global System Settings
define('APP_NAME', 'BETELITE');
define('PLATFORM_FEE_PCT', 20.00); // 20% commission goes to Admin, 80% to Predictor
define('REFERRAL_COMMISSION_PCT', 10.00); // 10% on deposits
define('VIP_COST_WEEKLY', 29.99); // USD
define('VIP_COST_MONTHLY', 79.99); // USD

// CSRF Protection Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * XSS String Filtering Utility
 */
function clean_input($data) {
    if ($data === null) return '';
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * REST API JSON Output Helper
 */
function response_json($status, $message, $data = []) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge([
        'success' => $status,
        'message' => $message
    ], $data));
    exit;
}

/**
 * Verify CSRF Token
 */
function verify_csrf($token) {
    return !empty($token) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Simple Authentication Guard Middlewares
 */
function require_login() {
    if (empty($_SESSION['user_id'])) {
        response_json(false, 'Unauthorized. Please login.');
    }
}

function require_role($role) {
    require_login();
    if ($_SESSION['user_role'] !== $role && $_SESSION['user_role'] !== 'ADMIN') {
        response_json(false, 'Access denied. Privileged role required.');
    }
}

/**
 * Telegram WebApp Raw Init Data Validation Method
 * Validates data received from Telegram WebApp iframe
 */
function validate_telegram_init_data($initData, $botToken) {
    if (empty($initData) || empty($botToken)) return false;
    
    parse_str($initData, $data);
    if (!isset($data['hash'])) return false;
    
    $checkHash = $data['hash'];
    unset($data['hash']);
    
    ksort($data);
    $dataCheckString = '';
    foreach ($data as $key => $value) {
        $dataCheckString .= "$key=$value\n";
    }
    $dataCheckString = rtrim($dataCheckString, "\n");
    
    $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
    $hash = hash_hmac('sha256', $dataCheckString, $secretKey);
    
    return hash_equals($hash, $checkHash);
}
