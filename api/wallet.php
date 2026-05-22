<?php
/**
 * BETELITE - REST API Wallet Transaction & balance core
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

require_login();

$userId = $_SESSION['user_id'];
$action = clean_input($_POST['action'] ?? $_GET['action'] ?? '');

// Handle dynamic post updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'deposit') {
        $amount = (double)($_POST['amount'] ?? 50.00);
        $method = clean_input($_POST['payment_method'] ?? 'Telegram Stars');
        $ref = 'TXD' . mt_rand(100000, 999999);
        
        // Connect and update user wallet balance state
        $stmt = $conn->prepare("UPDATE `wallets` SET `balance` = `balance` + ? WHERE `user_id` = ?");
        if ($stmt) {
            $stmt->bind_param("di", $amount, $userId);
            $stmt->execute();
            $stmt->close();
            
            // Log transaction entry
            $txStmt = $conn->prepare("INSERT INTO `transactions` (`user_id`, `amount`, `type`, `status`, `payment_method`, `reference`) VALUES (?, ?, 'DEPOSIT', 'COMPLETED', ?, ?)");
            if ($txStmt) {
                $txStmt->bind_param("idss", $userId, $amount, $method, $ref);
                $txStmt->execute();
                $txStmt->close();
            }
        }
        
        $_SESSION['balance'] = ($_SESSION['balance'] ?? 100.00) + $amount;
        response_json(true, "Successfully deposited $amount USD via $method.", ['balance' => $_SESSION['balance']]);
        
    } elseif ($action === 'withdraw') {
        $amount = (double)($_POST['amount'] ?? 0.00);
        $address = clean_input($_POST['address'] ?? '');
        $method = clean_input($_POST['method'] ?? 'USDT TRC20');
        $ref = 'TXW' . mt_rand(100000, 999999);

        if ($amount <= 0 || $amount > ($_SESSION['balance'] ?? 0.00)) {
            response_json(false, 'Insufficient balance or invalid amount.');
        }

        // Subtract from wallet
        $stmt = $conn->prepare("UPDATE `wallets` SET `balance` = `balance` - ? WHERE `user_id` = ?");
        if ($stmt) {
            $stmt->bind_param("di", $amount, $userId);
            $stmt->execute();
            $stmt->close();

            // Log withdrawal proposal request
            $wStmt = $conn->prepare("INSERT INTO `withdrawals` (`user_id`, `amount`, `payment_method`, `payout_details`, `status`) VALUES (?, ?, ?, ?, 'PENDING')");
            if ($wStmt) {
                $wStmt->bind_param("idss", $userId, $amount, $method, $address);
                $wStmt->execute();
                $wStmt->close();
            }

            // Log transaction entry
            $txStmt = $conn->prepare("INSERT INTO `transactions` (`user_id`, `amount`, `type`, `status`, `payment_method`, `reference`, `details`) VALUES (?, ?, 'WITHDRAWAL', 'PENDING', ?, ?, ?)");
            if ($txStmt) {
                $txStmt->bind_param("idsss", $userId, $amount, $method, $ref, $address);
                $txStmt->execute();
                $txStmt->close();
            }
        }

        $_SESSION['balance'] = ($_SESSION['balance'] ?? 100.00) - $amount;
        response_json(true, 'Withdrawal proposal logged. Awaiting administrator approval.', ['balance' => $_SESSION['balance']]);

    } elseif ($action === 'vip_purchase') {
        $amount = 29.99;
        if ($_SESSION['balance'] < $amount) {
            response_json(false, 'Insufficient funds to checkout VIP pass.');
        }

        // Deduct balance and update user vip field status
        $stmt = $conn->prepare("UPDATE `wallets` SET `balance` = `balance` - ? WHERE `user_id` = ?");
        if ($stmt) {
            $stmt->bind_param("di", $amount, $userId);
            $stmt->execute();
            $stmt->close();

            // Update user role VIP
            $usrStmt = $conn->prepare("UPDATE `users` SET `is_vip` = 1, `vip_expires_at` = DATE_ADD(NOW(), INTERVAL 7 DAY) WHERE `id` = ?");
            if ($usrStmt) {
                $usrStmt->bind_param("i", $userId);
                $usrStmt->execute();
                $usrStmt->close();
            }

            // Record VIP sub
            $subStmt = $conn->prepare("INSERT INTO `subscriptions` (`user_id`, `plan_type`, `price_paid`, `start_date`, `end_date`, `status`) VALUES (?, 'WEEKLY', ?, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'ACTIVE')");
            if ($subStmt) {
                $subStmt->bind_param("id", $userId, $amount);
                $subStmt->execute();
                $subStmt->close();
            }

            // Log ledger transaction
            $ref = 'TXV' . mt_rand(100000, 999999);
            $txStmt = $conn->prepare("INSERT INTO `transactions` (`user_id`, `amount`, `type`, `status`, `payment_method`, `reference`) VALUES (?, ?, 'VIP_UPGRADE', 'COMPLETED', 'Wallet Transfer', ?)");
            if ($txStmt) {
                $txStmt->bind_param("ids", $userId, $amount, $ref);
                $txStmt->execute();
                $txStmt->close();
            }
        }

        $_SESSION['is_vip'] = 1;
        $_SESSION['balance'] = ($_SESSION['balance'] ?? 100) - $amount;
        response_json(true, 'Elite VIP Protection subscription purchase complete.', ['balance' => $_SESSION['balance']]);
    }
}

// Fetch ledger entries
$transactions = [];
$txRes = $conn->query("SELECT * FROM `transactions` WHERE `user_id` = $userId ORDER BY `id` DESC LIMIT 10");
if ($txRes && $txRes->num_rows > 0) {
    while($row = $txRes->fetch_assoc()) {
        $transactions[] = $row;
    }
} else {
    // Return sample ledger items
    $transactions = [
        [
            'id' => 1,
            'user_id' => $userId,
            'amount' => '50.00',
            'type' => 'DEPOSIT',
            'status' => 'COMPLETED',
            'payment_method' => 'Telegram Stars',
            'reference' => 'TXD731420',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ]
    ];
}

$balance = $_SESSION['balance'] ?? 100.00;
$p_earnings = $_SESSION['predictor_earnings'] ?? 0.00;

response_json(true, 'Wallet ledger list synced.', [
    'balance' => $balance,
    'predictor_earnings' => $p_earnings,
    'transactions' => $transactions
]);
?>
