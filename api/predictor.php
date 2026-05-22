<?php
/**
 * BETELITE - REST API Predictor HQ Operations
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

require_role('PREDICTOR');

$userId = $_SESSION['user_id'];
$action = clean_input($_POST['action'] ?? '');

if ($action === 'publish') {
    $matchId = (int)($_POST['match_id'] ?? 0);
    $title = clean_input($_POST['title'] ?? '');
    $desc = clean_input($_POST['description'] ?? '');
    $price = (double)($_POST['price'] ?? 10.00);
    $isVip = (int)($_POST['is_vip'] ?? 0);
    $tips_json = $_POST['tips'] ?? '[]'; // JSON encoded tips

    if (empty($title) || $matchId === 0) {
        response_json(false, 'Missing required parameters: Title and target Match ID.');
    }

    // Double check tips JSON is accurate length 3 tips
    $tipsArr = json_decode($tips_json, true);
    if (!is_array($tipsArr) || count($tipsArr) < 3) {
        response_json(false, 'Prediction Combo bundle must contain exactly 3 sports soccer tips.');
    }

    // Multiply odds to calculate total cumulative odds value
    $total_odds = 1.00;
    foreach ($tipsArr as $t) {
        $total_odds *= (double)($t['odds'] ?? 1.10);
    }

    $confidence = mt_rand(75, 96);
    $status = 'PENDING';

    $stmt = $conn->prepare("INSERT INTO `predictions` (`predictor_id`, `match_id`, `title`, `description`, `price`, `tips_json`, `total_odds`, `confidence`, `is_vip`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iissdsdiss", $userId, $matchId, $title, $desc, $price, $tips_json, $total_odds, $confidence, $isVip, $status);
        $stmt->execute();
        $stmt->close();
        response_json(true, 'Your prediction combo ticket is live now in the marketplace.');
    } else {
        response_json(false, 'Failed compiling prediction database listing.');
    }

} elseif ($action === 'withdraw') {
    // Transfer from predictor sales earnings to available primary funds balance
    $earnings = $_SESSION['predictor_earnings'] ?? 0.00;
    if ($earnings < 10) {
        response_json(false, 'Minimum payout cashout threshold is $10.00');
    }

    $stmt = $conn->prepare("UPDATE `wallets` SET `balance` = `balance` + `predictor_earnings`, `predictor_earnings` = 0 WHERE `user_id` = ?");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
        
        // Log transaction logs
        $ref = 'TXC' . mt_rand(100000, 999999);
        $txStmt = $conn->prepare("INSERT INTO `transactions` (`user_id`, `amount`, `type`, `status`, `payment_method`, `reference`) VALUES (?, ?, 'EARNING', 'COMPLETED', 'Internal Transfer', ?)");
        if ($txStmt) {
            $txStmt->bind_param("ids", $userId, $earnings, $ref);
            $txStmt->execute();
            $txStmt->close();
        }
        
        $_SESSION['balance'] = ($_SESSION['balance'] ?? 0.00) + $earnings;
        $_SESSION['predictor_earnings'] = 0.00;
        
        response_json(true, 'Predictor commission transferred successfully to your available wallet balance.');
    } else {
        response_json(false, 'Transaction payout processing error.');
    }
}

response_json(false, 'Predictor payload mapping error.');
?>
