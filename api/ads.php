<?php
/**
 * BETELITE - REST API Sponsored promotional banners
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$action = clean_input($_POST['action'] ?? $_GET['action'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad_id = (int)($_POST['ad_id'] ?? 0);
    
    if ($action === 'click' && $ad_id > 0) {
        $stmt = $conn->prepare("UPDATE `ads` SET `clicks` = `clicks` + 1 WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("i", $ad_id);
            $stmt->execute();
            $stmt->close();
        }
        response_json(true, 'Click tracked successfully.');
    }
}

// Default Ads output
$ads = [];
$res = $conn->query("SELECT * FROM `ads` WHERE `status` = 'ACTIVE'");

if ($res && $res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        $ads[] = $row;
    }
} else {
    // Seed default mocks
    $ads = [
        [
            'id' => 1,
            'title' => '🔥 UPGRADE TO VIP - Get Fixed Combo tickets over 15+ Odds!',
            'image_url' => 'https://images.unsplash.com/photo-1518063319789-7217e6706b04?q=80&w=600',
            'link' => '#wallet',
            'type' => 'BANNER',
            'status' => 'ACTIVE'
        ],
        [
            'id' => 2,
            'title' => '⚡ Sponsor: Stake with 1XBET! Promo Code: BETELITE',
            'image_url' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?q=80&w=600',
            'link' => 'https://1xbet.com',
            'type' => 'BANNER',
            'status' => 'ACTIVE'
        ]
    ];
}

response_json(true, 'Banners loaded.', ['ads' => $ads]);
?>
