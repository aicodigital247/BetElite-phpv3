<?php
/**
 * BETELITE - REST API Identity Switcher / Login Session handler
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response_json(false, 'Only POST method is permitted.');
}

$role = clean_input($_POST['role'] ?? 'USER');

// Determine identity details based on role requested
$userId = 3;
$username = 'buyer_bob';
$firstName = 'Bob';
$lastName = 'Buyer';
$isVip = 0;
$balance = 100.00;
$earnings = 0.00;

if ($role === 'ADMIN') {
    $userId = 1;
    $username = 'admin_demo';
    $firstName = 'BetElite';
    $lastName = 'Admin';
    $isVip = 1;
    $balance = 10000.00;
} elseif ($role === 'PREDICTOR') {
    $userId = 2;
    $username = 'predictor_john';
    $firstName = 'John';
    $lastName = 'Predictor';
    $isVip = 1;
    $balance = 250.00;
    $earnings = 480.00;
}

// Store inside secure session state
$_SESSION['user_id'] = $userId;
$_SESSION['username'] = $username;
$_SESSION['first_name'] = $firstName;
$_SESSION['last_name'] = $lastName;
$_SESSION['user_role'] = $role;
$_SESSION['is_vip'] = $isVip;
$_SESSION['balance'] = $balance;
$_SESSION['predictor_earnings'] = $earnings;

// Prepared Statement to update or sync this user record if database table is available
$stmt = $conn->prepare("INSERT INTO `users` (`id`, `username`, `first_name`, `last_name`, `role`, `is_vip`, `referral_code`) 
    VALUES (?, ?, ?, ?, ?, ?, ?) 
    ON DUPLICATE KEY UPDATE `role` = ?, `is_vip` = ?");

if ($stmt) {
    $refCode = 'BE' . $userId;
    $stmt->bind_param("issssisss", $userId, $username, $firstName, $lastName, $role, $isVip, $refCode, $role, $isVip);
    $stmt->execute();
    $stmt->close();
    
    // Create wallet record if not existing
    $wStmt = $conn->prepare("INSERT INTO `wallets` (`user_id`, `balance`, `predictor_earnings`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `balance` = ?");
    if ($wStmt) {
        $wStmt->bind_param("iddd", $userId, $balance, $earnings, $balance);
        $wStmt->execute();
        $wStmt->close();
    }
}

response_json(true, "Successfully initialized session as $role.", [
    'user' => [
        'id' => $userId,
        'username' => $username,
        'first_name' => $firstName,
        'role' => $role,
        'balance' => $balance,
        'is_vip' => $isVip
    ]
]);
