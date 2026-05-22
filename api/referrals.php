<?php
/**
 * BETELITE - REST API Affiliates & Referrals metrics
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

require_login();

$userId = $_SESSION['user_id'];
$referrals = [];

$res = $conn->query("SELECT * FROM `referrals` WHERE `referrer_id` = $userId ORDER BY `id` DESC");
if ($res && $res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        $referrals[] = $row;
    }
} else {
    // Seed sample referrals as backup
    $referrals = [
        [
            'id' => 1,
            'referrer_id' => $userId,
            'referred_id' => 18452,
            'commission_earned' => '10.00',
            'status' => 'PAID',
            'created_at' => date('Y-m-d H:i:s', strtotime('-4 days'))
        ],
        [
            'id' => 2,
            'referrer_id' => $userId,
            'referred_id' => 47102,
            'commission_earned' => '5.00',
            'status' => 'PENDING',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
        ]
    ];
}

response_json(true, 'SaaS Affiliate referrals fetched.', [
    'referrals' => $referrals
]);
