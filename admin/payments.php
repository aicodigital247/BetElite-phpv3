<?php
/**
 * BETELITE - Admin Payout & Deposit ledger management
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

require_role('ADMIN');

// Handle approval status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdrawal_id'])) {
    $wid = (int)$_POST['withdrawal_id'];
    $status = clean_input($_POST['status'] ?? 'APPROVED');

    $stmt = $conn->prepare("UPDATE `withdrawals` SET `status` = ? WHERE `id` = ?");
    if ($stmt) {
        $stmt->bind_param("si", $status, $wid);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch withdrawals list
$withdrawals = [];
$res = $conn->query("SELECT * FROM `withdrawals` ORDER BY `id` DESC");
if ($res) {
    while($row = $res->fetch_assoc()) {
        $withdrawals[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>🏆 Admin Payouts | BETELITE</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-[#0b0c10] text-[#f1f3f5] p-6 font-sans">
  <div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between border-b border-white/5 pb-4 mb-6">
      <div>
        <h1 class="text-xl font-bold text-white uppercase flex items-center gap-2">💳 Bank Cashout Requests</h1>
        <p class="text-xs text-gray-400">Review, settle, or reject predictor withdrawal payouts.</p>
      </div>
      <a href="/admin/index.php" class="text-xs text-emerald-400 bg-emerald-500/10 px-3.5 py-1.5 rounded-lg font-bold">Admin Home</a>
    </div>

    <div class="glass-panel overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-white/5 border-b border-white/10 text-gray-400">
            <tr>
              <th class="p-3">ID</th>
              <th class="p-3">User ID</th>
              <th class="p-3">Amount</th>
              <th class="p-3">Payment Target</th>
              <th class="p-3">Method</th>
              <th class="p-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5 text-gray-300">
            <?php foreach($withdrawals as $w): ?>
            <tr class="hover:bg-white/5">
              <td class="p-3 font-mono text-gray-400"><?= $w['id'] ?></td>
              <td class="p-3 font-mono">#<?= $w['user_id'] ?></td>
              <td class="p-3 font-mono font-bold text-emerald-400">$<?= number_format($w['amount'], 2) ?></td>
              <td class="p-3 font-mono max-w-[200px] truncate"><?= htmlspecialchars($w['payout_details']) ?></td>
              <td class="p-3"><?= htmlspecialchars($w['payment_method']) ?></td>
              <td class="p-3 text-right">
                <?php if ($w['status'] === 'PENDING'): ?>
                  <form method="POST" class="inline-flex gap-1.5">
                    <input type="hidden" name="withdrawal_id" value="<?= $w['id'] ?>">
                    <button name="status" value="APPROVED" class="bg-mintGreen text-bgDark font-bold px-2 py-1 rounded text-[10px] uppercase">Approve</button>
                    <button name="status" value="REJECTED" class="bg-red-500 text-white font-bold px-2 py-1 rounded text-[10px] uppercase">Reject</button>
                  </form>
                <?php else: ?>
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $w['status'] === 'APPROVED' ? 'bg-green-500/15 text-green-400' : 'bg-red-500/15 text-red-400' ?>">
                    <?= $w['status'] ?>
                  </span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
