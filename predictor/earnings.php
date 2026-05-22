<?php
/**
 * BETELITE - Predictor Sales Payout and Ledger reports
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

require_role('PREDICTOR');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>💳 Sales Cashouts Vault | BETELITE</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-[#0b0c10] text-[#f1f3f5] p-6 font-sans">
  <div class="max-w-md mx-auto py-8">
    <div class="glass-panel p-6 border-emerald-500/10 text-center">
      <h1 class="text-lg font-bold text-white mb-2">💳 Seller Commissions cashouts</h1>
      <p class="text-xs text-gray-400 mb-6 leading-relaxed">Predictors can securely cashout accumulated commissions (80% royalties on sales) to their primary available wallet funds vault instantly with a single tap inside the main Telegram Client HUD wallet section.</p>
      <a href="/predictor/dashboard.php" class="btn-emerald-graded w-full text-center py-2.5 rounded-xl text-xs font-bold block mb-2">Back to Dashboard</a>
      <a href="/index.php" class="bg-[#1f2937] hover:bg-white/5 w-full text-center py-2.5 rounded-xl text-xs text-white block transition font-bold">Open Active App</a>
    </div>
  </div>
</body>
</html>
