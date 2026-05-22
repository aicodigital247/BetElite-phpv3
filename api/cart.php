<?php
/**
 * BETELITE - REST API Cart checkout and split payouts
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

require_login();

$userId = $_SESSION['user_id'];
$raw_ids = $_POST['prediction_ids'] ?? '[]';
$pred_ids = json_decode($raw_ids, true);

if (!is_array($pred_ids) || count($pred_ids) === 0) {
    response_json(false, 'Cart is empty. Select ticket bundles first.');
}

// Fetch prediction details from DB to calculate exact aggregate sum
$placeholders = implode(',', array_fill(0, count($pred_ids), '?'));
$types = str_repeat('i', count($pred_ids));

$stmt = $conn->prepare("SELECT `id`, `price`, `predictor_id`, `title` FROM `predictions` WHERE `id` IN ($placeholders)");
if ($stmt) {
    $stmt->bind_param($types, ...$pred_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $total_price = 0.00;
    $purchases = [];
    
    while ($row = $result->fetch_assoc()) {
        $total_price += (double)$row['price'];
        $purchases[] = $row;
    }
    $stmt->close();

    if ($total_price > ($_SESSION['balance'] ?? 0.00)) {
        response_json(false, 'Insufficient available wallet balance to complete checkout.');
    }

    // Deduct user balance
    $dedStmt = $conn->prepare("UPDATE `wallets` SET `balance` = `balance` - ? WHERE `user_id` = ?");
    if ($dedStmt) {
        $dedStmt->bind_param("di", $total_price, $userId);
        $dedStmt->execute();
        $dedStmt->close();
    }

    // Process split commissions for each purchased predictions
    foreach ($purchases as $item) {
        $prediction_id = $item['id'];
        $price = (double)$item['price'];
        $predictor_id = $item['predictor_id'];

        // Record Order history Log
        $ordStmt = $conn->prepare("INSERT INTO `orders` (`buyer_id`, `prediction_id`, `price_paid`, `status`) VALUES (?, ?, ?, 'COMPLETED')");
        if ($ordStmt) {
            $ordStmt->bind_param("iid", $userId, $prediction_id, $price);
            $ordStmt->execute();
            $order_id = $ordStmt->insert_id;
            $ordStmt->close();

            // Calculate split commissions (80% predictor, 20% admin platform)
            $predictor_commission = $price * 0.80;
            
            // Add earnings record
            $earnStmt = $conn->prepare("INSERT INTO `earnings` (`predictor_id`, `order_id`, `amount`, `commission_percentage`) VALUES (?, ?, ?, 80.00)");
            if ($earnStmt) {
                $earnStmt->bind_param("iid", $predictor_id, $order_id, $predictor_commission);
                $earnStmt->execute();
                $earnStmt->close();
            }

            // Update Predictor Sales Balance
            $predWal = $conn->prepare("UPDATE `wallets` SET `predictor_earnings` = `predictor_earnings` + ? WHERE `user_id` = ?");
            if ($predWal) {
                $predWal->bind_param("di", $predictor_commission, $predictor_id);
                $predWal->execute();
                $predWal->close();
            }

            // Increment sales views counts (SaaS metrics)
            $incStmt = $conn->prepare("UPDATE `predictions` SET `sales_count` = `sales_count` + 1 WHERE `id` = ?");
            if ($incStmt) {
                $incStmt->bind_param("i", $prediction_id);
                $incStmt->execute();
                $incStmt->close();
            }
        }
    }

    // Create Ledger Transaction for purchase
    $ref = 'TXC' . mt_rand(100000, 999999);
    $txStmt = $conn->prepare("INSERT INTO `transactions` (`user_id`, `amount`, `type`, `status`, `payment_method`, `reference`) VALUES (?, ?, 'PURCHASE', 'COMPLETED', 'Wallet Transfer', ?)");
    if ($txStmt) {
        $txStmt->bind_param("ids", $userId, $total_price, $ref);
        $txStmt->execute();
        $txStmt->close();
    }

    $_SESSION['balance'] = ($_SESSION['balance'] ?? 100.00) - $total_price;
    response_json(true, 'Checked out sports prediction tickets successfully.', ['balance' => $_SESSION['balance']]);
}

response_json(false, 'Checkout failed to connect database schemas.');
?>
