<?php
/**
 * BETELITE - Admin predictions list and verification page
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

require_role('ADMIN');

$predictions = [];
$res = $conn->query("SELECT * FROM `predictions` ORDER BY `id` DESC");
if ($res) {
    while($row = $res->fetch_assoc()) {
        $predictions[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>🏆 Admin Prediction Manager | BETELITE</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-[#0b0c10] text-[#f1f3f5] p-6 font-sans">
  <div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between border-b border-white/5 pb-4 mb-6">
      <div>
        <h1 class="text-xl font-bold text-white uppercase flex items-center gap-2">📋 Marketplace Predictions</h1>
        <p class="text-xs text-gray-400">View performance stats of all published prediction bundles.</p>
      </div>
      <a href="/admin/index.php" class="text-xs text-emerald-400 bg-emerald-500/10 px-3.5 py-1.5 rounded-lg font-bold">Admin Home</a>
    </div>

    <div class="glass-panel overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-white/5 border-b border-white/10 text-gray-400">
            <tr>
              <th class="p-3">ID</th>
              <th class="p-3">Predictor ID</th>
              <th class="p-3">Ticket Title</th>
              <th class="p-3">Odds</th>
              <th class="p-3">Price</th>
              <th class="p-3">Sales</th>
              <th class="p-3 text-right">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5 text-gray-300">
            <?php foreach($predictions as $p): ?>
            <tr class="hover:bg-white/5">
              <td class="p-3 font-mono text-gray-400"><?= $p['id'] ?></td>
              <td class="p-3 font-mono">#<?= $p['predictor_id'] ?></td>
              <td class="p-3 font-semibold text-white"><?= htmlspecialchars($p['title']) ?></td>
              <td class="p-3 font-mono text-emerald-400"><?= number_format($p['total_odds'], 2) ?></td>
              <td class="p-3 font-mono">$<?= number_format($p['price'], 2) ?></td>
              <td class="p-3 font-mono"><?= $p['sales_count'] ?> sales</td>
              <td class="p-3 text-right">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $p['status'] === 'WON' ? 'bg-green-500/15 text-green-400' : ($p['status'] === 'LOST' ? 'bg-red-500/15 text-red-400' : 'bg-amber-500/15 text-amber-500') ?>">
                  <?= $p['status'] ?>
                </span>
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
