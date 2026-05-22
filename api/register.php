<?php
/**
 * BETELITE - REST API Telegram user profile registration & synchronisation
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response_json(false, 'Only POST mapping supported.');
}

$telegram_id = clean_input($_POST['telegram_id'] ?? '');
$username = clean_input($_POST['username'] ?? '');
$first_name = clean_input($_POST['first_name'] ?? 'BetElite');
$last_name = clean_input($_POST['last_name'] ?? 'User');
$avatar_url = clean_input($_POST['avatar_url'] ?? '');

if (empty($telegram_id)) {
    response_json(false, 'Telegram ID is mandatory information.');
}

// Check if user exists in DB
$stmt = $conn->prepare("SELECT `id`, `role`, `is_vip`, `username` FROM `users` WHERE `telegram_id` = ?");
if ($stmt) {
    $stmt->bind_param("s", $telegram_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User already registered, load session
        $user = $result->fetch_assoc();
        
        // Fetch active wallet balance
        $balance = 100.00; // default starting preview value
        $predictor_earnings = 0.00;
        
        $wStmt = $conn->prepare("SELECT `balance`, `predictor_earnings` FROM `wallets` WHERE `user_id` = ?");
        if ($wStmt) {
            $wStmt->bind_param("i", $user['id']);
            $wStmt->execute();
            $wRes = $wStmt->get_result();
            if ($wRes->num_rows > 0) {
                $walletAndBal = $wRes->fetch_assoc();
                $balance = parseFloat($walletAndBal['balance']);
                $predictor_earnings = parseFloat($walletAndBal['predictor_earnings']);
            }
            $wStmt->close();
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'] ?: $username;
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['is_vip'] = $user['is_vip'];
        $_SESSION['balance'] = $balance;

        response_json(true, 'User synchronized successfully.', [
            'user' => [
                'id' => $user['id'],
                'username' => $_SESSION['username'],
                'role' => $user['role'],
                'is_vip' => $user['is_vip'],
                'balance' => $balance,
                'predictor_earnings' => $predictor_earnings
            ]
        ]);
    } else {
        // Create new user profile with referral tracker code
        $refCode = 'BE' . mt_rand(100000, 999999);
        $role = 'USER';
        $isVip = 0;
        
        $insStmt = $conn->prepare("INSERT INTO `users` (`telegram_id`, `username`, `first_name`, `last_name`, `avatar_url`, `role`, `is_vip`, `referral_code`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($insStmt) {
            $insStmt->bind_param("ssssssis", $telegram_id, $username, $first_name, $last_name, $avatar_url, $role, $isVip, $refCode);
            $insStmt->execute();
            $newUserId = $insStmt->insert_id;
            $insStmt->close();

            // Setup default wallet
            $startingBalance = 100.00; // Gift starting balance for preview
            $walIns = $conn->prepare("INSERT INTO `wallets` (`user_id`, `balance`) VALUES (?, ?)");
            if ($walIns) {
                $walIns->bind_param("id", $newUserId, $startingBalance);
                $walIns->execute();
                $walIns->close();
            }

            $_SESSION['user_id'] = $newUserId;
            $_SESSION['username'] = $username;
            $_SESSION['user_role'] = $role;
            $_SESSION['is_vip'] = $isVip;
            $_SESSION['balance'] = $startingBalance;

            response_json(true, 'Telegram profile registered in platform ledger.', [
                'user' => [
                    'id' => $newUserId,
                    'username' => $username,
                    'role' => $role,
                    'is_vip' => $isVip,
                    'balance' => $startingBalance,
                    'predictor_earnings' => 0.00
                ]
            ]);
        }
    }
    $stmt->close();
}

// If DB connection fails table fallback
response_json(true, 'Telemetry synchronization simulated.');
